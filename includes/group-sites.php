<?php
/**
 * Group blogs functionality
 */

/**
 * Utility function for fetching the group id for a blog.
 *
 * @param int $blog_id
 * @param int $group_id
 */
function openlab_get_group_id_by_blog_id( $blog_id ) {
	global $wpdb, $bp;

	if ( ! bp_is_active( 'groups' ) ) {
		return 0;
	}

	if ( empty( $bp->groups->table_name_groupmeta ) ) {
		return 0;
	}

	$group_id = wp_cache_get( $blog_id, 'site_group_ids' );

	if ( false === $group_id ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$group_id = $wpdb->get_var( $wpdb->prepare( "SELECT group_id FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'cboxol_group_site_id' AND meta_value = %d", $blog_id ) );

		if ( null !== $group_id ) {
			wp_cache_set( $blog_id, (int) $group_id, 'site_group_ids' );
		}
	}

	return (int) $group_id;
}

/**
 * Busts cache when group site ID is changed.
 *
 * @param int $group_id
 * @param int $site_id
 */
function cboxol_bust_site_group_id_cache( $group_id, $site_id ) {
	wp_cache_delete( $site_id, 'site_group_ids' );
}
add_action( 'cboxol_set_group_site_id', 'cboxol_bust_site_group_id_cache', 10, 2 );

/**
 * Deletes groupmeta linking a site to a group when the site is deleted.
 *
 * @since 1.2.3
 *
 * @param WP_Site $site Site object.
 */
function cboxol_unlink_site_from_group_on_site_deletion( $site ) {
	$group_id = openlab_get_group_id_by_blog_id( $site->id );

	if ( ! $group_id ) {
		return;
	}

	// For debugging purposes in case of unlinked groups.
	groups_update_groupmeta( $group_id, 'cboxol_group_site_id_deleted', $site->id );

	groups_delete_groupmeta( $group_id, 'cboxol_group_site_id' );

	cboxol_bust_site_group_id_cache( $group_id, $site->id );
}
add_action( 'wp_delete_site', 'cboxol_unlink_site_from_group_on_site_deletion' );

/**
 * Deletes groupmeta linking a site to a group when site is marked as deleted or spam.
 *
 * @since 1.2.3
 *
 * @param WP_Site $site Site object.
 */
function cboxol_unlink_site_from_group_on_site_status_change( $site ) {
	if ( ! $site->spam && ! $site->deleted ) {
		return;
	}

	cboxol_unlink_site_from_group_on_site_deletion( $site );
}
add_action( 'wp_update_site', 'cboxol_unlink_site_from_group_on_site_status_change' );

/**
 * Utility function for fetching the site id for a group
 */
function openlab_get_site_id_by_group_id( $group_id = 0 ) {
	if ( ! $group_id ) {
		$group_id = bp_get_current_group_id();
	}

	return (int) groups_get_groupmeta( $group_id, 'cboxol_group_site_id' );
}

/**
 * Get a group's site ID.
 *
 * Alias of openlab_get_site_id_by_group_id() for naming consistency.
 */
function cboxol_get_group_site_id( $group_id = 0 ) {
	return openlab_get_site_id_by_group_id( $group_id );
}

/**
 * Set a group's site ID.
 *
 * @param int $group_id ID of the group.
 * @param int $site_id  ID of the site.
 */
function cboxol_set_group_site_id( $group_id, $site_id ) {
	$set = (bool) groups_update_groupmeta( $group_id, 'cboxol_group_site_id', (int) $site_id );

	/**
	 * Fires when a group's site ID has been set or updated.
	 *
	 * @param int $group_id ID of the group.
	 * @param int $site_id  ID of the site.
	 */
	do_action( 'cboxol_set_group_site_id', $group_id, $site_id );

	return $set;
}

/**
 * Syncs the group site's blog_public setting to the linked group.
 *
 * @since 1.2.0
 *
 * @param string $old_value
 * @param string $new_value
 */
function cboxol_sync_group_site_blog_public( $old_value, $value ) {
	$group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );
	if ( ! $group_id ) {
		return;
	}

	groups_update_groupmeta( $group_id, 'blog_public', $value );
}
add_action( 'update_option_blog_public', 'cboxol_sync_group_site_blog_public', 10, 2 );

/**
 * Syncs the group site's blog_public setting to the linked group when group is saved.
 *
 * @param \BP_Groups_Group $group Group object.
 */
function cboxol_sync_group_blog_public_on_group_save( $group ) {
	$site_id = openlab_get_site_id_by_group_id( $group->id );
	if ( ! $site_id ) {
		return;
	}

	$blog_public = get_blog_option( $site_id, 'blog_public' );

	groups_update_groupmeta( $group->id, 'blog_public', (int) $blog_public );
}
add_action( 'groups_group_after_save', 'cboxol_sync_group_blog_public_on_group_save' );

/**
 * Get site type based on the group type.
 *
 * @param int $site_id
 * @return string $group_type
 */
function cboxol_get_group_site_type( $site_id ) {
	$group_id = openlab_get_group_id_by_blog_id( $site_id );

	if ( ! $group_id ) {
		return '';
	}

	$group_type = cboxol_get_group_group_type( $group_id );
	if ( is_wp_error( $group_type ) ) {
		return '';
	}

	return $group_type;
}

/**
 * Use this function to get the URL of a group's site. It'll work whether the site is internal
 * or external
 *
 * @param int $group_id
 */
function openlab_get_group_site_url( $group_id = false ) {
	if ( false === $group_id ) {
		$group_id = openlab_fallback_group();
	}

	$site_url = '';

	if ( ! $group_id ) {
		return $site_url;
	}

	// First check for an internal site, then external
	$site_id = openlab_get_site_id_by_group_id( $group_id );
	if ( $site_id ) {
		$site_url = get_blog_option( $site_id, 'siteurl' );
	} else {
		$site_url = openlab_get_external_site_url_by_group_id( $group_id );
	}

	return $site_url;
}

/**
 * Save the blogname in wp_blogmeta at site initialization.
 *
 * @since 1.4.0
 *
 * @param WP_Site $site Site object.
 */
function cboxol_save_blogname_to_blogmeta_at_site_initialization( $site ) {
	update_site_meta( $site->blog_id, 'blogname', $site->blogname );
}
add_action( 'wp_initialize_site', 'cboxol_save_blogname_to_blogmeta_at_site_initialization' );

/**
 * Save the blogname in wp_blogmeta at options update.
 *
 * @since 1.4.0
 *
 * @param string $old_value Old blogname.
 * @param string $value     New blogname.
 */
function cboxol_save_blogname_to_blogmeta_at_options_update( $old_value, $value ) {
	update_site_meta( get_current_blog_id(), 'blogname', $value );
}
add_action( 'update_option_blogname', 'cboxol_save_blogname_to_blogmeta_at_options_update', 10, 2 );

/**
 * Save group extras, including group blog creation.
 *
 * @todo Split up.
 */
function cboxol_save_group_extras( $group ) {
	global $wpdb, $bp;

	$is_editing = false;

	// phpcs:disable WordPress.Security.NonceVerification.Missing

	if ( isset( $_POST['_wp_http_referer'] ) && strpos( $_POST['_wp_http_referer'], 'edit-details' ) !== false ) {
		$is_editing = true;
	}

	if ( isset( $_POST['wds_faculty'] ) ) {
		groups_update_groupmeta( $group->id, 'wds_faculty', $_POST['wds_faculty'] );
	}
	if ( isset( $_POST['wds_group_school'] ) ) {
		$wds_group_school = implode( ',', $_POST['wds_group_school'] );

		//fully deleting and then adding in school metadata so schools can be unchecked
		groups_delete_groupmeta( $group->id, 'wds_group_school' );
		groups_add_groupmeta( $group->id, 'wds_group_school', $wds_group_school, true );
	} elseif ( ! isset( $_POST['wds_group_school'] ) ) {
		//allows user to uncheck all schools (projects and clubs only)
		//on edit only
		if ( $is_editing ) {
			groups_update_groupmeta( $group->id, 'wds_group_school', '' );
		}
	}

	if ( isset( $_POST['wds_departments'] ) ) {
		$wds_departments = implode( ',', $_POST['wds_departments'] );

		//fully deleting and then adding in department metadata so departments can be unchecked
		groups_delete_groupmeta( $group->id, 'wds_departments' );
		groups_add_groupmeta( $group->id, 'wds_departments', $wds_departments, true );
	} elseif ( ! isset( $_POST['wds_departments'] ) ) {
		//allows user to uncheck all departments (projects and clubs only)
		//on edit only
		if ( $is_editing ) {
			groups_update_groupmeta( $group->id, 'wds_departments', '' );
		}
	}

	if ( isset( $_POST['wds_course_code'] ) ) {
		groups_update_groupmeta( $group->id, 'wds_course_code', $_POST['wds_course_code'] );
	}
	if ( isset( $_POST['wds_section_code'] ) ) {
		groups_update_groupmeta( $group->id, 'wds_section_code', $_POST['wds_section_code'] );
	}
	if ( isset( $_POST['wds_course_html'] ) ) {
		groups_update_groupmeta( $group->id, 'wds_course_html', $_POST['wds_course_html'] );
	}
	if ( isset( $_POST['group_project_type'] ) ) {
		groups_update_groupmeta( $group->id, 'wds_group_project_type', $_POST['group_project_type'] );
	}

	// Portfolio list display
	if ( isset( $_POST['group-portfolio-list-heading'] ) ) {
		$enabled = ! empty( $_POST['group-show-portfolio-list'] ) ? 'yes' : 'no';
		groups_update_groupmeta( $group->id, 'portfolio_list_enabled', $enabled );

		groups_update_groupmeta( $group->id, 'portfolio_list_heading', wp_strip_all_tags( stripslashes( $_POST['group-portfolio-list-heading'] ) ) );
	}

	// Feed URLs ( step two of group creation )
	if ( isset( $_POST['external-site-posts-feed'] ) || isset( $_POST['external-site-comments-feed'] ) ) {
		groups_update_groupmeta( $group->id, 'external_site_posts_feed', $_POST['external-site-posts-feed'] );
		groups_update_groupmeta( $group->id, 'external_site_comments_feed', $_POST['external-site-comments-feed'] );
	}

	// phpcs:enable WordPress.Security.NonceVerification.Missing
}
add_action( 'groups_group_after_save', 'cboxol_save_group_extras', 20 );

////////////////////////
/// MEMBERSHIP SYNC ////
////////////////////////

/**
 * Get the site role corresponding to a group role.
 *
 * @param int    $group_id   ID of the group.
 * @param int    $user_id    ID of the user.
 * @param string $group_role Optional. When absent, group role is inferred from group + user.
 * @return string
 */
function openlab_get_blog_role_for_group_role( $group_id, $user_id, $group_role = null ) {
	$role_settings = openlab_get_group_member_role_settings( $group_id );

	if ( null === $group_role ) {
		if ( groups_is_user_admin( $user_id, $group_id ) ) {
			$group_role = 'admin';
		} elseif ( groups_is_user_mod( $user_id, $group_id ) ) {
			$group_role = 'mod';
		} else {
			$group_role = 'member';
		}
	}

	return isset( $role_settings[ $group_role ] ) ? $role_settings[ $group_role ] : 'author';
}

/**
 * Gets the member role settings for a group.
 */
function openlab_get_group_member_role_settings( $group_id ) {
	$defaults = array(
		'admin'  => 'administrator',
		'mod'    => 'editor',
		'member' => 'author',
	);

	$raw_settings = groups_get_groupmeta( $group_id, 'member_site_roles' );

	if ( ! $raw_settings ) {
		$settings = $defaults;
	} else {
		$settings = array();
		foreach ( $defaults as $group_role => $site_role ) {
			$settings[ $group_role ] = isset( $raw_settings[ $group_role ] ) ? $raw_settings[ $group_role ] : $site_role;
		}
	}

	return $settings;
}

/**
 * Add user to the group blog when joining the group
 */
function openlab_add_user_to_groupblog( $group_id, $user_id, $role = null ) {
	$blog_id = cboxol_get_group_site_id( $group_id );

	if ( $blog_id ) {
		if ( null === $role ) {
			$role = openlab_get_blog_role_for_group_role( $group_id, $user_id );
		}

		if ( isset( $role ) ) {
			add_user_to_blog( $blog_id, $user_id, $role );
		}
	}
}
add_action( 'groups_join_group', 'openlab_add_user_to_groupblog', 10, 2 );

/**
 * Modify group site membership on promotion.
 *
 * @param int    $group_id ID of the group.
 * @param int    $user_id  ID of the user.
 * @param string $status   Status to which user is being promoted.
 */
function openlab_add_user_to_groupblog_on_promotion( $group_id, $user_id, $status ) {
	$role = openlab_get_blog_role_for_group_role( $group_id, $user_id, $status );
	openlab_add_user_to_groupblog( $group_id, $user_id, $role );
}
add_action( 'groups_promote_member', 'openlab_add_user_to_groupblog_on_promotion', 10, 3 );

/**
 * Modify group site membership on hooks that take group_id + user_id.
 *
 * @param int $group_id ID of the group.
 * @param int $user_id  ID of the user.
 */
function openlab_add_user_to_groupblog_on_demotion( $group_id, $user_id ) {
	$role = openlab_get_blog_role_for_group_role( $group_id, $user_id, 'member' );
	openlab_add_user_to_groupblog( $group_id, $user_id, $role );
}
add_action( 'groups_demote_member', 'openlab_add_user_to_groupblog_on_demotion', 10, 2 );
add_action( 'groups_unban_member', 'openlab_add_user_to_groupblog_on_demotion', 10, 2 );

/**
 * Join a user to a groupblog when joining the group
 *
 * This function exists because the arguments are passed to the hook in the wrong order
 *
 * @param int $user_id  ID of the user.
 * @param int $group_id ID of the group.
 */
function openlab_add_user_to_groupblog_accept( $user_id, $group_id ) {
	openlab_add_user_to_groupblog( $group_id, $user_id );
}
add_action( 'groups_membership_accepted', 'openlab_add_user_to_groupblog_accept', 10, 2 );
add_action( 'groups_accept_invite', 'openlab_add_user_to_groupblog_accept', 10, 2 );

/**
 * Sync group membership to a site at the moment that the site is linked to the group.
 *
 * @param int $group_id ID of the group.
 * @param int $site_id  ID of the site.
 */
function openlab_sync_group_site_membership( $group_id, $site_id ) {
	$group_members = groups_get_group_members(
		array(
			'group_id'            => $group_id,
			'exclude_admins_mods' => false,
			'exclude'             => array( get_current_user_id() ),
		)
	);

	foreach ( $group_members['members'] as $group_member ) {
		openlab_add_user_to_groupblog( $group_id, $group_member->user_id );
	}
}
add_action( 'cboxol_set_group_site_id', 'openlab_sync_group_site_membership', 10, 2 );

/**
 * Remove a user from a site when leaving the group.
 *
 * @param int $group_id ID of the group.
 * @param int $user_id  ID of the user.
 */
function openlab_remove_user_from_groupblog( $group_id, $user_id ) {
	$site_id = openlab_get_site_id_by_group_id( $group_id );
	if ( ! $site_id ) {
		return;
	}

	remove_user_from_blog( $user_id, $site_id );
}
add_action( 'groups_ban_member', 'openlab_remove_user_from_groupblog', 10, 2 );
add_action( 'groups_remove_member', 'openlab_remove_user_from_groupblog', 10, 2 );
add_action( 'groups_leave_group', 'openlab_remove_user_from_groupblog', 10, 2 );


////////////////////////
///     ACTIVITY     ///
////////////////////////

/**
 * Get blog posts into group streams
 */
function openlab_group_blog_activity( $activity ) {

	if ( 'new_blog_post' !== $activity->type && 'new_blog_comment' !== $activity->type ) {
		return $activity;
	}

	$blog_id = $activity->item_id;

	if ( 'new_blog_post' === $activity->type ) {
		$post_id = $activity->secondary_item_id;
		$post    = get_post( $post_id );
	} elseif ( 'new_blog_comment' === $activity->type ) {
		$comment = get_comment( $activity->secondary_item_id );
		$post_id = $comment->comment_post_ID;
		$post    = get_post( $post_id );
	}

	$group_id = openlab_get_group_id_by_blog_id( $blog_id );

	if ( ! $group_id ) {
		return $activity;
	}

	$group = groups_get_group( array( 'group_id' => $group_id ) );

	// Verify if we already have the modified activity for this blog post
	$id = bp_activity_get_activity_id(
		array(
			'user_id'           => $activity->user_id,
			'type'              => $activity->type,
			'item_id'           => $group_id,
			'secondary_item_id' => $activity->secondary_item_id,
		)
	);

	// if we don't have, verify if we have an original activity
	if ( ! $id ) {
		$id = bp_activity_get_activity_id(
			array(
				'user_id'           => $activity->user_id,
				'type'              => $activity->type,
				'item_id'           => $activity->item_id,
				'secondary_item_id' => $activity->secondary_item_id,
			)
		);
	}

	// If we found an activity for this blog post, then overwrite it to
	// avoid have multiple activities for every blog post edit.
	//
	// Here we'll also prevent email notifications from being sent
	if ( $id ) {
		$activity->id = $id;
		remove_action( 'bp_activity_after_save', 'ass_group_notification_activity', 50 );
	}

	// Replace the necessary values to display in group activity stream
	if ( 'new_blog_post' === $activity->type ) {
		$activity->action = sprintf(
			// translators: 1. Link to author, 2. Link to post, 3. Link to group
			__( '%1$s wrote a new blog post %2$s in the group %3$s', 'commons-in-a-box' ),
			bp_core_get_userlink( $activity->user_id ),
			'<a href="' . get_permalink( $post->ID ) . '">' . esc_html( $post->post_title ) . '</a>',
			'<a href="' . bp_get_group_permalink( $group ) . '">' . esc_html( $group->name ) . '</a>'
		);
	} else {
		$userlink = '';
		if ( $activity->user_id ) {
			$userlink = bp_core_get_userlink( $activity->user_id );
		} else {
			$userlink = '<a href="' . esc_attr( $comment->comment_author_url ) . '">' . esc_html( $comment->comment_author ) . '</a>';
		}
		$activity->action = sprintf(
			// translators: 1. Link to commenter, 2. Link to post, 3. Link to group
			__( '%1$s commented on %2$s in the group %3$s', 'commons-in-a-box' ),
			$userlink,
			'<a href="' . get_permalink( $post->ID ) . '">' . esc_html( $post->post_title ) . '</a>',
			'<a href="' . bp_get_group_permalink( $group ) . '">' . esc_html( $group->name ) . '</a>'
		);
	}

	$activity->item_id   = (int) $group_id;
	$activity->component = 'groups';

	$public = get_blog_option( $blog_id, 'blog_public' );

	if ( 0 > (float) $public ) {
		$activity->hide_sitewide = 1;
	} else {
		$activity->hide_sitewide = 0;
	}

	// Mark the group as having been active
	groups_update_groupmeta( $group_id, 'last_activity', bp_core_current_time() );

	// prevent infinite loops, but let this function run on later activities ( for unit tests )
	remove_action( 'bp_activity_before_save', 'openlab_group_blog_activity' );
	add_action(
		'bp_activity_after_save',
		function() {
			add_action( 'bp_activity_before_save', 'openlab_group_blog_activity' );
		}
	);

	return $activity;
}
add_action( 'bp_activity_before_save', 'openlab_group_blog_activity' );

/**
 * When a blog post is deleted, remove the corresponding activity item
 *
 * We have to do this manually because the activity filter in
 * bp_blogs_remove_post() does not align with the schema imposed by OL's
 * groupblog hacks
 *
 * See #850
 */
function openlab_group_blog_remove_activity( $post_id, $blog_id = 0, $user_id = 0 ) {
	global $wpdb, $bp;

	if ( empty( $wpdb->blogid ) ) {
		return false;
	}

	$post_id = (int) $post_id;

	if ( ! $blog_id ) {
		$blog_id = (int) $wpdb->blogid;
	}

	if ( ! $user_id ) {
		$user_id = bp_loggedin_user_id();
	}

	$group_id = openlab_get_group_id_by_blog_id( $blog_id );

	if ( $group_id ) {
		// Delete activity stream item
		bp_blogs_delete_activity(
			array(
				'item_id'           => $group_id,
				'secondary_item_id' => $post_id,
				'component'         => 'groups',
				'type'              => 'new_blog_comment',
			)
		);
	}
}
add_action( 'delete_post', 'openlab_group_blog_remove_activity' );
add_action( 'trash_post', 'openlab_group_blog_remove_activity' );

/**
 * When a blog comment is deleted, remove the corresponding activity item
 *
 * We have to do this manually because the activity filter in
 * bp_blogs_remove_comment() does not align with the schema imposed by OL's
 * groupblog hacks
 *
 * See #850
 */
function openlab_group_blog_remove_comment_activity( $comment_id ) {
	global $wpdb, $bp;

	if ( empty( $wpdb->blogid ) ) {
		return false;
	}

	$comment_id = (int) $comment_id;
	$blog_id    = (int) $wpdb->blogid;

	$group_id = openlab_get_group_id_by_blog_id( $blog_id );

	if ( $group_id ) {
		// Delete activity stream item
		bp_blogs_delete_activity(
			array(
				'item_id'           => $group_id,
				'secondary_item_id' => $post_id,
				'component'         => 'groups',
				'type'              => 'new_blog_comment',
			)
		);
	}
}
add_action( 'delete_comment', 'openlab_group_blog_remove_comment_activity' );
add_action( 'trash_comment', 'openlab_group_blog_remove_comment_activity' );
add_action( 'spam_comment', 'openlab_group_blog_remove_comment_activity' );

/**
 * Overrides BP's default behavior, which hardcodes blog_public = 0 checks.
 *
 * Can be refactored after https://buddypress.trac.wordpress.org/ticket/4831#comment:10
 */
function cboxol_blogs_post_pre_publish( $return = true, $blog_id = 0, $post_id = 0, $user_id = 0 ) {
	$bp = buddypress();

	// If blog is not trackable, or we are installing, do not record the activity.
	if ( ! function_exists( 'bp_blogs_is_blog_trackable' ) || ! bp_blogs_is_blog_trackable( $blog_id, $user_id ) ) {
		return false;
	}

	/*
	 * Stop infinite loops with WordPress MU Sitewide Tags.
	 * That plugin changed the way its settings were stored at some point. Thus the dual check.
	 */
	$sitewide_tags_blog_settings = bp_core_get_root_option( 'sitewide_tags_blog' );
	if ( ! empty( $sitewide_tags_blog_settings ) ) {
		$st_options   = maybe_unserialize( $sitewide_tags_blog_settings );
		$tags_blog_id = isset( $st_options['tags_blog_id'] ) ? $st_options['tags_blog_id'] : 0;
	} else {
		$tags_blog_id = bp_core_get_root_option( 'sitewide_tags_blog' );
		$tags_blog_id = intval( $tags_blog_id );
	}

	/**
	 * Filters whether or not BuddyPress should block sitewide tags activity.
	 *
	 * @since 2.2.0
	 *
	 * @param bool $value Current status of the sitewide tags activity.
	 */
	if ( (int) $blog_id === (int) $tags_blog_id && apply_filters( 'bp_blogs_block_sitewide_tags_activity', true ) ) {
		return false;
	}

	return $return;
}
remove_filter( 'bp_activity_post_pre_publish', 'bp_blogs_post_pre_publish', 10, 4 );
remove_filter( 'bp_activity_post_pre_comment', 'bp_blogs_post_pre_publish', 10, 4 );
add_filter( 'bp_activity_post_pre_publish', 'cboxol_blogs_post_pre_publish', 10, 4 );
add_filter( 'bp_activity_post_pre_comment', 'cboxol_blogs_post_pre_publish', 10, 4 );

/**
 * Ensure that 0 blog_public is bypassed by BP when registering post type activity support.
 *
 * Can be refactored after https://buddypress.trac.wordpress.org/ticket/4831#comment:10
 */
add_action(
	'bp_setup_globals',
	function() {

		/**
		 * Filters the post types to track for the Blogs component.
		 *
		 * @since 1.5.0
		 * @deprecated 2.3.0
		 *
		 * @param array $value Array of post types to track.
		 */
		$post_types = apply_filters( 'bp_blogs_record_post_post_types', array( 'post' ) );

		foreach ( $post_types as $post_type ) {
			if ( ! post_type_supports( $post_type, 'buddypress-activity' ) ) {
				add_post_type_support( $post_type, 'buddypress-activity' );
			}
		}
	},
	100
);

/**
 * Ensure that hide_sitewide is set conservatively for groupblog post items.
 *
 * Always choose the more private of the two settings: group + blog_public.
 */
function cboxol_set_groupblog_activity_hide_sitewide( $activity ) {
	if ( 'new_blog_post' !== $activity->type && 'new_blog_comment' !== $activity->type ) {
		return;
	}

	if ( $activity->hide_sitewide ) {
		return;
	}

	$group = groups_get_group( $activity->item_id );
	if ( 'public' !== $group->status ) {
		$activity->hide_sitewide = true;
		return;
	}

	$site_id     = openlab_get_site_id_by_group_id( $activity->item_id );
	$blog_public = (int) get_blog_option( $site_id, 'blog_public' );
	if ( 1 !== $blog_public ) {
		$activity->hide_sitewide = true;
	}
}
add_action( 'bp_activity_before_save', 'cboxol_set_groupblog_activity_hide_sitewide' );

////////////////////////
///  MISCELLANEOUS   ///
////////////////////////

/**
 * Get the base for building subdomain URLs.
 */
function cboxol_get_subdomain_base() {
	$current_network = get_network();
	return preg_replace( '|^www\.|', '', $current_network->domain );
}

/**
 * Catch 'unlink-site' requests, process, and send back
 */
function openlab_process_unlink_site() {
	if ( bp_is_group_admin_page() && bp_is_action_variable( 'unlink-site', 1 ) ) {
		check_admin_referer( 'unlink-site' );

		$meta_to_delete = array(
			'external_site_url',
			'cboxol_group_site_id',
			'external_site_comments_feed',
			'external_site_posts_feed',
		);

		foreach ( $meta_to_delete as $m ) {
			groups_delete_groupmeta( bp_get_current_group_id(), $m );
		}
	}
}

add_action( 'bp_actions', 'openlab_process_unlink_site', 1 );


/**
 * Server side group blog URL validation
 *
 * When you attempt to create a groupblog, this function catches the request and checks to make sure
 * that the URL is not used. If it is, an error is sent back.
 */
function openlab_validate_groupblog_url() {
	global $current_blog;

	/**
	 * This is terrifying.
	 * We check for a groupblog in the following cases:
	 * a)  'new' == $_POST['new_or_old'] || 'clone' == $_POST['new_or_old'], and either
	 * b1) the 'Set up a site?' checkbox has been checked, OR
	 * b2) the group type is Portfolio, which requires a blog
	 */
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$group_type               = isset( $_POST['group-type'] ) ? cboxol_get_group_type( wp_unslash( urldecode( $_POST['group-type'] ) ) ) : null;
	$group_type_requires_site = false;
	if ( $group_type && ! is_wp_error( $group_type ) && $group_type->get_requires_site() ) {
		$group_type_requires_site = true;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$new_or_old = isset( $_POST['new_or_old'] ) && in_array( $_POST['new_or_old'], array( 'new', 'clone' ), true ) ? wp_unslash( $_POST['new_or_old'] ) : '';
	if (
		$new_or_old &&
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		( isset( $_POST['set-up-site-toggle'] ) || $group_type_requires_site )
	) {
		// Which field we check depends on whether this is a clone
		$path = '';
		if ( 'clone' === $new_or_old ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$path = wp_unslash( $_POST['clone-destination-path'] );
		} else {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$path = wp_unslash( $_POST['blog']['domain'] );
		}

		if ( empty( $path ) ) {
			bp_core_add_message( 'Your site URL cannot be blank.', 'error' );
			bp_core_redirect( wp_guess_url() );
		}

		$validated = cboxol_validate_blogname( $path );
		if ( ! $validated['validated'] ) {
			bp_core_add_message( $validated['error'], 'error' );
			bp_core_redirect( bp_get_requested_url() );
		}
	}
}
add_action( 'bp_actions', 'openlab_validate_groupblog_url', 1 );

/**
 * For groupblog types other than 'Create a new site', perform basic validation
 */
function openlab_validate_groupblog_selection() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( isset( $_POST['new_or_old'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		switch ( $_POST['new_or_old'] ) {
			case 'old':
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( empty( $_POST['groupblog-blogid'] ) ) {
					$error_message = 'You must select an existing site from the dropdown menu.';
				}
				break;

			case 'external':
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( empty( $_POST['external-site-url'] ) || ! openlab_validate_url( $_POST['external-site-url'] ) || 'http://' === trim( $_POST['external-site-url'] ) ) {
					$error_message = 'You must provide a valid external site URL.';
				}
				break;
		}

		if ( isset( $error_message ) ) {
			bp_core_add_message( $error_message, 'error' );
			bp_core_redirect( wp_guess_url() );
		}
	}
}
add_action( 'bp_actions', 'openlab_validate_groupblog_selection', 1 );

/**
 * Handler for AJAX group blog URL validation
 */
function openlab_validate_groupblog_url_handler() {
	global $current_blog;

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$slug      = isset( $_POST['path'] ) ? wp_unslash( $_POST['path'] ) : '';
	$validated = cboxol_validate_blogname( $slug );

	if ( $validated['validated'] ) {
		wp_send_json_success();
	} else {
		wp_send_json_error(
			array(
				'error' => esc_html( $validated['error'] ),
			)
		);
	}
}

add_action( 'wp_ajax_openlab_validate_groupblog_url_handler', 'openlab_validate_groupblog_url_handler' );

/**
 * The following function overrides the BP_Blogs_Blog::get() in function bp_blogs_get_blogs(),
 * when looking at the my-sites page, so that the only blogs shown are those without a group
 * attached to them.
 */
function openlab_filter_groupblogs_from_my_sites( $blogs, $params ) {

	// Note: It may be desirable to expand the locations where this filtering happens
	// I'm just playing it safe for the time being
	if ( ! is_page( 'my-sites' ) ) {
		return $blogs;
	}

	global $bp, $wpdb;

	// Set up the necessary variables for the rest of the function, out of $params
	$type         = $params['type'];
	$limit        = $params['per_page'];
	$page         = $params['page'];
	$user_id      = $params['user_id'];
	$search_terms = $params['search_terms'];

	// The magic: Pull up a list of blogs that have associated groups, and exclude them
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$exclude_blogs = $wpdb->get_col( "SELECT meta_value FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'cboxol_group_site_id'" );

	if ( ! empty( $exclude_blogs ) ) {
		$exclude_sql = ' AND b.blog_id NOT IN ( ' . implode( ',', $exclude_blogs ) . ' ) ';
	} else {
		$exclude_sql = '';
	}

	if ( ! is_user_logged_in() || ( ! is_super_admin() && ( (int) bp_loggedin_user_id() !== (int) $user_id ) ) ) {
		$hidden_sql = 'AND wb.public = 1';
	} else {
		$hidden_sql = '';
	}

	$pag_sql = ( $limit && $page ) ? $wpdb->prepare( ' LIMIT %d, %d', intval( ( $page - 1 ) * $limit ), intval( $limit ) ) : '';

	$user_sql = ! empty( $user_id ) ? $wpdb->prepare( ' AND b.user_id = %d', $user_id ) : '';

	switch ( $type ) {
		case 'active':
		default:
			$order_sql = 'ORDER BY bm.meta_value DESC';
			break;
		case 'alphabetical':
			$order_sql = 'ORDER BY bm2.meta_value ASC';
			break;
		case 'newest':
			$order_sql = 'ORDER BY wb.registered DESC';
			break;
		case 'random':
			$order_sql = 'ORDER BY RAND()';
			break;
	}

	if ( ! empty( $search_terms ) ) {
		// phpcs:ignore WordPress.WP.DeprecatedFunctions.like_escapeFound
		$filter = like_escape( $wpdb->escape( $search_terms ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$paged_blogs = $wpdb->get_results( "SELECT b.blog_id, b.user_id as admin_user_id, u.user_email as admin_user_email, wb.domain, wb.path, bm.meta_value as last_activity, bm2.meta_value as name FROM {$bp->blogs->table_name} b, {$bp->blogs->table_name_blogmeta} bm, {$bp->blogs->table_name_blogmeta} bm2, {$wpdb->base_prefix}blogs wb, {$wpdb->users} u WHERE b.blog_id = wb.blog_id AND b.user_id = u.ID AND b.blog_id = bm.blog_id AND b.blog_id = bm2.blog_id AND wb.archived = '0' AND wb.spam = 0 AND wb.mature = 0 AND wb.deleted = 0 {$hidden_sql} AND bm.meta_key = 'last_activity' AND bm2.meta_key = 'name' AND bm2.meta_value LIKE '%%$filter%%' {$user_sql} {$exclude_sql} GROUP BY b.blog_id {$order_sql} {$pag_sql}" );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total_blogs = $wpdb->get_var( "SELECT COUNT( DISTINCT b.blog_id ) FROM {$bp->blogs->table_name} b, {$wpdb->base_prefix}blogs wb, {$bp->blogs->table_name_blogmeta} bm, {$bp->blogs->table_name_blogmeta} bm2 WHERE b.blog_id = wb.blog_id AND bm.blog_id = b.blog_id AND bm2.blog_id = b.blog_id AND wb.archived = '0' AND wb.spam = 0 AND wb.mature = 0 AND wb.deleted = 0 {$hidden_sql} AND bm.meta_key = 'name' AND bm2.meta_key = 'description' AND ( bm.meta_value LIKE '%%$filter%%' || bm2.meta_value LIKE '%%$filter%%' ) {$user_sql} {$exclude_sql}" );
	} else {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$paged_blogs = $wpdb->get_results( "SELECT b.blog_id, b.user_id as admin_user_id, u.user_email as admin_user_email, wb.domain, wb.path, bm.meta_value as last_activity, bm2.meta_value as name FROM {$bp->blogs->table_name} b, {$bp->blogs->table_name_blogmeta} bm, {$bp->blogs->table_name_blogmeta} bm2, {$wpdb->base_prefix}blogs wb, {$wpdb->users} u WHERE b.blog_id = wb.blog_id AND b.user_id = u.ID AND b.blog_id = bm.blog_id AND b.blog_id = bm2.blog_id {$user_sql} AND wb.archived = '0' AND wb.spam = 0 AND wb.mature = 0 AND wb.deleted = 0 {$hidden_sql} {$exclude_sql} AND bm.meta_key = 'last_activity' AND bm2.meta_key = 'name' GROUP BY b.blog_id {$order_sql} {$pag_sql}" );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total_blogs = $wpdb->get_var( "SELECT COUNT( DISTINCT b.blog_id ) FROM {$bp->blogs->table_name} b, {$wpdb->base_prefix}blogs wb WHERE b.blog_id = wb.blog_id {$user_sql} AND wb.archived = '0' AND wb.spam = 0 AND wb.mature = 0 AND wb.deleted = 0 {$hidden_sql} {$exclude_sql}" );
	}

	$blog_ids = array();
	foreach ( (array) $paged_blogs as $blog ) {
		$blog_ids[] = $blog->blog_id;
	}

	$blog_ids    = $wpdb->escape( join( ',', (array) $blog_ids ) );
	$paged_blogs = BP_Blogs_Blog::get_blog_extras( $paged_blogs, $blog_ids, $type );

	return array(
		'blogs' => $paged_blogs,
		'total' => $total_blogs,
	);
}

add_filter( 'bp_blogs_get_blogs', 'openlab_filter_groupblogs_from_my_sites', 10, 2 );

/**
 * This function checks the blog_public option of the group site, and depending on the result,
 * returns whether the current user can view the site.
 */
function cboxol_site_can_be_viewed( $group_id = null ) {
	global $user_ID, $wpdb;

	// External sites can always be viewed
	if ( openlab_get_external_site_url_by_group_id() ) {
		return true;
	}

	if ( null === $group_id ) {
		$group_id = bp_get_group_id();
	}

	$blog_public          = false;
	$wds_bp_group_site_id = cboxol_get_group_site_id( $group_id );

	if ( $wds_bp_group_site_id ) {
		$blog_private = get_blog_option( $wds_bp_group_site_id, 'blog_public' );
		$blog_prefix  = $wpdb->get_blog_prefix( $wds_bp_group_site_id );

		switch ( $blog_private ) {
			case '-3':
				if ( is_user_logged_in() ) {
					$user_capabilities = get_user_meta( $user_ID, $blog_prefix . 'capabilities', true );
					if ( isset( $user_capabilities['administrator'] ) ) {
						$blog_public = true;
					}
				}
				break;

			case '-2':
				if ( is_user_logged_in() ) {
					$user_capabilities = get_user_meta( $user_ID, $blog_prefix . 'capabilities', true );
					if ( '' !== $user_capabilities ) {
						$blog_public = true;
					}
				}
				break;

			case '-1':
				if ( is_user_logged_in() ) {
					$blog_public = true;
				}
				break;

			default:
				$blog_public = true;
				break;
		}
	}
	return $blog_public;
}

////////////////////////
///  EXTERNAL SITES  ///
////////////////////////

/**
 * Wrapper function to get the URL of an external site, if it exists
 */
function openlab_get_external_site_url_by_group_id( $group_id = 0 ) {
	if ( 0 === (int) $group_id ) {
		$group_id = bp_get_current_group_id();
	}

	$external_site_url = groups_get_groupmeta( $group_id, 'external_site_url' );

	return $external_site_url;
}

/**
 * Given a group id, fetch its external posts
 *
 * Attempts to fetch from a transient before refreshing
 */
function openlab_get_external_posts_by_group_id( $group_id = 0 ) {
	if ( 0 === (int) $group_id ) {
		$group_id = bp_get_current_group_id();
	}

	// Check transients first
	$posts = get_transient( 'openlab_external_posts_' . $group_id );

	if ( empty( $posts ) ) {
		$feed_url = groups_get_groupmeta( $group_id, 'external_site_posts_feed' );

		if ( $feed_url ) {
			$posts = openlab_format_rss_items( $feed_url );
			set_transient( 'openlab_external_posts_' . $group_id, $posts, 60 * 10 );

			// Translate the feed items into activity items
			openlab_convert_feed_to_activity( $posts, 'posts' );
		}
	}

	return $posts;
}

/**
 * Given a group id, fetch its external comments
 *
 * Attempts to fetch from a transient before refreshing
 */
function openlab_get_external_comments_by_group_id( $group_id = 0 ) {
	if ( 0 === (int) $group_id ) {
		$group_id = bp_get_current_group_id();
	}

	// Check transients first
	$comments = get_transient( 'openlab_external_comments_' . $group_id );

	if ( empty( $comments ) ) {
		$feed_url = groups_get_groupmeta( $group_id, 'external_site_comments_feed' );

		if ( $feed_url ) {
			$comments = openlab_format_rss_items( $feed_url );
			set_transient( 'openlab_external_comments_' . $group_id, $comments, 60 * 10 );

			// Translate the feed items into activity items
			openlab_convert_feed_to_activity( $comments, 'comments' );
		}
	}

	return $comments;
}

/**
 * Given an RSS feed URL, fetch the items and parse into an array containing permalink, title,
 * and content
 */
function openlab_format_rss_items( $feed_url, $num_items = 3 ) {
	$feed_posts = fetch_feed( $feed_url );

	if ( empty( $feed_posts ) || is_wp_error( $feed_posts ) ) {
		return;
	}

	$items = array();

	foreach ( $feed_posts->get_items( 0, $num_items ) as $key => $feed_item ) {
		$items[] = array(
			'permalink' => $feed_item->get_link(),
			'title'     => $feed_item->get_title(),
			'content'   => wp_strip_all_tags( bp_create_excerpt( $feed_item->get_content(), 135, array( 'html' => true ) ) ),
			'author'    => $feed_item->get_author(),
			'date'      => $feed_item->get_date(),
		);
	}

	return $items;
}

/**
 * Convert RSS items to activity items
 */
function openlab_convert_feed_to_activity( $items = array(), $item_type = 'posts' ) {
	$type  = 'posts' === $item_type ? 'new_blog_post' : 'new_blog_comment';
	$group = groups_get_current_group();

	$hide_sitewide = false;
	if ( ! empty( $group ) && isset( $group->status ) && 'public' !== $group->status ) {
		$hide_sitewide = true;
	}

	$group_id = ! empty( $group ) ? $group->id : '';

	foreach ( (array) $items as $item ) {
		// Make sure we don't have duplicates
		// We check based on the item's permalink
		if ( ! openlab_external_activity_item_exists( $item['permalink'], $group_id, $type ) ) {
			$action = '';

			$group           = groups_get_current_group();
			$group_name      = $group->name;
			$group_permalink = bp_get_group_permalink( $group );

			$group_link = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $group_permalink ),
				esc_html( $group_name )
			);

			$post_link = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $item['permalink'] ),
				esc_html( $item['title'] )
			);

			if ( 'posts' === $item_type ) {
				/* translators: 1. Post link, 2. Group link */
				$action = sprintf( __( 'A new post %1$s was published in %2$s', 'commons-in-a-box' ), $post_link, $group_link );
			} elseif ( 'comments' === $item_type ) {
				/* translators: 1. Post link, 2. Group link */
				$action = sprintf( __( 'A new comment was posted on the post %1$s in %2$s', 'commons-in-a-box' ), $post_link, $group_link );
			}

			$item_date = strtotime( $item['date'] );
			$now       = time();
			if ( $item_date > $now ) {
				$item_date = $now;
			}
			$recorded_time = gmdate( 'Y-m-d H:i:s', $item_date );

			$args = array(
				'action'        => $action,
				'content'       => $item['content'],
				'component'     => 'groups',
				'type'          => $type,
				'primary_link'  => $item['permalink'],
				'user_id'       => 0, // todo
				'item_id'       => bp_get_current_group_id(), // improve?
				'recorded_time' => $recorded_time,
				'hide_sitewide' => $hide_sitewide,
			);

			remove_action( 'bp_activity_before_save', 'openlab_group_blog_activity' );
			bp_activity_add( $args );
		}
	}
}

/**
 * Check to see whether an external blog post activity item exists for this item already
 *
 * @param str Permalink of original post
 * @param int Associated group id
 * @param str Activity type ( new_blog_post, new_blog_comment )
 * @return bool
 */
function openlab_external_activity_item_exists( $permalink, $group_id, $type ) {
	global $wpdb, $bp;

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$sql = $wpdb->prepare( "SELECT id FROM {$bp->activity->table_name} WHERE primary_link = %s AND type = %s AND component = 'groups' AND item_id = %s", $permalink, $type, $group_id );

	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	return (bool) $wpdb->get_var( $sql );
}

/**
 * Validate a URL format
 */
function openlab_validate_url( $url ) {
	if ( 0 !== strpos( $url, 'http' ) ) {
		// Let's guess that http was left off
		$url = 'http://' . $url;
	}

	$url = trailingslashit( $url );

	return $url;
}

/**
 * Given a site URL, try to get feed URLs
 */
function openlab_find_feed_urls( $url ) {

	$url = trailingslashit( $url );

	// Supported formats
	$formats = array(
		'wordpress' => array(
			'posts'    => '{{URL}}feed/',
			'comments' => '{{URL}}comments/feed/',
		),
		'blogger'   => array(
			'posts'    => '{{URL}}feeds/posts/default?alt=rss',
			'comments' => '{{URL}}feeds/comments/default?alt=rss',
		),
		'drupal'    => array(
			'posts' => '{{URL}}posts/feed',
		),
	);

	$feed_urls = array();

	foreach ( $formats as $ftype => $f ) {
		$maybe_feed_url = str_replace( '{{URL}}', trailingslashit( $url ), $f['posts'] );

		// Do a HEAD check first to avoid loops when self-querying.
		$maybe_feed_head = wp_remote_head(
			$maybe_feed_url,
			array(
				'redirection' => 2,
			)
		);

		if ( 200 !== wp_remote_retrieve_response_code( $maybe_feed_head ) ) {
			continue;
		}

		$maybe_feed = wp_remote_get( $maybe_feed_url );
		if ( ! is_wp_error( $maybe_feed ) && 200 === (int) $maybe_feed['response']['code'] ) {

			// Check to make sure this is actually a feed
			$feed_items = fetch_feed( $maybe_feed_url );
			if ( is_wp_error( $feed_items ) ) {
				continue;
			}

			$feed_urls['posts'] = $maybe_feed_url;
			$feed_urls['type']  = $ftype;

			// Test the comment feed
			if ( isset( $f['comments'] ) ) {
				$maybe_comments_feed_url = str_replace( '{{URL}}', trailingslashit( $url ), $f['comments'] );
				$maybe_comments_feed     = wp_remote_get(
					$maybe_comments_feed_url,
					array(
						'redirection' => 2,
					)
				);

				if ( 200 === (int) $maybe_comments_feed['response']['code'] ) {
					$feed_urls['comments'] = $maybe_comments_feed_url;
				}
			}

			break;
		}
	}

	return $feed_urls;
}

/**
 * AJAX handler for feed detection
 */
function openlab_detect_feeds_handler() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$url   = isset( $_REQUEST['site_url'] ) ? $_REQUEST['site_url'] : '';
	$feeds = openlab_find_feed_urls( $url );

	die( wp_json_encode( $feeds ) );
}

add_action( 'wp_ajax_openlab_detect_feeds', 'openlab_detect_feeds_handler' );

/**
 * Catch feed refresh requests and processem
 */
function openlab_catch_refresh_feed_requests() {
	if ( ! bp_is_group() ) {
		return;
	}

	if ( ! isset( $_GET['refresh_feed'] ) || ! in_array( $_GET['refresh_feed'], array( 'posts', 'comments' ), true ) ) {
		return;
	}

	if ( ! groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id() ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$feed_type = $_GET['refresh_feed'];

	check_admin_referer( 'refresh-' . $feed_type . '-feed' );

	delete_transient( 'openlab_external_' . $feed_type . '_' . bp_get_current_group_id() );
	call_user_func( 'openlab_get_external_' . $feed_type . '_by_group_id' );
}

add_action( 'bp_actions', 'openlab_catch_refresh_feed_requests' );

/**
 * Map "instructor" status to group administrator for wp-grade-comments.
 */
function openlab_olgc_is_instructor( $is ) {
	$group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );
	return groups_is_user_admin( get_current_user_id(), $group_id );
}
add_filter( 'olgc_is_instructor', 'openlab_olgc_is_instructor' );

// Disable admin notices for wp-grade-comments.
add_filter( 'olgc_display_notices', '__return_false' );

/**
 * WP Grade Comments fallback.
 *
 * Hide private comments even after the plugin was deactivated.
 *
 * @return void
 */
function openlab_olgc_fallback( WP_Comment_Query $query ) {
	// Make private comments visible for admins in the dashboard.
	if ( is_admin() && current_user_can( 'manage_options' ) ) {
		return;
	}

	$plugin = 'wp-grade-comments/wp-grade-comments.php';
	if ( in_array( $plugin, (array) get_option( 'active_plugins', [] ), true ) ) {
		return;
	}

	/**
	 * Have to override meta query.
	 *
	 * See: https://core.trac.wordpress.org/ticket/32762
	 */
	$query->meta_query = new WP_Meta_Query(
		[
			'relation' => 'OR',
			[
				'key'   => 'olgc_is_private',
				'value' => '0',
			],
			[
				'key'     => 'olgc_is_private',
				'compare' => 'NOT EXISTS',
			],
		]
	);
}
add_action( 'pre_get_comments', 'openlab_olgc_fallback' );

/**
 * Email the course instructor when a wp-grade-comments "private" comment is posted.
 *
 * @param int        $comment_id ID of the comment.
 * @param WP_Comment $comment    Comment object.
 */
function openlab_olgc_notify_instructor( $comment_id, $comment ) {
	$is_private = get_comment_meta( $comment_id, 'olgc_is_private', true );
	if ( ! $is_private ) {
		return;
	}

	$group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );
	if ( ! $group_id ) {
		return;
	}

	$admins = groups_get_group_admins( $group_id );
	if ( ! $admins ) {
		return;
	}

	// Sanity check.
	$comment_author_user = get_user_by( 'email', $comment->comment_author_email );
	if ( ! $comment_author_user ) {
		return;
	}

	$subject = sprintf( 'New private comment on %s', get_option( 'blogname' ) );

	$post    = get_post( $comment->comment_post_ID );
	$message = sprintf(
		'There is a new private comment on your site %s.

Post name: %s
Comment author: %s
Comment URL: %s',
		get_option( 'blogname' ),
		$post->post_title,
		bp_core_get_user_displayname( $comment_author_user->ID ),
		get_comment_link( $comment )
	);

	foreach ( $admins as $admin ) {
		// Don't send notification to instructor of her own comment.
		if ( (int) $admin->user_id === (int) $comment_author_user->ID ) {
			continue;
		}

		$admin_user = get_user_by( 'id', $admin->user_id );
		if ( ! $admin_user ) {
			continue;
		}

		wp_mail( $admin_user->user_email, $subject, $message );
	}
}
add_action( 'wp_insert_comment', 'openlab_olgc_notify_instructor', 20, 2 );

/**
 * Show a notice on the dashboard of cloned course sites.
 */
function openlab_cloned_course_notice() {
	global $current_blog;

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Don't show for sites created before 2016-03-09.
	$latest     = new DateTime( '2016-03-09' );
	$registered = new DateTime( $current_blog->registered );
	if ( $latest > $registered ) {
		return;
	}

	// Allow dismissal.
	if ( get_option( 'openlab-clone-notice-dismissed' ) ) {
		return;
	}

	// Only show for cloned courses.
	$group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );
	if ( ! groups_get_groupmeta( $group_id, 'clone_source_group_id' ) ) {
		return;
	}

	// Groan
	$dismiss_url = $_SERVER['REQUEST_URI'];
	$nonce       = wp_create_nonce( 'ol_clone_dismiss' );
	$dismiss_url = add_query_arg( 'ol-clone-dismiss', '1', $dismiss_url );
	$dismiss_url = add_query_arg( '_wpnonce', $nonce, $dismiss_url );

	$posts_url = admin_url( 'edit.php' );
	$pages_url = admin_url( 'edit.php?post_type=page' );
	$menus_url = admin_url( 'nav-menus.php' );

	?>
	<style type="text/css">
		.ol-cloned-message {
			position: relative;
		}
		.ol-cloned-message > p > span {
			width: 80%;
		}
		.ol-clone-message-dismiss {
			position: absolute;
			right: 15px;
		}
	</style>
	<div class="updated fade ol-cloned-message">
		<p>
			<span>
				<?php
				printf(
					// translators: 1. Link to Posts admin page, 2. Link to Pages admin page, 3. Link to Menus admin page
					esc_html__( 'Please Note: Posts and pages from the site you cloned are set to "draft" until you publish or delete them via %1$s and %2$s. Custom menus will need to be reactivated via %3$s.', 'commons-in-a-box' ),
					sprintf( '<a href="%s">%s</a>', esc_url( $posts_url ), esc_html__( 'Posts', 'commons-in-a-box' ) ),
					sprintf( '<a href="%s">%s</a>', esc_url( $pages_url ), esc_html__( 'Pages', 'commons-in-a-box' ) ),
					sprintf( '<a href="%s">%s</a>', esc_url( $menus_url ), esc_html__( 'Appearance > Menu', 'commons-in-a-box' ) )
				);
				?>
			</span>
			<a class="ol-clone-message-dismiss" href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'Dismiss', 'commons-in-a-box' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'openlab_cloned_course_notice' );

/**
 * Catch cloned course notice dismissals.
 */
function openlab_catch_cloned_course_notice_dismissals() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( empty( $_GET['ol-clone-dismiss'] ) ) {
		return;
	}

	check_admin_referer( 'ol_clone_dismiss' );

	update_option( 'openlab-clone-notice-dismissed', 1 );
}
add_action( 'admin_init', 'openlab_catch_cloned_course_notice_dismissals' );

/**
 * Add a widget to the "main" sidebar.
 *
 * This function includes some guesswork about what the "main" sidebar is, based on the theme.
 *
 * @since 1.2.0
 *
 * @param string $widget
 */
function openlab_add_widget_to_main_sidebar( $widget ) {
	switch ( get_template() ) {
		case 'hemingway':
		case 'genesis':
			$sidebar = 'sidebar';
			break;

		case 'twentyten':
			$sidebar = 'primary-widget-area';
			break;

		case 'gillian':
		case 'twentyfifteen':
		case 'twentyfourteen':
		case 'twentyeleven':
		case 'twentyseventeen':
		case 'twentysixteen':
		case 'twentythirteen':
		case 'twentytwelve':
			$sidebar = 'sidebar-1';
			break;

		default:
			$sidebar = null;
			if ( isset( $GLOBALS['wp_registered_sidebars'] ) ) {
				$sidebars = array_keys( $GLOBALS['wp_registered_sidebars'] );
				$sidebar  = $sidebars[0];
			}
			break;
	}

	// No doubles.
	$sidebars = get_option( 'sidebars_widgets', array() );
	$already  = false;
	if ( ! empty( $sidebars[ $sidebar ] ) ) {
		foreach ( $sidebars[ $sidebar ] as $widget_id ) {
			if ( 0 === strpos( $widget_id, $widget ) ) {
				$already = true;
				break;
			}
		}
	}

	if ( $already ) {
		return;
	}

	if ( ! class_exists( 'CBox_Widget_Setter' ) ) {
		require CBOXOL_PLUGIN_DIR . '/lib/cbox-widget-setter.php';
	}

	CBox_Widget_Setter::set_widget(
		array(
			'id_base'    => $widget,
			'sidebar_id' => $sidebar,
		)
	);
}

/**
 * Copy blog from a template.
 *
 * @todo Merge with course copy code, which is better than this.
 *
 * @param int $group_id
 */
function cboxol_copy_blog_page( $group_id ) {
	global $bp, $wpdb, $current_site, $user_email;

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$blog = isset( $_POST['blog'] ) ? $_POST['blog'] : array();

	if ( empty( $blog['domain'] ) ) {
		return;
	}

	$current_user = wp_get_current_user();
	$group        = groups_get_group( $group_id );

	// Validate.
	$validate = wpmu_validate_blog_signup( $blog['domain'], $group->name, $current_user );

	$error_codes = $validate['errors']->get_error_codes();
	if ( ! empty( $error_codes ) ) {
		return $validate;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$src_id = intval( $_POST['source_blog'] );

	$title = $group->name;

	$msg = '';
	if ( ! $src_id ) {
		$msg = __( 'Select a source blog.', 'commons-in-a-box' );
	}

	if ( $msg ) {
		return $msg;
	}

	$wpdb->hide_errors();
	$new_id = wpmu_create_blog( $validate['domain'], $validate['path'], $validate['blog_title'], $current_user->ID, array( 'public' => 1 ), $current_site->id );
	$id     = $new_id;

	$wpdb->show_errors();

	if ( is_wp_error( $id ) ) {
		return $id;
	}

	cboxol_set_group_site_id( $group_id, $id );

	$template_id = get_site_meta( (int) $src_id, '_site_template_id', true );

	/**
	 * Save "Site Template" ID if we have one.
	 *
	 * We're saving this with a different meta key, mainly to avoid syncing side effects.
	 * We don't want to delete "Site Template" when a non-blueprint site is deleted.
	 */
	if ( $template_id ) {
		update_site_meta( $id, '_template_id', $template_id );
	}

	// translators: 1. login of user who created new site, 2. URL of the new site, 3. title of the new site
	$content_mail = sprintf( __( "New site created by %1$1s\n\nAddress: http://%2$2s\nName: %3$3s", 'commons-in-a-box' ), $current_user->user_login, $validate['domain'] . $validate['path'], stripslashes( $validate['blog_title'] ) );

	wp_mail(
		get_site_option( 'admin_email' ),
		// translators: site name
		sprintf( __( '[%s] New Blog Created', 'commons-in-a-box' ), $current_site->site_name ),
		$content_mail,
		'From: "Site Admin" <' . get_site_option( 'admin_email' ) . '>'
	);

	$msg = __( 'Site Created', 'commons-in-a-box' );
	// now copy
	$blogtables = $wpdb->base_prefix . $src_id . '_';
	$newtables  = $wpdb->base_prefix . $new_id . '_';
	$query      = "SHOW TABLES LIKE '{$blogtables}%'";

	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$tables = $wpdb->get_results( $query, ARRAY_A );

	if ( $tables ) {
		reset( $tables );
		$create     = array();
		$data       = array();
		$len        = strlen( $blogtables );
		$create_col = 'Create Table';

		// add std wp tables to this array
		$wptables = array(
			$blogtables . 'links',
			$blogtables . 'postmeta',
			$blogtables . 'posts',
			$blogtables . 'terms',
			$blogtables . 'term_taxonomy',
			$blogtables . 'term_relationships',
			$blogtables . 'termmeta',
		);

		$table_count = count( $tables );
		for ( $i = 0; $i < $table_count; $i++ ) {
			$table = current( $tables[ $i ] );
			if ( substr( $table, 0, $len ) === $blogtables ) {
				if ( ! ( $table === $blogtables . 'options' || $table === $blogtables . 'comments' || $table === $blogtables . 'commentmeta' ) ) {
					// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$create[ $table ] = $wpdb->get_row( "SHOW CREATE TABLE {$table}" );
					$data[ $table ]   = $wpdb->get_results( "SELECT * FROM {$table}", ARRAY_A );
					// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				}
			}
		}

		if ( $data ) {
			switch_to_blog( $src_id );
			$src_url      = get_option( 'siteurl' );
			$option_query = "SELECT option_name, option_value FROM {$wpdb->options}";
			$upload_dir   = wp_upload_dir();
			restore_current_blog();

			$new_url = get_blog_option( $new_id, 'siteurl' );
			foreach ( $data as $k => $v ) {
				$table = str_replace( $blogtables, $newtables, $k );
				if ( in_array( $k, $wptables, true ) ) { // drop new blog table
					$query = "DROP TABLE IF EXISTS {$table}";
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->query( $query );
				}
				$key   = (array) $create[ $k ];
				$query = str_replace( $blogtables, $newtables, $key[ $create_col ] );

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( $query );
				$is_post = ( $k === $blogtables . 'posts' );
				if ( $v ) {
					foreach ( $v as $row ) {
						if ( $is_post ) {
							$row['guid']         = str_replace( $src_url, $new_url, $row['guid'] );
							$row['post_content'] = str_replace( $src_url, $new_url, $row['post_content'] );
							$row['post_author']  = $current_user->ID;
						}
						$wpdb->insert( $table, $row );
					}
				}
			}

			$source_site_upload_dir = $upload_dir['basedir'];
			$dest_site_upload_dir   = str_replace( $src_id, $new_id, $source_site_upload_dir );

			// Copy uploaded files.
			cboxol_copyr( $source_site_upload_dir, $dest_site_upload_dir );

			// update options
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$options = $wpdb->get_results( $option_query );

			$skip_options = cboxol_clone_options_to_skip( $src_id );

			/**
			 * Filters the options that should be skipped when a new site is generated from a template.
			 *
			 * @since 1.4.1
			 *
			 * @param array Option names.
			 */
			$skip_options = apply_filters( 'cboxol_template_skip_options', $skip_options );

			// new blog stuff
			if ( $options ) {
				switch_to_blog( $new_id );

				$source_site_url = get_blog_option( $src_id, 'home' );
				$dest_site_url   = get_blog_option( $new_id, 'home' );

				foreach ( $options as $o ) {
					if ( ! in_array( $o->option_name, $skip_options, true ) && substr( $o->option_name, 0, 6 ) !== '_trans' ) {
						$value = maybe_unserialize( $o->option_value );
						$value = map_deep(
							maybe_unserialize( $o->option_value ),
							function( $v ) use ( $source_site_url, $source_site_upload_dir, $dest_site_url, $dest_site_upload_dir ) {
								return str_replace(
									array( $source_site_url, $source_site_upload_dir ),
									array( $dest_site_url, $dest_site_upload_dir ),
									$v
								);
							}
						);
						update_option( $o->option_name, $value );
					}
				}

				// Updated custom nav menu items
				$primary_nav_key = cboxol_get_theme_primary_nav_menu_location();
				$locations       = get_theme_mod( 'nav_menu_locations' );
				$menu_id         = isset( $locations[ $primary_nav_key ] ) ? (int) $locations[ $primary_nav_key ] : 0;
				$nav_items       = get_term_meta( $menu_id, 'cboxol_custom_menus', true );

				if ( $menu_id && ! empty( $nav_items ) ) {
					$group_type = cboxol_get_group_group_type( $group_id );

					// Update Group Profile URL.
					wp_update_nav_menu_item(
						$menu_id,
						$nav_items['group'],
						array(
							'menu-item-title'    => '[ ' . $group_type->get_label( 'group_home' ) . ' ]',
							'menu-item-url'      => bp_get_group_permalink( $group ),
							'menu-item-status'   => 'publish',
							'menu-item-position' => -2,
							'menu-item-classes'  => 'group-profile-link',
						)
					);

					// Update home URL.
					wp_update_nav_menu_item(
						$menu_id,
						$nav_items['home'],
						array(
							'menu-item-title'    => __( 'Home', 'cbox-openlab-core' ),
							'menu-item-url'      => home_url( '/' ),
							'menu-item-status'   => 'publish',
							'menu-item-position' => -1,
							'menu-item-classes'  => 'home',
						)
					);
				}

				restore_current_blog();
				$msg = __( 'Blog Copied', 'commons-in-a-box' );
			}
		}
	}

	return $msg;
}

/**
 * Returns a list of option names to be skipped when cloning a site.
 *
 * @since 1.5.0
 *
 * @param int $source_site_id If present, we will concatenate the 'user_roles' option key,
 *                            to ensure that it also is omitted.
 * @return string[]
 */
function cboxol_clone_options_to_skip( $source_site_id = null ) {
	global $wpdb;

	$options = [
		'admin_email',
		'astra_partials_config_cache', // #433
		'blc_activation_enabled', // #443
		'blc_installation_log', // #443
		'blc_options', // #443
		'blogname',
		'cboxol_initial_rewrite_flush',
		'cron',
		'db_version',
		'doing_cron',
		'fileupload_url',
		'home',
		'new_admin_email',
		'nonce_salt',
		'random_seed',
		'rewrite_rules',
		'secret',
		'siteurl',
		'upload_path',
		'upload_url_path',
		'wsblc_options', // #443
	];

	if ( $source_site_id ) {
		$options[] = $wpdb->get_blog_prefix( $source_site_id ) . 'user_roles';
	}

	/**
	 * Filters the list of options to skip during clone.
	 *
	 * Third parties may add to this list to avoid issues with cloned options.
	 *
	 * @since 1.5.0
	 *
	 * @param string[] $options Option names.
	 */
	return apply_filters( 'cboxol_clone_options_to_skip', $options );
}

/**
 * Is this group hidden?
 */
function cboxol_group_is_hidden( $group_id = 0 ) {
	$is_hidden = false;

	if ( ! $group_id ) {
		if ( bp_is_group() ) {
			$group = groups_get_current_group();
		} else {
			$group_id = openlab_fallback_group();
		}
	}

	if ( empty( $group ) ) {
		$group = groups_get_group( array( 'group_id' => $group_id ) );
	}

	if ( empty( $group ) ) {
		return $is_hidden;
	} else {
		return isset( $group->status ) && 'hidden' === $group->status;
	}
}

function cboxol_blogname_contains_illegal_characters( $blogname ) {
	return (bool) preg_match( '/[^a-z0-9\-_]+/', $blogname );
}

function cboxol_allow_extended_blogname_charset( $retval ) {
	if ( empty( $retval['errors'] ) ) {
		return $retval;
	}

	$blogname_messages = $retval['errors']->get_error_messages( 'blogname' );
	if ( empty( $blogname_messages ) ) {
		return $retval;
	}

	$chars_message = __( 'Site names can only contain lowercase letters (a-z) and numbers.', 'commons-in-a-box' );
	if ( ! in_array( $chars_message, $blogname_messages, true ) ) {
		return $retval;
	}

	// Allow hyphens and underscores.
	if ( cboxol_blogname_contains_illegal_characters( $retval['blogname'] ) ) {
		return $retval;
	}

	$new_blogname_messages = array_diff( $blogname_messages, array( $chars_message ) );
	$retval['errors']->remove( 'blogname' );
	if ( $new_blogname_messages ) {
		foreach ( $new_blogname_messages as $new_blogname_message ) {
			$retval['errors']->add( 'blogname', $new_blogname_message );
		}
	}

	return $retval;
}
add_filter( 'wpmu_validate_blog_signup', 'cboxol_allow_extended_blogname_charset' );

/**
 * Validate a blogname.
 *
 * Checks for the following:
 * - Existing sites with the same name.
 * - That the blogname doesn't have illegal characters.
 * - That the blogname is long enough to pass WP's built-in validation.
 * - That the blogname is not illegal
 *
 * @param string $blogname Subdomain or path, depending on installation type.
 * @return array
 */
function cboxol_validate_blogname( $blogname ) {
	$error = null;

	/**
	 * Filters the minimum site name length required when validating a site signup.
	 *
	 * @since 4.8.0
	 *
	 * @param int $length The minimum site name length. Default 4.
	 */
	$minimum_site_name_length = apply_filters( 'minimum_site_name_length', 4 );

	if ( cboxol_blogname_contains_illegal_characters( $blogname ) ) {
		$error = __( 'URLs can contain only alphanumeric characters, hyphens, and underscores.', 'commons-in-a-box' );
	} elseif ( get_id_from_blogname( $blogname ) ) {
		$error = __( 'That site URL is already taken. Please try another.', 'commons-in-a-box' );
	} elseif ( cboxol_blogname_is_illegal( $blogname ) ) {
		$error = __( 'That URL is not allowed', 'commons-in-a-box' );
	} elseif ( strlen( $blogname ) < $minimum_site_name_length ) {
		/* translators: %s: minimum site name length */
		$error = sprintf( _n( 'Site name must be at least %s character.', 'Site name must be at least %s characters.', $minimum_site_name_length, 'commons-in-a-box' ), number_format_i18n( $minimum_site_name_length ) );
	}

	$retval = array(
		'validated' => true,
	);

	if ( $error ) {
		$retval['validated'] = false;
		$retval['error']     = $error;
	}

	return $retval;
}

/**
 * Check whether a blogname is "illegal".
 *
 * This is a function that WordPress ought to provide but does not.
 *
 * @param string $blogname
 * @return bool
 */
function cboxol_blogname_is_illegal( $blogname ) {
	$illegal_names = get_site_option( 'illegal_names' );
	if ( false === $illegal_names ) {
		$illegal_names = array( 'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator' );
		add_site_option( 'illegal_names', $illegal_names );
	}

	/*
	 * On sub dir installs, some names are so illegal, only a filter can
	 * spring them from jail.
	 */
	if ( ! is_subdomain_install() ) {
		$illegal_names = array_merge( $illegal_names, get_subdirectory_reserved_names() );
	}

	return in_array( $blogname, $illegal_names, true );
}

/**
 * Load theme-specific fixes.
 */
function cboxol_load_theme_specific_fixes() {
	$template = get_template();

	/**
	 * Allow plugins to disable theme-specific fixes.
	 *
	 * @param string $template Theme template.
	 */
	if ( ! apply_filters( 'cboxol_allow_theme_specific_fixes', true, $template ) ) {
		return;
	}

	$css = null;
	$js  = null;

	switch ( $template ) {
		case 'hamilton':
			$css = CBOXOL_PLUGIN_URL . 'assets/css/themes/hamilton.css';
			break;

		case 'twentyfifteen':
			$js  = CBOXOL_PLUGIN_URL . "assets/js/themes/{$template}.js";
			$css = CBOXOL_PLUGIN_URL . "assets/css/themes/{$template}.css";
			break;
	}

	$ver = cboxol_get_asset_version();

	if ( $css ) {
		wp_enqueue_style( "cboxol-{$template}-fixes", $css, array(), $ver );
	}

	if ( $js ) {
		wp_enqueue_script( "cboxol-{$template}-fixes", $js, array(), $ver, true );
	}
}
add_action( 'wp_enqueue_scripts', 'cboxol_load_theme_specific_fixes' );

/**
 * Generate array of nav menu items.
 *
 * @return array $items
 */
function cboxol_get_nav_menu_items() {
	$items = [];

	$group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );
	if ( ! $group_id ) {
		return $items;
	}

	$group = groups_get_group( $group_id );
	if ( ! $group->is_visible ) {
		return $items;
	}

	$group_type = cboxol_get_group_group_type( $group_id );
	if ( ! is_wp_error( $group_type ) ) {
		$items[] = (object) [
			'ID'               => 'group-profile-link',
			'db_id'            => 0,
			'object_id'        => $group_id,
			'object'           => 'custom',
			'title'            => '[ ' . $group_type->get_label( 'group_home' ) . ' ]',
			'url'              => bp_get_group_permalink( $group ),
			'slug'             => 'group-profile-link',
			'type'             => 'custom',
			'classes'          => [ 'group-profile-link' ],
			'menu_item_parent' => 0,
			'attr_title'       => '',
			'target'           => '',
			'xfn'              => '',
		];
	}

	return $items;
}

/**
 * Determine a theme's primary nav menu location.
 *
 * Uses a heuristic based on naming conventions, and falls back on the first available.
 *
 * @since 1.6.0
 *
 * @return string|null
 */
function cboxol_get_theme_primary_nav_menu_location() {
	$keys_to_check = [ 'primary', 'main', 'header', 'top' ];

	$locations = get_nav_menu_locations();
	if ( ! $locations ) {
		return null;
	}

	foreach ( $keys_to_check as $key ) {
		if ( isset( $locations[ $key ] ) ) {
			return $key;
		}
	}

	return key( $locations );
}

/**
 * Register meta box for CBOX OpenLab nav menu.
 *
 * @return void
 */
function cboxol_wp_nav_menu_meta_box() {
	$is_group_site = (bool) openlab_get_group_id_by_blog_id( get_current_blog_id() );

	// Only add meta box panel to group sites.
	if ( ! $is_group_site ) {
		return;
	}

	add_meta_box(
		'cboxol-nav-menu-box',
		__( 'CBOX OpenLab', 'cbox-openlab-core' ),
		'cboxol_render_nav_menu_meta_box',
		'nav-menus',
		'side',
		'default'
	);
}
add_action( 'load-nav-menus.php', 'cboxol_wp_nav_menu_meta_box' );

/**
 * Render CBOX OpenLab meta box panel on Appearance > Menus.
 *
 * @return void
 */
function cboxol_render_nav_menu_meta_box() {
	global $nav_menu_selected_id;

	$walker = new Walker_Nav_Menu_Checklist();
	$args   = [ 'walker' => $walker ];
	$items  = cboxol_get_nav_menu_items();

	?>
	<div id="cboxol-menu" class="posttypediv">
		<div id="tabs-panel-cboxol-all" class="tabs-panel tabs-panel-active">
			<ul id="cboxol-menu-checklist" class="categorychecklist form-no-clear">
				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $items ), 0, (object) $args ); ?>
			</ul>
		</div>

		<p class="button-controls wp-clearfix">
			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'cbox-openlab-core' ); ?>" name="add-custom-menu-item" id="submit-cboxol-menu" />
				<span class="spinner"></span>
			</span>
		</p>
	</div>
	<?php
}

/**
 * Set CBOX OpenLab item nav for the customizer.
 *
 * @param array $item_types Navigation menu item types.
 * @return array $item_types Updated menu item types.
 */
function cboxol_add_customizer_nav_menu_item_types( $item_types = [] ) {
	$item_types[] = [
		'title'      => __( 'CBOX OpenLab', 'cbox-openlab-core' ),
		'type_label' => __( 'CBOX OpenLab', 'cbox-openlab-core' ),
		'type'       => 'cboxol_nav',
		'object'     => 'cboxol_box',
	];

	return $item_types;
}
add_filter( 'customize_nav_menu_available_item_types', 'cboxol_add_customizer_nav_menu_item_types' );

/**
 * Populate CBOX OpenLab nav menu items for the customizer.
 *
 * @param  array   $items  List of nav menu items.
 * @param  string  $type   Nav menu type.
 * @param  string  $object Nav menu object.
 * @param  int     $page   Page number.
 * @return array   $items
 */
function cboxol_customizer_nav_menu_items( $items = [], $type = '', $object = '', $page = 0 ) {
	if ( 'cboxol_box' !== $object ) {
		return $items;
	}

	// Don't allow pagination since all items are loaded at once.
	if ( 0 < $page ) {
		return $items;
	}

	$cboxol_items = cboxol_get_nav_menu_items();

	foreach ( $cboxol_items as $cboxol_item ) {
		$items[] = [
			'id'         => 'group-profile-link',
			'title'      => html_entity_decode( $cboxol_item->title, ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'type'       => $cboxol_item->type,
			'url'        => esc_url_raw( $cboxol_item->url ),
			'classes'    => $cboxol_item->classes,
			'type_label' => _x( 'Custom Link', 'customizer menu type label', 'cbox-openlab-core' ),
		];
	}

	return $items;
}
add_filter( 'customize_nav_menu_available_items', 'cboxol_customizer_nav_menu_items', 10, 4 );

/**
 * Indicates whether the specified site should display the WP toolbar to logged-out users.
 *
 * @since 1.3.0
 *
 * @param int $site_id ID of the site.
 * @return bool
 */
function cboxol_show_admin_bar_for_anonymous_users( $site_id ) {
	// Flip the logic for better defaults.
	return ! (bool) get_blog_option( $site_id, 'cboxol_hide_admin_bar_for_anonymous_users' );
}

/**
 * Hides the admin bar for anonymous users, based on admin-configured setting.
 *
 * @since 1.3.0
 */
function cboxol_maybe_hide_admin_bar_for_anonymous_users() {
	if ( cbox_is_main_site() ) {
		show_admin_bar( true );
		return;
	}

	if ( is_user_logged_in() ) {
		show_admin_bar( true );
		return;
	}

	if ( cboxol_show_admin_bar_for_anonymous_users( get_current_blog_id() ) ) {
		show_admin_bar( true );
	} else {
		show_admin_bar( false );
	}
}
add_action( 'init', 'cboxol_maybe_hide_admin_bar_for_anonymous_users' );

/**
 * Loads License widget.
 *
 * @since 1.3.0
 */
function cboxol_register_license_widget() {
	$widgets = [
		'\CBOX\OL\Widget\License',
	];

	foreach ( $widgets as $widget ) {
		register_widget( $widget );
	}
}
add_action( 'widgets_init', 'cboxol_register_license_widget' );

/**
 * Use entire text of comment or blog post when sending BPGES notifications.
 *
 * @param string $content Activity content.
 * @param object $activity Activity object.
 */
function openlab_use_full_text_for_blog_related_bpges_notifications( $content, $activity ) {
	if ( 'groups' !== $activity->component ) {
		return $content;
	}

	// @todo new-style blog comments?
	if ( ! in_array( $activity->type, array( 'new_blog_post', 'new_blog_comment' ), true ) ) {
		return $content;
	}

	$group_id = $activity->item_id;
	$blog_id  = openlab_get_site_id_by_group_id( $group_id );

	if ( ! $blog_id ) {
		return $content;
	}

	switch_to_blog( $blog_id );

	if ( 'new_blog_post' === $activity->type ) {
		$post    = get_post( $activity->secondary_item_id );
		$content = empty( $post->post_password ) ? $post->post_content : 'This post is password protected.';
	} elseif ( 'new_blog_comment' === $activity->type ) {
		$comment = get_comment( $activity->secondary_item_id );
		$content = $comment->comment_content;
	}

	restore_current_blog();

	return openlab_convert_chars_for_email( $content );
}
add_action( 'bp_ass_activity_notification_content', 'openlab_use_full_text_for_blog_related_bpges_notifications', 10, 2 );

/**
 * Sanitize characters used for blog post notifications.
 *
 * Sometimes things can be mangled in copy-paste from Word, etc.
 */
function openlab_convert_chars_for_email( $text ) {
	// UTF-8
	$conv = array(
		"\xC2\xA0"     => '&nbsp;',
		"\xC2\xA1"     => '&iexcl;',
		"\xC2\xA2"     => '&cent;',
		"\xC2\xA3"     => '&pound;',
		"\xC2\xA4"     => '&curren;',
		"\xC2\xA5"     => '&yen;',
		"\xC2\xA6"     => '&brvbar;',
		"\xC2\xA7"     => '&sect;',
		"\xC2\xA8"     => '&uml;',
		"\xC2\xA9"     => '&copy;',
		"\xC2\xAA"     => '&ordf;',
		"\xC2\xAB"     => '&laquo;',
		"\xC2\xAC"     => '&not;',
		"\xC2\xAD"     => '&shy;',
		"\xC2\xAE"     => '&reg;',
		"\xC2\xAF"     => '&macr;',
		"\xC2\xB0"     => '&deg;',
		"\xC2\xB1"     => '&plusmn;',
		"\xC2\xB2"     => '&sup2;',
		"\xC2\xB3"     => '&sup3;',
		"\xC2\xB4"     => '&acute;',
		"\xC2\xB5"     => '&micro;',
		"\xC2\xB6"     => '&para;',
		"\xC2\xB7"     => '&middot;',
		"\xC2\xB8"     => '&cedil;',
		"\xC2\xB9"     => '&sup1;',
		"\xC2\xBA"     => '&ordm;',
		"\xC2\xBB"     => '&raquo;',
		"\xC2\xBC"     => '&frac14;',
		"\xC2\xBD"     => '&frac12;',
		"\xC2\xBE"     => '&frac34;',
		"\xC2\xBF"     => '&iquest;',
		"\xC3\x80"     => '&Agrave;',
		"\xC3\x81"     => '&Aacute;',
		"\xC3\x82"     => '&Acirc;',
		"\xC3\x83"     => '&Atilde;',
		"\xC3\x84"     => '&Auml;',
		"\xC3\x85"     => '&Aring;',
		"\xC3\x86"     => '&AElig;',
		"\xC3\x87"     => '&Ccedil;',
		"\xC3\x88"     => '&Egrave;',
		"\xC3\x89"     => '&Eacute;',
		"\xC3\x8A"     => '&Ecirc;',
		"\xC3\x8B"     => '&Euml;',
		"\xC3\x8C"     => '&Igrave;',
		"\xC3\x8D"     => '&Iacute;',
		"\xC3\x8E"     => '&Icirc;',
		"\xC3\x8F"     => '&Iuml;',
		"\xC3\x90"     => '&ETH;',
		"\xC3\x91"     => '&Ntilde;',
		"\xC3\x92"     => '&Ograve;',
		"\xC3\x93"     => '&Oacute;',
		"\xC3\x94"     => '&Ocirc;',
		"\xC3\x95"     => '&Otilde;',
		"\xC3\x96"     => '&Ouml;',
		"\xC3\x97"     => '&times;',
		"\xC3\x98"     => '&Oslash;',
		"\xC3\x99"     => '&Ugrave;',
		"\xC3\x9A"     => '&Uacute;',
		"\xC3\x9B"     => '&Ucirc;',
		"\xC3\x9C"     => '&Uuml;',
		"\xC3\x9D"     => '&Yacute;',
		"\xC3\x9E"     => '&THORN;',
		"\xC3\x9F"     => '&szlig;',
		"\xC3\xA0"     => '&agrave;',
		"\xC3\xA1"     => '&aacute;',
		"\xC3\xA2"     => '&acirc;',
		"\xC3\xA3"     => '&atilde;',
		"\xC3\xA4"     => '&auml;',
		"\xC3\xA5"     => '&aring;',
		"\xC3\xA6"     => '&aelig;',
		"\xC3\xA7"     => '&ccedil;',
		"\xC3\xA8"     => '&egrave;',
		"\xC3\xA9"     => '&eacute;',
		"\xC3\xAA"     => '&ecirc;',
		"\xC3\xAB"     => '&euml;',
		"\xC3\xAC"     => '&igrave;',
		"\xC3\xAD"     => '&iacute;',
		"\xC3\xAE"     => '&icirc;',
		"\xC3\xAF"     => '&iuml;',
		"\xC3\xB0"     => '&eth;',
		"\xC3\xB1"     => '&ntilde;',
		"\xC3\xB2"     => '&ograve;',
		"\xC3\xB3"     => '&oacute;',
		"\xC3\xB4"     => '&ocirc;',
		"\xC3\xB5"     => '&otilde;',
		"\xC3\xB6"     => '&ouml;',
		"\xC3\xB7"     => '&divide;',
		"\xC3\xB8"     => '&oslash;',
		"\xC3\xB9"     => '&ugrave;',
		"\xC3\xBA"     => '&uacute;',
		"\xC3\xBB"     => '&ucirc;',
		"\xC3\xBC"     => '&uuml;',
		"\xC3\xBD"     => '&yacute;',
		"\xC3\xBE"     => '&thorn;',
		"\xC3\xBF"     => '&yuml;',
		// Latin Extended-A
		"\xC5\x92"     => '&OElig;',
		"\xC5\x93"     => '&oelig;',
		"\xC5\xA0"     => '&Scaron;',
		"\xC5\xA1"     => '&scaron;',
		"\xC5\xB8"     => '&Yuml;',
		// Spacing Modifier Letters
		"\xCB\x86"     => '&circ;',
		"\xCB\x9C"     => '&tilde;',
		// General Punctuation
		"\xE2\x80\x82" => '&ensp;',
		"\xE2\x80\x83" => '&emsp;',
		"\xE2\x80\x89" => '&thinsp;',
		"\xE2\x80\x8C" => '&zwnj;',
		"\xE2\x80\x8D" => '&zwj;',
		"\xE2\x80\x8E" => '&lrm;',
		"\xE2\x80\x8F" => '&rlm;',
		"\xE2\x80\x93" => '&ndash;',
		"\xE2\x80\x94" => '&mdash;',
		"\xE2\x80\x98" => '&lsquo;',
		"\xE2\x80\x99" => '&rsquo;',
		"\xE2\x80\x9A" => '&sbquo;',
		"\xE2\x80\x9C" => '&ldquo;',
		"\xE2\x80\x9D" => '&rdquo;',
		"\xE2\x80\x9E" => '&bdquo;',
		"\xE2\x80\xA0" => '&dagger;',
		"\xE2\x80\xA1" => '&Dagger;',
		"\xE2\x80\xB0" => '&permil;',
		"\xE2\x80\xB9" => '&lsaquo;',
		"\xE2\x80\xBA" => '&rsaquo;',
		"\xE2\x82\xAC" => '&euro;',
		// Latin Extended-B
		"\xC6\x92"     => '&fnof;',
		// Greek
		"\xCE\x91"     => '&Alpha;',
		"\xCE\x92"     => '&Beta;',
		"\xCE\x93"     => '&Gamma;',
		"\xCE\x94"     => '&Delta;',
		"\xCE\x95"     => '&Epsilon;',
		"\xCE\x96"     => '&Zeta;',
		"\xCE\x97"     => '&Eta;',
		"\xCE\x98"     => '&Theta;',
		"\xCE\x99"     => '&Iota;',
		"\xCE\x9A"     => '&Kappa;',
		"\xCE\x9B"     => '&Lambda;',
		"\xCE\x9C"     => '&Mu;',
		"\xCE\x9D"     => '&Nu;',
		"\xCE\x9E"     => '&Xi;',
		"\xCE\x9F"     => '&Omicron;',
		"\xCE\xA0"     => '&Pi;',
		"\xCE\xA1"     => '&Rho;',
		"\xCE\xA3"     => '&Sigma;',
		"\xCE\xA4"     => '&Tau;',
		"\xCE\xA5"     => '&Upsilon;',
		"\xCE\xA6"     => '&Phi;',
		"\xCE\xA7"     => '&Chi;',
		"\xCE\xA8"     => '&Psi;',
		"\xCE\xA9"     => '&Omega;',
		"\xCE\xB1"     => '&alpha;',
		"\xCE\xB2"     => '&beta;',
		"\xCE\xB3"     => '&gamma;',
		"\xCE\xB4"     => '&delta;',
		"\xCE\xB5"     => '&epsilon;',
		"\xCE\xB6"     => '&zeta;',
		"\xCE\xB7"     => '&eta;',
		"\xCE\xB8"     => '&theta;',
		"\xCE\xB9"     => '&iota;',
		"\xCE\xBA"     => '&kappa;',
		"\xCE\xBB"     => '&lambda;',
		"\xCE\xBC"     => '&mu;',
		"\xCE\xBD"     => '&nu;',
		"\xCE\xBE"     => '&xi;',
		"\xCE\xBF"     => '&omicron;',
		"\xCF\x80"     => '&pi;',
		"\xCF\x81"     => '&rho;',
		"\xCF\x82"     => '&sigmaf;',
		"\xCF\x83"     => '&sigma;',
		"\xCF\x84"     => '&tau;',
		"\xCF\x85"     => '&upsilon;',
		"\xCF\x86"     => '&phi;',
		"\xCF\x87"     => '&chi;',
		"\xCF\x88"     => '&psi;',
		"\xCF\x89"     => '&omega;',
		"\xCF\x91"     => '&thetasym;',
		"\xCF\x92"     => '&upsih;',
		"\xCF\x96"     => '&piv;',
		// General Punctuation
		"\xE2\x80\xA2" => '&bull;',
		"\xE2\x80\xA6" => '&hellip;',
		"\xE2\x80\xB2" => '&prime;',
		"\xE2\x80\xB3" => '&Prime;',
		"\xE2\x80\xBE" => '&oline;',
		"\xE2\x81\x84" => '&frasl;',
		// Letterlike Symbols
		"\xE2\x84\x98" => '&weierp;',
		"\xE2\x84\x91" => '&image;',
		"\xE2\x84\x9C" => '&real;',
		"\xE2\x84\xA2" => '&trade;',
		"\xE2\x84\xB5" => '&alefsym;',
		// Arrows
		"\xE2\x86\x90" => '&larr;',
		"\xE2\x86\x91" => '&uarr;',
		"\xE2\x86\x92" => '&rarr;',
		"\xE2\x86\x93" => '&darr;',
		"\xE2\x86\x94" => '&harr;',
		"\xE2\x86\xB5" => '&crarr;',
		"\xE2\x87\x90" => '&lArr;',
		"\xE2\x87\x91" => '&uArr;',
		"\xE2\x87\x92" => '&rArr;',
		"\xE2\x87\x93" => '&dArr;',
		"\xE2\x87\x94" => '&hArr;',
		// Mathematical Operators
		"\xE2\x88\x80" => '&forall;',
		"\xE2\x88\x82" => '&part;',
		"\xE2\x88\x83" => '&exist;',
		"\xE2\x88\x85" => '&empty;',
		"\xE2\x88\x87" => '&nabla;',
		"\xE2\x88\x88" => '&isin;',
		"\xE2\x88\x89" => '&notin;',
		"\xE2\x88\x8B" => '&ni;',
		"\xE2\x88\x8F" => '&prod;',
		"\xE2\x88\x91" => '&sum;',
		"\xE2\x88\x92" => '&minus;',
		"\xE2\x88\x97" => '&lowast;',
		"\xE2\x88\x9A" => '&radic;',
		"\xE2\x88\x9D" => '&prop;',
		"\xE2\x88\x9E" => '&infin;',
		"\xE2\x88\xA0" => '&ang;',
		"\xE2\x88\xA7" => '&and;',
		"\xE2\x88\xA8" => '&or;',
		"\xE2\x88\xA9" => '&cap;',
		"\xE2\x88\xAA" => '&cup;',
		"\xE2\x88\xAB" => '&int;',
		"\xE2\x88\xB4" => '&there4;',
		"\xE2\x88\xBC" => '&sim;',
		"\xE2\x89\x85" => '&cong;',
		"\xE2\x89\x88" => '&asymp;',
		"\xE2\x89\xA0" => '&ne;',
		"\xE2\x89\xA1" => '&equiv;',
		"\xE2\x89\xA4" => '&le;',
		"\xE2\x89\xA5" => '&ge;',
		"\xE2\x8A\x82" => '&sub;',
		"\xE2\x8A\x83" => '&sup;',
		"\xE2\x8A\x84" => '&nsub;',
		"\xE2\x8A\x86" => '&sube;',
		"\xE2\x8A\x87" => '&supe;',
		"\xE2\x8A\x95" => '&oplus;',
		"\xE2\x8A\x97" => '&otimes;',
		"\xE2\x8A\xA5" => '&perp;',
		"\xE2\x8B\x85" => '&sdot;',
		// Miscellaneous Technical
		"\xE2\x8C\x88" => '&lceil;',
		"\xE2\x8C\x89" => '&rceil;',
		"\xE2\x8C\x8A" => '&lfloor;',
		"\xE2\x8C\x8B" => '&rfloor;',
		"\xE2\x8C\xA9" => '&lang;',
		"\xE2\x8C\xAA" => '&rang;',
		// Geometric Shapes
		"\xE2\x97\x8A" => '&loz;',
		// Miscellaneous Symbols
		"\xE2\x99\xA0" => '&spades;',
		"\xE2\x99\xA3" => '&clubs;',
		"\xE2\x99\xA5" => '&hearts;',
		"\xE2\x99\xA6" => '&diams;',
	);

	$string = strtr( $text, $conv );

	// Unicode
	$conv = array(
		chr( 128 ) => '&euro;',
		chr( 130 ) => '&sbquo;',
		chr( 131 ) => '&fnof;',
		chr( 132 ) => '&bdquo;',
		chr( 133 ) => '&hellip;',
		chr( 134 ) => '&dagger;',
		chr( 135 ) => '&Dagger;',
		chr( 136 ) => '&circ;',
		chr( 137 ) => '&permil;',
		chr( 138 ) => '&Scaron;',
		chr( 139 ) => '&lsaquo;',
		chr( 140 ) => '&OElig;',
		chr( 145 ) => '&lsquo;',
		chr( 146 ) => '&rsquo;',
		chr( 147 ) => '&ldquo;',
		chr( 148 ) => '&rdquo;',
		chr( 149 ) => '&bull;',
		chr( 150 ) => '&ndash;',
		chr( 151 ) => '&mdash;',
		chr( 152 ) => '&tilde;',
		chr( 153 ) => '&trade;',
		chr( 154 ) => '&scaron;',
		chr( 155 ) => '&rsaquo;',
		chr( 156 ) => '&oelig;',
		chr( 159 ) => '&yuml;',
		chr( 160 ) => '&nbsp;',
		chr( 161 ) => '&iexcl;',
		chr( 162 ) => '&cent;',
		chr( 163 ) => '&pound;',
		chr( 164 ) => '&curren;',
		chr( 165 ) => '&yen;',
		chr( 166 ) => '&brvbar;',
		chr( 167 ) => '&sect;',
		chr( 168 ) => '&uml;',
		chr( 169 ) => '&copy;',
		chr( 170 ) => '&ordf;',
		chr( 171 ) => '&laquo;',
		chr( 172 ) => '&not;',
		chr( 173 ) => '&shy;',
		chr( 174 ) => '&reg;',
		chr( 175 ) => '&macr;',
		chr( 176 ) => '&deg;',
		chr( 177 ) => '&plusmn;',
		chr( 178 ) => '&sup2;',
		chr( 179 ) => '&sup3;',
		chr( 180 ) => '&acute;',
		chr( 181 ) => '&micro;',
		chr( 182 ) => '&para;',
		chr( 183 ) => '&middot;',
		chr( 184 ) => '&cedil;',
		chr( 185 ) => '&sup1;',
		chr( 186 ) => '&ordm;',
		chr( 187 ) => '&raquo;',
		chr( 188 ) => '&frac14;',
		chr( 189 ) => '&frac12;',
		chr( 190 ) => '&frac34;',
		chr( 191 ) => '&iquest;',
		chr( 192 ) => '&Agrave;',
		chr( 193 ) => '&Aacute;',
		chr( 194 ) => '&Acirc;',
		chr( 195 ) => '&Atilde;',
		chr( 196 ) => '&Auml;',
		chr( 197 ) => '&Aring;',
		chr( 198 ) => '&AElig;',
		chr( 199 ) => '&Ccedil;',
		chr( 200 ) => '&Egrave;',
		chr( 201 ) => '&Eacute;',
		chr( 202 ) => '&Ecirc;',
		chr( 203 ) => '&Euml;',
		chr( 204 ) => '&Igrave;',
		chr( 205 ) => '&Iacute;',
		chr( 206 ) => '&Icirc;',
		chr( 207 ) => '&Iuml;',
		chr( 208 ) => '&ETH;',
		chr( 209 ) => '&Ntilde;',
		chr( 210 ) => '&Ograve;',
		chr( 211 ) => '&Oacute;',
		chr( 212 ) => '&Ocirc;',
		chr( 213 ) => '&Otilde;',
		chr( 214 ) => '&Ouml;',
		chr( 215 ) => '&times;',
		chr( 216 ) => '&Oslash;',
		chr( 217 ) => '&Ugrave;',
		chr( 218 ) => '&Uacute;',
		chr( 219 ) => '&Ucirc;',
		chr( 220 ) => '&Uuml;',
		chr( 221 ) => '&Yacute;',
		chr( 222 ) => '&THORN;',
		chr( 223 ) => '&szlig;',
		chr( 224 ) => '&agrave;',
		chr( 225 ) => '&aacute;',
		chr( 226 ) => '&acirc;',
		chr( 227 ) => '&atilde;',
		chr( 228 ) => '&auml;',
		chr( 229 ) => '&aring;',
		chr( 230 ) => '&aelig;',
		chr( 231 ) => '&ccedil;',
		chr( 232 ) => '&egrave;',
		chr( 233 ) => '&eacute;',
		chr( 234 ) => '&ecirc;',
		chr( 235 ) => '&euml;',
		chr( 236 ) => '&igrave;',
		chr( 237 ) => '&iacute;',
		chr( 238 ) => '&icirc;',
		chr( 239 ) => '&iuml;',
		chr( 240 ) => '&eth;',
		chr( 241 ) => '&ntilde;',
		chr( 242 ) => '&ograve;',
		chr( 243 ) => '&oacute;',
		chr( 244 ) => '&ocirc;',
		chr( 245 ) => '&otilde;',
		chr( 246 ) => '&ouml;',
		chr( 247 ) => '&divide;',
		chr( 248 ) => '&oslash;',
		chr( 249 ) => '&ugrave;',
		chr( 250 ) => '&uacute;',
		chr( 251 ) => '&ucirc;',
		chr( 252 ) => '&uuml;',
		chr( 253 ) => '&yacute;',
		chr( 254 ) => '&thorn;',
		chr( 255 ) => '&yuml;',
	);

	return strtr( $string, $conv );
}

/** Post Visibility **************************************************/

/**
 * Registers openlab_post_visibility meta field.
 *
 * @return void
 */
function openlab_register_post_visibility_meta() {
	register_meta(
		'post',
		'openlab_post_visibility',
		[
			'single'            => true,
			'type'              => 'string',
			'description'       => __( 'Visibility of the post.', 'commons-in-a-box' ),
			'show_in_rest'      => true,
			'auth_callback'     => function() {
				return current_user_can( 'edit_posts' );
			},
			'sanitize_callback' => function( $value ) {
				return sanitize_text_field( $value );
			},
		]
	);
}
add_action( 'init', 'openlab_register_post_visibility_meta', 20 );

/**
 * Single-post access control for openlab_post_visibility.
 *
 * @return void
 */
function openlab_post_visibility_access_control() {
	$queried_object = get_queried_object();
	if ( ! is_a( $queried_object, 'WP_Post' ) ) {
		return;
	}

	$post_visibility = get_post_meta( $queried_object->ID, 'openlab_post_visibility', true );

	// Logged-in members only.
	if ( 'members-only' === $post_visibility ) {
		// If the user is logged in, allow access.
		if ( is_user_logged_in() ) {
			return;
		}

		// If the user is not logged in, redirect to the login page.
		wp_safe_redirect( wp_login_url( get_permalink( $queried_object->ID ) ) );
		exit;
	}

	if ( 'group-members-only' === $post_visibility ) {
		$redirect = null;
		if ( ! is_user_logged_in() ) {
			$redirect = wp_login_url( get_permalink( $queried_object->ID ) );
		} else {
			$current_site_group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );
			if ( $current_site_group_id && ! groups_is_user_member( get_current_user_id(), $current_site_group_id ) ) {
				$redirect = home_url();
			}
		}

		if ( $redirect ) {
			wp_safe_redirect( $redirect );
			exit;
		}
	}

	// If no visibility is set, or some other value exists, fall through and allow access.
}
add_action( 'template_redirect', 'openlab_post_visibility_access_control' );

/**
 * Remove posts from queries according to openlab_post_visibility settings.
 *
 * @param WP_Query $query Query object.
 * @return void
 */
function openlab_post_visibility_query_filter( $query ) {
	// Don't perform the query on singular posts, which are handled by template_redirect logic.
	if ( $query->is_singular() && $query->is_main_query() ) {
		return;
	}

	$invisible_post_ids = openlab_get_invisible_post_ids();
	if ( ! $invisible_post_ids ) {
		return;
	}

	// We will run a separate query and pass posts to post__in.
	$post__not_in = $query->get( 'post__not_in' );
	if ( ! is_array( $post__not_in ) ) {
		$post__not_in = [];
	}

	$post__not_in = array_merge( $post__not_in, $invisible_post_ids );
	$query->set( 'post__not_in', $post__not_in );
}
add_action( 'pre_get_posts', 'openlab_post_visibility_query_filter' );

/**
 * Gets a list of post IDs that are not visible to the current user.
 *
 * @param int $blog_id Blog ID. Defaults to current blog.
 * @return array
 */
function openlab_get_invisible_post_ids( $blog_id = null ) {
	static $post_ids = [];

	if ( null === $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	if ( ! isset( $post_ids[ $blog_id ] ) ) {
		// If there's no associated group ID, there's no visibility settings.
		$current_site_group_id = openlab_get_group_id_by_blog_id( $blog_id );
		if ( ! $current_site_group_id ) {
			$post_ids[ $blog_id ] = [];
			return $post_ids[ $blog_id ];
		}

		// If the user is a super admin or a group member, allow access to all posts.
		if ( is_super_admin() || groups_is_user_member( get_current_user_id(), $current_site_group_id ) ) {
			$post_ids[ $blog_id ] = [];
			return $post_ids[ $blog_id ];
		}

		// If we've gotten here, the current user is not a group member.
		$invisible_settings = [ 'group-members-only' ];

		if ( ! is_user_logged_in() ) {
			$invisible_settings[] = 'members-only';
		}

		$switched = false;
		if ( get_current_blog_id() !== $blog_id ) {
			switch_to_blog( $blog_id );
			$switched = true;
		}

		remove_action( 'pre_get_posts', 'openlab_post_visibility_query_filter' );
		$post_ids[ $blog_id ] = get_posts(
			[
				'post_type'   => 'any',
				'post_status' => 'any',
				'fields'      => 'ids',
				'meta_query'  => [
					[
						'key'   => 'openlab_post_visibility',
						'value' => $invisible_settings,
					],
				],
			]
		);
		add_action( 'pre_get_posts', 'openlab_post_visibility_query_filter' );

		if ( $switched ) {
			restore_current_blog();
		}
	}

	return $post_ids[ $blog_id ];
}

/**
 * Ensure that the "existing activity ID" query in bp_activity_post_type_publish() finds hidden items.
 *
 * Otherwise a duplicate activity item is created.
 */
add_filter(
	'bp_before_activity_get_parse_args',
	function( $args ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
		$db        = debug_backtrace();
		$do_filter = false;
		foreach ( $db as $_db ) {
			if ( 'bp_activity_post_type_publish' === $_db['function'] ) {
				$do_filter = true;
				break;
			}
		}

		if ( $do_filter ) {
			$args['show_hidden'] = true;
		}

		return $args;
	}
);

/**
 * Sets hide_sitewide flag for posts that have been posted via the REST API.
 *
 * When a post is created via the REST API, as is the case when composing with
 * the Block Editor, postmeta such as openlab_post_visibility is not set until
 * after the post is created. As such, it's not available when BP creates the
 * activity item.
 */
function openlab_modify_hide_sitewide_for_non_public_rest_posts( $post_id ) {
	// Get activity item associated with this blog post.
	$activity = bp_activity_get(
		[
			'show_hidden' => true,
			'filter'      => [
				'action'       => 'new_blog_post',
				'object'       => 'groups',
				'primary_id'   => openlab_get_group_id_by_blog_id( get_current_blog_id() ),
				'secondary_id' => $post_id,
			],
		]
	);

	if ( empty( $activity['activities'] ) ) {
		return;
	}

	$activity = $activity['activities'][0];

	openlab_toggle_hide_sitewide_for_post_visibility( $activity->id );
}
add_action( 'wp_after_insert_post', 'openlab_modify_hide_sitewide_for_non_public_rest_posts' );

/**
 * Toggle hide_sitewide on activity linked to posts that have custom openlab_post_visibility.
 *
 * @since 1.6.0
 *
 * @param int $activity_id Activity ID.
 * @return void
 */
function openlab_toggle_hide_sitewide_for_post_visibility( $activity_id ) {
	$activity = new BP_Activity_Activity( $activity_id );
	if ( in_array( $activity->type, [ 'new_blog_post', 'new_blog_comment' ], true ) ) {
		if ( 'new_blog_post' === $activity->type ) {
			$post_id = (int) $activity->secondary_item_id;
		} else {
			$comment = get_comment( $activity->secondary_item_id );
			$post_id = (int) $comment->comment_post_ID;
		}

		// For safety, we only switch to hide_sitewide, never back again.
		$post_visibility = get_post_meta( $post_id, 'openlab_post_visibility', true );
		global $wpdb;
		if ( ! $activity->hide_sitewide && in_array( $post_visibility, [ 'members-only', 'group-members-only' ], true ) ) {
			$cloned_activity = new BP_Activity_Activity( $activity_id );

			// This is the only reliable way to take precedence over other hooks.
			add_action(
				'bp_activity_before_save',
				function( $activity_object ) {
					$activity_object->hide_sitewide = 1;
				},
				99999
			);

			$saved = $cloned_activity->save();
		}
	}
}
