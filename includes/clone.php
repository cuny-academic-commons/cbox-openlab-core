<?php

/**
 * Functionality related to cloning.
 *
 * @since 1.2.0
 */

/**
 * Loads cloning widgets.
 *
 * @since 1.2.0
 */
function cboxol_register_clone_widgets() {
	$widgets = [
		'\CBOX\OL\Widget\CloneCredits',
		'\CBOX\OL\Widget\ShareableContent',
	];

	foreach ( $widgets as $widget ) {
		register_widget( $widget );
	}
}
add_action( 'widgets_init', 'cboxol_register_clone_widgets' );

function cboxol_unregister_clone_widgets() {
	$group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );
	if ( $group_id ) {
		return;
	}

	$widgets = [
		'\CBOX\OL\Widget\CloneCredits',
		'\CBOX\OL\Widget\ShareableContent',
	];

	foreach ( $widgets as $widget ) {
		unregister_widget( $widget );
	}
}
add_action( 'bp_setup_globals', 'cboxol_unregister_clone_widgets' );

/**
 * Get the clone history of a group.
 *
 * Returns an array of group IDs, ordered from oldest to newest.
 *
 * @since 1.2.0
 *
 * @param int $group_id ID of the group.
 * @return array
 */
function openlab_get_group_clone_history( $group_id ) {
	$history = groups_get_groupmeta( $group_id, 'clone_history', true );
	if ( empty( $history ) ) {
		$history = array();

		// Legacy.
		$clone_source_group = groups_get_groupmeta( $group_id, 'clone_source_group_id', true );
		if ( $clone_source_group ) {
			$history[] = $clone_source_group;
		}
	}

	return array_map( 'intval', $history );
}

/**
 * Get the clone historty of a group.
 *
 * @param int $group_id        ID for the group.
 * @param int $exclude_creator Whether to exclude groups created by the specified user.
 *                             These groups are trimmed only from the end of the ancestry chain.
 *                             Default true.
 * @return array $history The clone history.
 */
function openlab_get_group_clone_history_data( $group_id, $exclude_creator = null ) {
	$source_ids = openlab_get_group_clone_history( $group_id );
	$history    = array();
	if ( ! $source_ids ) {
		return $history;
	}

	$group_type = cboxol_get_group_group_type( $group_id );

	foreach ( $source_ids as $source_id ) {
		$source_group = groups_get_group( $source_id );

		$course_code = groups_get_groupmeta( $source_id, 'cboxol_course_code', true );

		$history[] = array(
			'group_id'           => $source_id,
			'group_url'          => bp_get_group_permalink( $source_group ),
			'group_name'         => $course_code ? $course_code : $group_type->get_label( 'singular' ),
			'group_creator_id'   => $source_group->creator_id,
			'group_creator_name' => bp_core_get_user_displayname( $source_group->creator_id ),
			'group_creator_url'  => bp_core_get_user_domain( $source_group->creator_id ),
		);
	}

	// Trim exclude_creator groups.
	if ( $history && null !== $exclude_creator ) {
		$exclude_creator = intval( $exclude_creator );
		$source_count    = count( $history ) - 1;
		for ( $i = $source_count; $i >= 0; $i-- ) {
			if ( $history[ $i ]['group_creator_id'] !== $exclude_creator ) {
				break;
			}

			unset( $history[ $i ] );
		}
	}

	return $history;
}

/**
 * Gets a text-based list of links to clone history.
 *
 * @since 1.2.0
 *
 * @param int $group_id Group ID.
 * @return string
 */
function openlab_get_group_clone_history_list( $group_id, $exclude_creator = null ) {
	$list = '';

	$history_data = openlab_get_group_clone_history_data( $group_id, $exclude_creator );
	if ( ! $history_data ) {
		return $list;
	}

	$credits_groups = array_map(
		function( $clone_group ) {
			return sprintf(
				'<li><a href="%s">%s</a> &mdash; <a href="%s">%s</a></li>',
				esc_attr( $clone_group['group_url'] ),
				esc_html( $clone_group['group_name'] ),
				esc_attr( $clone_group['group_creator_url'] ),
				esc_html( $clone_group['group_creator_name'] )
			);
		},
		$history_data
	);

	return '<ul class="group-credits">' . implode( "\n", $credits_groups ) . '</ul>';
}

/**
 * Get the group ID of a group's clone source.
 *
 * @param int $group_id
 * @return int $group_id
 */
function cboxol_get_clone_source_group_id( $group_id ) {
	return (int) groups_get_groupmeta( $group_id, 'clone_source_group_id' );
}

/**
 * Determines whether a group can be cloned.
 *
 * @param int $group_id The group ID.
 * @param bool
 */
function openlab_group_can_be_cloned( $group_id = null ) {
	if ( null === $group_id ) {
		$group_id = bp_get_current_group_id();
	}

	if ( ! $group_id ) {
		return false;
	}

	$sharing_enabled_for_group = groups_get_groupmeta( $group_id, 'enable_sharing', true );

	return ! empty( $sharing_enabled_for_group );
}

/**
 * Determines whether a current user can clone the group.
 *
 * @param int $group_id The group id.
 * @return bool
 */
function openlab_user_can_clone_group( $group_id ) {
	$group_type = cboxol_get_group_group_type( $group_id );

	if ( is_wp_error( $group_type ) ) {
		return false;
	}

	if ( is_super_admin() ) {
		return true;
	}

	$user_id       = get_current_user_id();
	$can_be_cloned = $group_type->get_can_be_cloned();

	if ( groups_is_user_admin( $user_id, $group_id ) ) {
		return $can_be_cloned;
	}

	$member_type = cboxol_get_user_member_type( $user_id );

	if ( is_wp_error( $member_type ) ) {
		return false;
	}

	$sharing_enabled = openlab_group_can_be_cloned( $group_id );

	if ( $group_type->get_is_course() && $sharing_enabled ) {
		return $member_type->get_can_create_courses();
	}

	if ( $sharing_enabled ) {
		return $can_be_cloned;
	}

	return false;
}

/**
 * Flushes rewrite rules on newly created sites.
 *
 * @since 1.2.4
 */
function cboxol_flush_rewrite_rules_on_newly_created_sites() {
	if ( bp_is_root_blog() ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( get_option( 'cboxol_initial_rewrite_flush' ) ) {
		return;
	}

	update_option( 'cboxol_initial_rewrite_flush', time() );

	flush_rewrite_rules( false );
}
add_action( 'shutdown', 'cboxol_flush_rewrite_rules_on_newly_created_sites' );
