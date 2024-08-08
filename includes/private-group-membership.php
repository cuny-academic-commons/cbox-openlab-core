<?php

/**
 * Functionality related to private group memberships.
 *
 * @since 1.6.0
 */

/**
 * Gets the name of the private membership table.
 *
 * @since 1.6.0
 *
 * @return string
 */
function openlab_get_private_membership_table_name() {
	global $wpdb;
	return $wpdb->base_prefix . 'openlab_private_membership';
}

/**
 * Creates the private membership table.
 *
 * @since 1.6.0
 *
 * @return void
 */
function openlab_create_private_membership_table() {
	global $wpdb;

	$table_name = openlab_get_private_membership_table_name();

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table_name} (
		`id` bigint(20) NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) NOT NULL,
		`group_id` bigint(20) NOT NULL,
		PRIMARY KEY (`id`),
		KEY `user_id` (`user_id`),
		KEY `group_id` (`group_id`)
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

/**
 * Get private members of a group.
 *
 * @param int  $group_id     ID of the group.
 * @param bool $exclude_self Whether to exclude the current user. Default true.
 * @return array Array of user IDs.
 */
function openlab_get_private_members_of_group( $group_id, $exclude_self = true ) {
	static $members = [];

	global $wpdb;

	if ( null !== $members[ $group_id ] ) {
		$private_member_ids = $members[ $group_id ];
	} else {
		$table_name = openlab_get_private_membership_table_name();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$private_member_ids = $wpdb->get_col( $wpdb->prepare( "SELECT `user_id` FROM {$table_name} WHERE `group_id` = %d", $group_id ) );
	}

	$private_member_ids = array_map( 'intval', $private_member_ids );

	// Verify that these are in fact members of the group.
	$private_member_ids = array_filter(
		$private_member_ids,
		function( $user_id ) use ( $group_id ) {
			return groups_is_user_member( $user_id, $group_id );
		}
	);

	$members[ $group_id ] = $private_member_ids;

	if ( $exclude_self ) {
		$private_member_ids = array_diff( $private_member_ids, [ bp_loggedin_user_id() ] );
	}

	return $private_member_ids;
}

/**
 * Check if the membership for the specified group is private
 * for the logged user.
 *
 * @param int $group_id Group ID.
 * @return bool
 */
function openlab_is_my_membership_private( $group_id ) {
	global $wpdb;

	// Skip if group id is missing.
	if ( empty( $group_id ) ) {
		return false;
	}

	// Get private membership table.
	$table_name = openlab_get_private_membership_table_name();

	// Get current user id
	$user_id = bp_loggedin_user_id();

	// Check if the membership is private based on user id and group id.
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$query = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE `user_id` = %d AND `group_id` = %d", $user_id, $group_id ) );

	// If there is a record, return true. Otherwise, return false.
	if ( $query ) {
		return true;
	}

	return false;
}

/**
 * Get user's private membership groups.
 *
 * @param int $user_id User ID.
 * @return array
 */
function openlab_get_user_private_memberships( $user_id ) {
	global $wpdb;

	// Skip if user id is missing.
	if ( empty( $user_id ) ) {
		return [];
	}

	$table_name = openlab_get_private_membership_table_name();

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$query = $wpdb->get_results( $wpdb->prepare( "SELECT `group_id` FROM {$table_name} WHERE `user_id` = %d", $user_id ), OBJECT_K );

	$private_groups = array();

	if ( $query ) {
		foreach ( $query as $item ) {
			$private_groups[] = (int) $item->group_id;
		}
	}

	return $private_groups;
}

/**
 * Update private membership table with the user's group privacy data.
 *
 * @param int  $user_id    User ID.
 * @param int  $group_id   Group ID.
 * @param bool $is_private Is the group private.
 * @return bool
 */
function openlab_update_group_membership_privacy( $user_id, $group_id, $is_private ) {
	global $wpdb;

	// Get private membership table
	$table_name = openlab_get_private_membership_table_name();

	if ( $is_private ) {
		$success = $wpdb->insert(
			$table_name,
			[
				'user_id'  => $user_id,
				'group_id' => $group_id,
			]
		);
	} else {
		$success = $wpdb->delete(
			$table_name,
			[
				'user_id'  => $user_id,
				'group_id' => $group_id,
			]
		);
	}

	openlab_update_group_activity_privacy( $user_id, $group_id, $is_private );

	return (bool) $success;
}

/**
 * Update privacy for activity items associated with a member in a group.
 *
 * This can be used to perform a bulk update to hide_sitewide for a user's
 * group activity after the user has toggled their membership privacy.
 *
 * @param int  $user_id    User ID.
 * @param int  $group_id   Group ID.
 * @param bool $is_private Is the group private.
 * @return void
 */
function openlab_update_group_activity_privacy( $user_id, $group_id, $is_private ) {
	global $wpdb;

	/*
	 * For efficiency, we do a single query to update all activity items. This
	 * requires some cache manipulation. We use BP's API functions to set
	 * the meta flag, though, due to the complexity of cache invalidation
	 * with object metadata.
	 */
	$activity_args = [
		'filter'      => [
			'user_id'    => $user_id,
			'object'     => 'groups',
			'primary_id' => $group_id,
		],
		'show_hidden' => true,
	];

	$group = groups_get_group( $group_id );

	// Get private membership table
	$activity_table_name = buddypress()->activity->table_name;

	$query        = '';
	$activity_ids = [];
	if ( $is_private ) {
		$activities = bp_activity_get( $activity_args );

		$activity_ids = array_map(
			function( $activity ) {
				return ! $activity->hide_sitewide ? $activity->id : null;
			},
			$activities['activities']
		);

		$activity_ids = array_filter( $activity_ids );

		$query = "UPDATE {$activity_table_name} SET `hide_sitewide` = 1 WHERE `id` IN ( " . implode( ',', array_map( 'intval', $activity_ids ) ) . ' )';

		foreach ( $activity_ids as $activity_id ) {
			bp_activity_update_meta( $activity_id, 'openlab_private_membership_activity_toggled', 1 );
		}
	} elseif ( 'public' === $group->status ) {
		// Only switch the items back to hide_sitewide=0 if the group is public.
		$activity_args['meta_query'] = [
			[
				'key' => 'openlab_private_membership_activity_toggled',
			],
		];

		$activities = bp_activity_get( $activity_args );

		$activity_ids = array_map(
			function( $activity ) {
				return $activity->hide_sitewide ? $activity->id : null;
			},
			$activities['activities']
		);

		$activity_ids = array_filter( $activity_ids );

		$query = "UPDATE {$activity_table_name} SET `hide_sitewide` = 0 WHERE `id` IN ( " . implode( ',', array_map( 'intval', $activity_ids ) ) . ' )';

		foreach ( $activity_ids as $activity_id ) {
			bp_activity_delete_meta( $activity_id, 'openlab_private_membership_activity_toggled' );
		}
	}

	if ( $query ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $query );
		bp_activity_clear_cache_for_deleted_activity( $activity_ids );
	}
}

/**
 * Flips an activity to hide_sitewide for private memberships.
 *
 * Operates on a single activity item.
 *
 * @since 1.6.0
 *
 * @param int $activity_id Activity ID.
 * @return void
 */
function openlab_toggle_hide_sitewide_for_private_membership_activity( $activity_id ) {
	$activity = new BP_Activity_Activity( $activity_id );
	if ( $activity->hide_sitewide ) {
		return;
	}

	$activity->hide_sitewide = 1;

	$saved = $activity->save();

	bp_activity_update_meta( $activity_id, 'openlab_private_membership_activity_toggled', 1 );
}

/**
 * Trigger the toggling of hide_sitewide on activity posting.
 *
 * Wrapped here so that we don't have to have a weird function signature
 * for openlab_toggle_hide_sitewide_for_private_membership_activity().
 */
add_action(
	'bp_activity_add',
	function( $r, $activity_id ) {
		$activity = new BP_Activity_Activity( $activity_id );
		if ( 'groups' === $activity->component ) {
			$user_private_memberships = openlab_get_user_private_memberships( $activity->user_id );
			if ( in_array( (int) $activity->item_id, $user_private_memberships, true ) ) {
				openlab_toggle_hide_sitewide_for_private_membership_activity( $activity_id );
			}
		}
	},
	100,
	2
);

/**
 * Filters buddypress-docs queries to exclude private group members.
 *
 * @since 1.6.0
 *
 * @param array         $args       Query arguments.
 * @param BP_Docs_Query $docs_query BP_Docs_Query object.
 * @return array
 */
function openlab_filter_docs_query_to_exclude_private_group_members( $args, $docs_query ) {
	if ( empty( $docs_query->query_args['group_id'] ) ) {
		return $args;
	}

	$group_id = $docs_query->query_args['group_id'];

	if ( bp_current_user_can( 'view_private_members_of_group', $group_id ) ) {
		return $args;
	}

	$private_members = openlab_get_private_members_of_group( $group_id );
	if ( empty( $private_members ) ) {
		return $args;
	}

	$args['author__not_in'] = $private_members;
	return $args;
}
add_filter( 'bp_docs_pre_query_args', 'openlab_filter_docs_query_to_exclude_private_group_members', 10, 2 );

/**
 * Filters 'map_meta_cap' to add support for 'view_private_members_of_group' capability.
 *
 * @since 1.6.0
 *
 * @param array  $caps    Capabilities for meta capability.
 * @param string $cap     Meta capability name.
 * @param int    $user_id User ID.
 * @param array  $args    Arguments for the capability check.
 * @return array
 */
function openlab_filter_map_meta_cap_for_private_group_members( $caps, $cap, $user_id, $args ) {
	if ( 'view_private_members_of_group' !== $cap ) {
		return $caps;
	}

	$group_id = isset( $args[0] ) ? intval( $args[0] ) : bp_get_current_group_id();
	if ( ! $group_id ) {
		return $caps;
	}

	if ( bp_current_user_can( 'bp_moderate' ) || groups_is_user_member( bp_loggedin_user_id(), $group_id ) ) {
		$caps = [ 'exist' ];
	} else {
		$caps = [ 'do_not_allow' ];
	}

	return $caps;
}
add_filter( 'map_meta_cap', 'openlab_filter_map_meta_cap_for_private_group_members', 10, 4 );
