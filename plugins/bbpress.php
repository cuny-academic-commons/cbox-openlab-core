<?php

/**
 * bbPress-specific mods.
 */

/**
 * Don't show a bbPress step during group creation.
 */
function openlab_remove_forum_step_from_group_creation() {
	$gcs = buddypress()->groups->group_creation_steps;
	if ( isset( $gcs['forum'] ) ) {
		unset( $gcs['forum'] );
	}
	buddypress()->groups->group_creation_steps = $gcs;
}
add_action( 'bp_actions', 'openlab_remove_forum_step_from_group_creation', 9 );

/**
 * Create bbPress 2.x forum for newly created groups.
 */
function openlab_create_forum_on_group_creation( $group_id, $member, $group ) {
	// Set the default forum status
	switch ( $group->status ) {
		case 'hidden':
			$status = bbp_get_hidden_status_id();
			break;
		case 'private':
			$status = bbp_get_private_status_id();
			break;
		case 'public':
		default:
			$status = bbp_get_public_status_id();
			break;
	}
	// Create the initial forum
	$forum_id = bbp_insert_forum(
		array(
			'post_parent'  => bbp_get_group_forums_root_id(),
			'post_title'   => $group->name,
			'post_content' => $group->description,
			'post_status'  => $status,
		)
	);
	bbp_add_forum_id_to_group( $group_id, $forum_id );
	bbp_add_group_id_to_forum( $forum_id, $group_id );

	// Update forum active
	groups_update_groupmeta( $group_id, '_bbp_forum_enabled_' . $forum_id, true );

	// Set forum enabled status
	$group->enable_forum = 1;

	// Save the group
	$group->save();
	bbp_repair_forum_visibility();
}
add_action( 'groups_create_group', 'openlab_create_forum_on_group_creation', 10, 3 );

/**
 * Force group forums to be active.
 *
 * This is redundant but for some reason bbPress requires it.
 */
add_filter( 'bp_get_new_group_enable_forum', '__return_true' );

/**
 * Blogs must be public in order for BP to record their activity. Only at save time
 */
add_filter( 'bp_is_blog_public', '__return_true' );
