<?php

/**
 * Modifications for PressForward.
 */
/*
 * @todo list:
 * - Check PF minimum-role code and maybe get a better way to do it.
 */
add_action(
	'admin_enqueue_scripts',
	function() {
		$min = bp_core_get_minified_asset_suffix();

		wp_enqueue_script( 'bp-mentions', buddypress()->plugin_url . "bp-activity/js/mentions{$min}.js", array( 'jquery', 'jquery-atwho' ), bp_get_version(), true );
		wp_enqueue_style( 'bp-mentions-css', buddypress()->plugin_url . "bp-activity/css/mentions{$min}.css", array(), bp_get_version() );

		wp_style_add_data( 'bp-mentions-css', 'rtl', true );
		if ( $min ) {
			wp_style_add_data( 'bp-mentions-css', 'suffix', $min );
		}

		$ver = cboxol_get_asset_version();

		wp_enqueue_script( 'pf-bp-mentions', CBOXOL_PLUGIN_URL . 'assets/js/pf-bp.js', array( 'bp-mentions' ), $ver, true );
		wp_enqueue_style( 'pf-bp-mentions', CBOXOL_PLUGIN_URL . 'assets/css/pf-bp.css', array(), $ver );

		// @todo Should some roles be excluded/included? PF doesn't make it easy to get its internal setting for this, and I don't know how to translate "minmium" setting into something user-queryable
		//$minimum_role = get_option( 'pf_menu_group_access', pressforward()->menu->user_interface->pf_get_defining_capability_by_role( 'contributor' ) );

		$roles          = wp_roles()->get_names();
		$roles_to_match = array();
		foreach ( $roles as $role_name => $_ ) {
			$role = wp_roles()->get_role( $role_name );
			foreach ( $role as $_role ) {
				if ( ! $role->has_cap( 'edit_posts' ) ) {
					continue;
				}

				$roles_to_match[] = $role_name;
			}
		}

		// BP will try to force blog_id=0, so we must override via filter.
		$filter = function( $args ) {
			$args['blog_id'] = get_current_blog_id();
			return $args;
		};

		add_filter( 'bp_wp_user_query_args', $filter );

		$user_query = new BP_User_Query(
			array(
				'role__in' => $roles_to_match,
			)
		);

		remove_filter( 'bp_wp_user_query_args', $filter );

		$users = array();
		foreach ( $user_query->results as $user ) {
			$users[] = array(
				'ID'    => $user->user_nicename,
				'image' => bp_core_fetch_avatar(
					array(
						'html'    => false,
						'item_id' => $user->ID,
					)
				),
				'name'  => bp_core_get_user_displayname( $user->ID ),
			);
		}

		wp_localize_script(
			'pf-bp-mentions',
			'PFBPMentions',
			array(
				'users' => $users,
			)
		);

		// If the script has been enqueued, let's attach our mentions TinyMCE init callback.
		add_filter( 'tiny_mce_before_init', 'bp_add_mentions_on_tinymce_init', 10, 2 );
	}
);

/**
 * Sets the default comment permissions.
 */
add_filter(
	'default_option_pf_feature_comments_access',
	function() {
		return pf_get_defining_capability_by_role( 'contributor' );
	}
);

/**
 * Turn @-mentions into links in PF comment content.
 */
function pfbp_add_mention_links_to_comment_text( $text, $comment ) {
	if ( ! function_exists( 'bp_activity_at_name_filter' ) ) {
		return $text;
	}

	$post                = get_post( $comment->comment_post_ID );
	$feed_item_post_type = pressforward( 'schema.feed_item' )->post_type;
	if ( ! $post || $post->post_type !== $feed_item_post_type ) {
		return $text;
	}

	return bp_activity_at_name_filter( $text );
}
add_filter( 'comment_text', 'pfbp_add_mention_links_to_comment_text', 10, 2 );

function pfbp_register_activity_actions() {
	bp_activity_set_action(
		buddypress()->groups->id,
		'new_pf_feed_item_comment',
		__( 'Posted a comment on a PressForward feed item.', 'commons-in-a-box' ),
		'pfbp_format_activity_item_new_pf_feed_item_comment',
		__( 'PressForward Comments', 'commons-in-a-box' ),
		array( 'activity', 'member', 'member_groups' )
	);
}
add_action( 'bp_register_activity_actions', 'pfbp_register_activity_actions' );

/**
 * Format 'new_pf_feed_item_comment' activity actions.
 *
 * @param string $action   Static activity action.
 * @param object $activity Activity data object.
 * @return string
 */
function pfbp_format_activity_item_new_pf_feed_item_comment( $action, $activity ) {
	$user_link = bp_core_get_userlink( $activity->user_id );

	$site_id   = openlab_get_site_id_by_group_id( $activity->item_id );
	$site_name = get_blog_option( $site_id, 'blogname' );
	$site_link = '<a href="' . esc_url( get_home_url( $site_id ) ) . '">' . esc_html( $site_name ) . '</a>';

	$post_title = bp_activity_get_meta( $activity->id, 'post_title' );
	$post_url   = bp_activity_get_meta( $activity->id, 'post_url' );

	if ( ! $post_title || ! $post_url ) {
		switch_to_blog( $site_id );
		$comment    = get_comment( $activity->secondary_item_id );
		$post       = get_post( $comment->comment_post_ID );
		$post_title = $post->post_title;
		$post_url   = admin_url( 'admin.php?page=pf-menu' ) . '#modal-' . get_post_meta( $post->ID, 'item_id', true );
		restore_current_blog();

		bp_activity_update_meta( $activity->id, 'post_title', $post_title );
		bp_activity_update_meta( $activity->id, 'post_url', $post_url );
	}

	$post_link = sprintf( '<a href="%s">%s</a>', esc_url( $post_url ), esc_html( $post_title ) );

	// translators: 1. commenter link, 2. post link, 3. site link
	$action = sprintf( __( '%1$s posted a comment on the feed item %2$s on the site %3$s', 'commons-in-a-box' ), $user_link, $post_link, $site_link );

	/**
	 * Filters the 'new_pf_feed_item_comment' activity actions.
	 *
	 * @param string $action   The 'new_pf_feed_item_comment' activity action.
	 * @param object $activity Activity data object.
	 */
	return apply_filters( 'pfbp_activity_new_pf_feed_item_comment', $action, $activity );
}

/**
 * Generate activity item for a new PF comment.
 *
 * @param int        $comment_id ID of the comment.
 * @param WP_Comment $comment    Comment object.
 */
function pfbp_generate_activity_item_for_comment( $comment_id, $comment ) {
	if ( ! bp_is_active( 'activity' ) || ! bp_is_active( 'groups' ) ) {
		return;
	}

	// Don't run on non-PF content.
	$post                = get_post( $comment->comment_post_ID );
	$feed_item_post_type = pressforward( 'schema.feed_item' )->post_type;
	if ( ! $post || $post->post_type !== $feed_item_post_type ) {
		return;
	}

	// Get group site connection details from CBOX-OL.
	// @todo Abstract this to check for bp-groupblog or something else.
	$site_group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );
	if ( ! $site_group_id ) {
		return;
	}

	$user_link = bp_core_get_userlink( $comment->user_id );
	$site_name = get_option( 'blogname' );
	$site_link = '<a href="' . esc_url( home_url() ) . '">' . esc_html( $site_name ) . '</a>';

	$post_url  = admin_url( 'admin.php?page=pf-menu' ) . '#modal-' . get_post_meta( $post->ID, 'item_id', true );
	$post_link = sprintf( '<a href="%s">%s</a>', esc_url( $post_url ), esc_html( $post->post_title ) );

	// translators: 1. commenter link, 2. post link, 3. site link
	$action = sprintf( __( '%1$s posted a comment on the feed item %2$s on the site %3$s', 'commons-in-a-box' ), $user_link, $post_link, $site_link );

	groups_record_activity(
		array(
			'action'            => $action,
			'type'              => 'new_pf_feed_item_comment',
			'content'           => $comment->comment_content,
			'item_id'           => $site_group_id,
			'secondary_item_id' => $comment->comment_ID,
			'user_id'           => $comment->user_id,
			'hide_sitewide'     => true,
		)
	);
}
add_action( 'wp_insert_comment', 'pfbp_generate_activity_item_for_comment', 10, 2 );
