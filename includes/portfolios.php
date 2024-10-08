<?php

/**
 * Functionality related to Portfolio
 *
 * Overview:
 *  - 'portfolio' is a group type, alongside 'course', 'project', and 'club'
 *  - Portfolios must have associated sites
 *  - One portfolio per user
 */

/////////////////////////
//  PORTFOLIO DETAILS  //
/////////////////////////

/**
 * Get the portfolio group type.
 *
 * @return \CBOX\OL\GroupType|null Null if none is found.
 */
function cboxol_get_portfolio_group_type() {
	$group_types = cboxol_get_group_types();
	foreach ( $group_types as $group_type ) {
		if ( $group_type->get_is_portfolio() ) {
			return $group_type;
		}
	}

	return null;
}

/**
 * Get a user's portfolio *group* id
 *
 * @param int $user_id Defaults to displayed user, then to current member loop user
 * @return int
 */
function openlab_get_user_portfolio_id( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = openlab_fallback_user();
	}

	// Extra fallback for the case of portfolios: get the user associated
	// with the current group
	if ( ! $user_id ) {
		$user_id = openlab_get_user_id_from_portfolio_group_id( bp_get_current_group_id() );
	}

	$group_id = bp_get_user_meta( $user_id, 'portfolio_group_id', true );

	return (int) $group_id;
}

/**
 * Does a given user have a portfolio?
 *
 * @param int $user_id Defaults to displayed user, then to current member loop user
 * @return bool
 */
function openlab_user_has_portfolio( $user_id = 0 ) {
	return (bool) openlab_get_user_portfolio_id( $user_id );
}

/**
 * Echo a user's portfolio site URL
 */
function openlab_user_portfolio_url( $user_id = 0 ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo openlab_get_user_portfolio_url( $user_id );
}
	/**
	 * Get a user's portfolio URL
	 *
	 * @param int $user_id Defaults to displayed user, then to current member loop user
	 * @return string URL of the portfolio
	 */
function openlab_get_user_portfolio_url( $user_id = 0 ) {
	$group_id = openlab_get_user_portfolio_id( $user_id );
	$site_url = openlab_get_group_site_url( $group_id );

	return $site_url;
}

/**
 * Echo a user's portfolio profile URL
 */
function openlab_user_portfolio_profile_url( $user_id = 0 ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo openlab_get_user_portfolio_profile_url( $user_id );
}
	/**
	 * Get a user's portfolio profile URL
	 *
	 * @param int $user_id
	 * @return string
	 */
function openlab_get_user_portfolio_profile_url( $user_id = 0 ) {
	$group_id    = openlab_get_user_portfolio_id( $user_id );
	$profile_obj = groups_get_group( array( 'group_id' => $group_id ) );
	return bp_get_group_permalink( $profile_obj );
}

/**
 * Is a user's portfolio site local (vs external)?
 *
 * @param int $user_id
 * @return bool
 */
function openlab_user_portfolio_site_is_local( $user_id = 0 ) {
	$group_id = openlab_get_user_portfolio_id( $user_id );
	return (bool) openlab_get_site_id_by_group_id( $group_id );
}

/**
 * Should the user's portfolio link be shown on the user's profile?
 *
 * @since 1.6.0
 *
 * @param int $user_id
 * @return bool
 */
function openlab_show_portfolio_link_on_user_profile( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = bp_displayed_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	$show_raw = get_user_meta( $user_id, 'show_portfolio_link_on_user_profile', true );

	if ( '' === $show_raw ) {
		$show = true;
	} else {
		$show = '1' === $show_raw;
	}

	return (bool) $show;
}

/**
 * Save the setting of whether the user's portfolio link should be shown on the user's profile.
 *
 * @since 1.6.0
 *
 * @param int $user_id The user ID.
 * @param bool $show Whether to show the portfolio link.
 */
function openlab_save_show_portfolio_link_on_user_profile( $user_id, $show ) {
	$save_value = $show ? '1' : '0';

	update_user_meta( $user_id, 'show_portfolio_link_on_user_profile', $save_value );
}

/**
 * Get the user id of a portfolio user from the portfolio group's id
 *
 * @param int $group_id
 * @return bool
 */
function openlab_get_user_id_from_portfolio_group_id( $group_id = 0 ) {
	global $wpdb;

	$user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'portfolio_group_id' AND meta_value = %s", $group_id ) );

	return (int) $user_id;
}

/**
 * Suggest a name for a portfolio, based on the user's FN + LN
 */
function openlab_suggest_portfolio_name() {
	/* translators: portfolio owner's display name */
	return sprintf( __( "%s's Portfolio", 'commons-in-a-box' ), bp_loggedin_user_fullname() );
}

/**
 * Suggest a path for a portfolio, based on the user's display name.
 */
function openlab_suggest_portfolio_path( $user_id = null ) {
	$portfolio_type = cboxol_get_portfolio_group_type();
	if ( is_wp_error( $portfolio_type ) ) {
		return '';
	}

	if ( ! $user_id ) {
		$user_id = bp_loggedin_user_id();
	}

	$display_name = bp_core_get_user_displayname( $user_id );
	$slug         = sanitize_title( $display_name . '-' . $portfolio_type->get_slug() );

	// Ensure uniqueness.
	$incr = 2;
	$base = $slug;
	// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
	while ( $foo = get_id_from_blogname( $slug ) ) {
		$slug = $base . '-' . $incr;
		$incr++;
	}

	return $slug;
}

/** Group Portfolios *********************************************************/

/**
 * Get an array of group member portfolio info
 */
function openlab_get_group_member_portfolios( $group_id = false, $sort_by = 'display_name', $type = 'public' ) {
	if ( ! $group_id ) {
		$group_id = openlab_fallback_group();
	}

	$cache_key  = 'member_portfolios_' . $sort_by;
	$portfolios = groups_get_groupmeta( $group_id, $cache_key );

	if ( '' === $portfolios ) {
		$portfolios    = array();
		$group_members = new BP_Group_Member_Query(
			array(
				'group_id'   => $group_id,
				'per_page'   => false,
				'page'       => false,
				'group_role' => array( 'member', 'mod', 'admin' ),
				'type'       => 'alphabetical',
			)
		);

		foreach ( $group_members->results as $member ) {
			$portfolio_id      = openlab_get_user_portfolio_id( $member->ID );
			$portfolio_group   = groups_get_group( array( 'group_id' => $portfolio_id ) );
			$portfolio_blog_id = openlab_get_site_id_by_group_id( $portfolio_id );

			if ( empty( $portfolio_id ) || empty( $portfolio_group ) ) {
				continue;
			}

			// Don't add hidden portfolios, unless they've been requested
			if ( 'all' !== $type && 'hidden' === $portfolio_group->status ) {
				continue;
			}

						// If the portfolio_blog_id is empty, this may be an external portfolio.
			if ( empty( $portfolio_blog_id ) ) {
				$portfolio_url = openlab_get_external_site_url_by_group_id( $portfolio_id );

				// No URL found? There's no portfolio to link to.
				if ( empty( $portfolio_url ) ) {
					continue;
				}

				// Use the group title for the link text.
				$portfolio_title = $portfolio_group->name;
			} else {
				$portfolio_url   = openlab_get_user_portfolio_url( $member->ID );
				$portfolio_title = get_blog_option( $portfolio_blog_id, 'blogname' );
			}

						$portfolio = array(
							'user_id'           => $member->ID,
							'user_display_name' => $member->display_name,
							'user_type'         => xprofile_get_field_data( 'Account Type', $member->ID ),
							'portfolio_id'      => $portfolio_id,
							'portfolio_url'     => $portfolio_url,
							'portfolio_title'   => $portfolio_title,
						);

						$portfolios[] = $portfolio;
		}

		switch ( $sort_by ) {
			case 'display_name':
				$key = 'user_display_name';
				break;

			case 'random':
				$key = 'random';
				break;

			case 'title':
			default:
				$key = 'portfolio_title';
				break;
		}

		if ( 'random' === $key ) {
			shuffle( $portfolios );
		} else {
			usort(
				$portfolios,
				function( $a, $b ) use ( $key ) {
					$values = array(
						0 => $a[ $key ],
						1 => $b[ $key ],
					);
					$cmp    = strcasecmp( $values[0], $values[1] );

					if ( 0 > $cmp ) {
						$retval = -1;
					} elseif ( 0 < $cmp ) {
						$retval = 1;
					} else {
						$retval = 0;
					}
					return $retval;
				}
			);
		}

		groups_update_groupmeta( $group_id, $cache_key, $portfolios );
	}

	return $portfolios;
}

/**
 * Cache busting for group portfolio lists
 *
 * Bust the cache when:
 * - group membership changes - openlab_bust_group_portfolios_cache_on_membership_change()
 * - a group member adds/removes a portfolio site
 */
function openlab_bust_group_portfolio_cache( $group_id = 0 ) {
	global $wpdb, $bp;

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$keys = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM {$bp->groups->table_name_groupmeta} WHERE group_id = %d AND meta_key LIKE %s", $group_id, '%' . $wpdb->esc_like( 'member_portfolios_' ) . '%' ) );
	foreach ( $keys as $k ) {
		groups_delete_groupmeta( $group_id, $k );
	}

	// regenerate
	openlab_get_group_member_portfolios();
}

/**
 * Bust group portfolio cache when membership changes
 */
function openlab_bust_group_portfolios_cache_on_membership_change( $member ) {
	openlab_bust_group_portfolio_cache( $member->group_id );
}
add_action( 'groups_member_after_save', 'openlab_bust_group_portfolios_cache_on_membership_change' );


/**
 * Bust group portfolio cache when member leaves group
 */
function openlab_bust_group_portfolios_cache_on_group_leave( $group_id ) {
	openlab_bust_group_portfolio_cache( $group_id );
}
add_action( 'groups_uninvite_user', 'openlab_bust_group_portfolios_cache_on_group_leave' );

/**
 * Bust group portfolio cache when member is removed from group.
 *
 * We can't run on 'groups_remove_member' because it runs before the member
 * is removed.
 */
function openlab_bust_group_portfolios_cache_on_group_remove( $user_id, $group_id ) {
	openlab_bust_group_portfolio_cache( $group_id );
}
add_action( 'groups_removed_member', 'openlab_bust_group_portfolios_cache_on_group_remove', 10, 2 );

/**
 * Bust group portfolio cache when a member removes themselves from the group
 */
function openlab_bust_group_portfolios_cache_on_self_remove( $group_id, $user_id ) {
	openlab_bust_group_portfolio_cache( $group_id );
}
add_action( 'groups_leave_group', 'openlab_bust_group_portfolios_cache_on_self_remove', 10, 2 );

/**
 * Bust group portfolio cache when membership changes
 */
function openlab_bust_group_portfolios_cache_on_portfolio_event( $group_id ) {
	if ( ! cboxol_is_portfolio( $group_id ) ) {
		return;
	}

	// Delete the portfolio cache for each group the user is a member of
	// Don't regenerate - could be several groups. Let it happen on the fly
	$user_id   = openlab_get_user_id_from_portfolio_group_id( $group_id );
	$group_ids = groups_get_user_groups( $user_id );
	foreach ( $group_ids['groups'] as $gid ) {
		openlab_bust_group_portfolio_cache( $gid );
	}
}
add_action( 'groups_before_delete_group', 'openlab_bust_group_portfolios_cache_on_portfolio_event' );
add_action( 'groups_created_group', 'openlab_bust_group_portfolios_cache_on_portfolio_event' );
add_action( 'groups_group_settings_edited', 'openlab_bust_group_portfolios_cache_on_portfolio_event' );

/**
 * Check whether portfolio list display is enabled for a group.
 */
function openlab_portfolio_list_enabled_for_group( $group_id = 0 ) {
	// Always bail if portfolios are not enabled.
	if ( ! cboxol_get_portfolio_group_type() ) {
		return false;
	}

	if ( ! $group_id ) {
		$group_id = bp_get_current_group_id();
	}

	$group_type = cboxol_get_group_group_type( $group_id );

	if ( is_wp_error( $group_type ) ) {
		return false;
	}

	// Portfolio groups never have the list enabled
	if ( $group_type->get_is_portfolio() ) {
		return false;
	}

	if ( $group_type->get_enable_portfolio_list_by_default() ) {
		$enabled = 'no' !== groups_get_groupmeta( $group_id, 'portfolio_list_enabled' );

		// Otherwise default to 'no'
	} else {
		$enabled = 'yes' === groups_get_groupmeta( $group_id, 'portfolio_list_enabled' );
	}

	return $enabled;
}

/**
 * Adjust widget description to match proper group type.
 *
 * The widget is registered too early to do this in the class constructor.
 */
function openlab_swap_portfolio_widget_description() {
	global $wp_registered_widgets;

	foreach ( $wp_registered_widgets as &$w ) {
		if ( 'Portfolio List' !== $w['name'] ) {
			continue;
		}

		$group_id   = openlab_get_group_id_by_blog_id( get_current_blog_id() );
		$group_type = openlab_get_group_type_label(
			array(
				'group_id' => $group_id,
			)
		);

		$w['description'] = sprintf( 'Display a list of the Portfolios belonging to the members of this %s.', $group_type );
	}
}
add_action( 'bp_init', 'openlab_swap_portfolio_widget_description', 20 );

/**
 * Get the heading/title for the group portfolio listing.
 */
function openlab_portfolio_list_group_heading( $group_id = 0 ) {
	if ( ! $group_id ) {
		$group_id = bp_get_current_group_id();
	}

	$heading = groups_get_groupmeta( $group_id, 'portfolio_list_heading' );

	if ( ! $heading ) {
		$heading = 'Member Portfolios';
	}

	return $heading;
}

/**
 * Add the portfolio display to group sidebars.
 */
function openlab_portfolio_list_group_display() {
	if ( ! openlab_portfolio_list_enabled_for_group() ) {
		return;
	}

	// Non-public groups shouldn't show this to non-members. See #997
	$group = groups_get_current_group();
	if ( 'public' !== $group->status && empty( $group->user_has_access ) ) {
		return false;
	}

	// In a course, display only to members.
	$group_type = cboxol_get_group_group_type( $group->id );
	if ( ! is_wp_error( $group_type ) && $group_type->get_is_course() && ! groups_is_user_member( bp_loggedin_user_id(), $group->id ) ) {
		return;
	}

	$portfolio_data = openlab_get_group_member_portfolios();

	// Hide private-member portfolios from non-members.
	if ( current_user_can( 'view_private_members_of_group', $group->id ) ) {
		$group_private_members = [];
	} else {
		$group_private_members = openlab_get_private_members_of_group( $group->id );
	}

	$portfolio_data = array_filter(
		$portfolio_data,
		function( $portfolio ) use ( $group_private_members ) {
			return ! in_array( $portfolio['user_id'], $group_private_members, true );
		}
	);

	// No member of the group has a portfolio
	if ( empty( $portfolio_data ) ) {
		return;
	}

	?>

	<div id="group-member-portfolio-sidebar-widget" class="sidebar-widget">
		<h2 class="sidebar-header">
			<?php echo esc_html( openlab_portfolio_list_group_heading() ); ?>
		</h2>

		<div class="sidebar-block">
			<ul class="group-member-portfolio-list sidebar-sublinks inline-element-list group-data-list">
				<?php foreach ( $portfolio_data as $pdata ) : ?>
					<li><a href="<?php echo esc_url( $pdata['portfolio_url'] ); ?>"><?php echo esc_html( $pdata['user_display_name'] ); ?></a></li>
				<?php endforeach ?>
			</ul>

		</div>
	</div>

	<?php
}
add_action( 'bp_group_options_nav', 'openlab_portfolio_list_group_display', 20 );

/**
 * Catch form requests (from the widget dropdown) to redirect to a student portfolio
 *
 * See {@link OpenLab_Course_Portfolios_Widget::widget()}
 */
function openlab_redirect_to_student_portfolio_catcher() {
	if ( empty( $_GET['portfolio-goto'] ) ) {
		return;
	}

	check_admin_referer( 'portfolio_goto', '_pnonce' );

	$url = urldecode( $_GET['portfolio-goto'] );
	wp_safe_redirect( $url );
}
add_action( 'wp', 'openlab_redirect_to_student_portfolio_catcher' );

/////////////////////////
//    MISCELLANEOUS    //
/////////////////////////

/**
 * Echoes the URL for the portfolio creation page
 */
function openlab_portfolio_creation_url() {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo openlab_get_portfolio_creation_url();
}
	/**
	 * Returns the URL for the portfolio creation page
	 */
function openlab_get_portfolio_creation_url() {
	if ( ! bp_is_active( 'groups' ) ) {
		return '';
	}

	return add_query_arg(
		[
			'group_type' => 'portfolio',
			'new'        => 'true',
		],
		bp_groups_get_create_url( [ 'group-details' ] )
	);
}

/**
 * Remove BPGES settings from portfolio group admin and creation screens
 */
function openlab_remove_bpges_settings_for_portfolios() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( cboxol_is_portfolio() || ( bp_is_group_create() && isset( $_GET['group_type'] ) && 'portfolio' === $_GET['group_type'] ) ) {
		remove_action( 'bp_after_group_settings_admin', 'ass_default_subscription_settings_form' );
		remove_action( 'bp_after_group_settings_creation_step', 'ass_default_subscription_settings_form' );
	}
}
add_action( 'bp_actions', 'openlab_remove_bpges_settings_for_portfolios', 1 );

/**
 * Mark a group as being a user's portfolio
 */
function openlab_associate_portfolio_group_with_user( $group_id, $user_id ) {
	bp_update_user_meta( $user_id, 'portfolio_group_id', $group_id );

	$member_type = cboxol_get_user_member_type( $user_id );
	if ( ! is_wp_error( $member_type ) ) {
		groups_update_groupmeta( $group_id, 'portfolio_user_type', $member_type->get_slug() );
	}
}

/**
 * Is this my portfolio?
 */
function openlab_is_my_portfolio() {
	return bp_is_group() && cboxol_is_portfolio() && is_user_logged_in() && openlab_get_user_id_from_portfolio_group_id( bp_get_current_group_id() ) === bp_loggedin_user_id();
}

/**
 * On portfolio group deletion, also do the following:
 *  - Delete user metadata regarding portfolio affiliation
 */
function openlab_delete_portfolio( $group_id ) {
	if ( ! cboxol_is_portfolio( $group_id ) ) {
		return;
	}

	$user_id = openlab_get_user_id_from_portfolio_group_id( $group_id );
	bp_delete_user_meta( $user_id, 'portfolio_group_id' );
}
add_action( 'groups_before_delete_group', 'openlab_delete_portfolio' );

/**
 * After portfolio delete, redirect to user profile page
 */
function openlab_delete_portfolio_redirect() {
	bp_core_redirect( bp_loggedin_user_url() );
}

/**
 * Enforce one portfolio per person, by redirecting away from the portfolio creation page
 */
function openlab_enforce_one_portfolio_per_person() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( bp_is_active( 'groups' ) && bp_is_group_creation_step( 'group-details' ) && isset( $_GET['group_type'] ) && 'portfolio' === $_GET['group_type'] && openlab_user_has_portfolio( bp_loggedin_user_id() ) ) {
		bp_core_add_message( sprintf( 'You already have %s', openlab_get_portfolio_label( 'leading_a=1' ) ), 'error' );
		bp_core_redirect( bp_loggedin_user_url() );
	}
}
add_action( 'bp_actions', 'openlab_enforce_one_portfolio_per_person', 1 );

/**
 * Don't display Email settings on portfolio profile headers
 */
function openlab_remove_email_settings_from_portfolios() {
	if ( cboxol_is_portfolio() ) {
		remove_action( 'bp_group_header_meta', 'ass_group_subscribe_button' );
	}
}
add_action( 'bp_group_header_meta', 'openlab_remove_email_settings_from_portfolios', 1 );

/**
 * Filters strings from openlab-portfolio to use strings defined in CBOX-OL admin.
 *
 * @since 1.3.0
 *
 * @param string $label
 * @param string $type
 * @return string
 */
function cboxol_filter_openlab_portfolio_label( $label, $type ) {
	$portfolio_group_type = cboxol_get_portfolio_group_type();

	if ( ! $portfolio_group_type || ! $portfolio_group_type->get_is_enabled() ) {
		return $label;
	}

	$filtered_label = $portfolio_group_type->get_label( $type );
	if ( ! $filtered_label ) {
		return $label;
	}

	return $filtered_label;
}
add_filter( 'openlab_portfolio_get_label', 'cboxol_filter_openlab_portfolio_label', 10, 2 );

