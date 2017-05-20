<?php

/**
 * Miscellaneous functions.
 */

/**
 * Init REST endpoints.
 */
function openlab_rest_api_init() {
	$item_types_endpoint = new \CBOX\OL\API\ItemTypes();
	$item_types_endpoint->register_routes();

	$email_domains_endpoint = new \CBOX\OL\API\EmailDomains();
	$email_domains_endpoint->register_routes();

	$groups_search_endpoint = new \CBOX\OL\API\GroupsSearch();
	$groups_search_endpoint->register_routes();
}
add_action( 'rest_api_init', 'openlab_rest_api_init' );

/**
 * Utility function for getting a default user id when none has been passed to the function
 *
 * The logic is this: If there is a displayed user, return it. If not, check to see whether we're
 * in a members loop; if so, return the current member. If it's still 0, check to see whether
 * we're on a my-* page; if so, return the loggedin user id. Otherwise, return 0.
 *
 * Note that we have to manually check the $members_template variable, because
 * bp_get_member_user_id() doesn't do it properly.
 *
 * @return int
 */
function openlab_fallback_user() {
	global $members_template;

	$user_id = bp_displayed_user_id();

	if ( ! $user_id && ! empty( $members_template ) && isset( $members_template->member ) ) {
		$user_id = bp_get_member_user_id();
	}

	if ( ! $user_id && ( is_page( 'my-courses' ) || is_page( 'my-clubs' ) || is_page( 'my-projects' ) || is_page( 'my-sites' ) ) ) {
		$user_id = bp_loggedin_user_id();
	}

	return (int) $user_id;
}

/**
 * Utility function for getting a default group id when none has been passed to the function
 *
 * The logic is this: If this is a group page, return the current group id. If this is the group
 * creation process, return the new_group_id. If this is a group loop, return the id of the group
 * show during this iteration
 *
 * @return int
 */
function openlab_fallback_group() {
	global $groups_template;

	if ( ! bp_is_active( 'groups' ) ) {
		return 0;
	}

	$group_id = bp_get_current_group_id();

	if ( ! $group_id && bp_is_group_create() ) {
		$group_id = bp_get_new_group_id();
	}

	if ( ! $group_id && ! empty( $groups_template ) && isset( $groups_template->group ) ) {
		$group_id = $groups_template->group->id;
	}

	return (int) $group_id;
}

/**
 * Is this my profile?
 *
 * We need a specialized function that returns true when bp_is_my_profile() does, or in addition,
 * when on a my-* page
 *
 * @return bool
 */
function openlab_is_my_profile() {
	global $bp;

	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( bp_is_my_profile() ) {
		return true;
	}

	// @todo
	if ( is_page( 'my-courses' ) || is_page( 'my-clubs' ) || is_page( 'my-projects' ) || is_page( 'my-sites' ) ) {
		return true;
	}

	if ( bp_is_group_create() ) {
		return true;
	}

	return false;
}

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
 * @param       string $source    Source path
 * @param       string $dest      Destination path
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function cboxol_copyr( $source, $dest ) {
	if ( ! file_exists( $source ) ) {
		return;
	}

	// Check for symlinks
	if ( is_link( $source ) ) {
		return symlink( readlink( $source ), $dest );
	}

	// Simple copy for a file
	if ( is_file( $source ) ) {
		return copy( $source, $dest );
	}

	// Make destination directory
	if ( ! is_dir( $dest ) ) {
		mkdir( $dest );
	}

	// Loop through the folder
	$dir = dir( $source );
	while ( false !== $entry = $dir->read() ) {
		// Skip pointers
		if ( '.' === $entry || '..' === $entry ) {
			continue;
		}

		// Deep copy directories
		cboxol_copyr( "$source/$entry", "$dest/$entry" );
	}

	// Clean up
	$dir->close();
	return true;
}

/** Upgrade/install **********************************************************/

/**
 * Detect whether CBOX-OL must install or upgrade, and run upgrader.
 */
function cboxol_maybe_install() {
	$ver = get_site_option( 'cboxol_ver' );

	if ( ! $ver ) {
		$install = \CBOX\OL\Install::get_instance();
		$install->install();
	} elseif ( version_compare( CBOXOL_PLUGIN_VER, $ver, '>' ) ) {
		$install = \CBOX\OL\Install::get_instance();
		$install->upgrade();
	}

	update_site_option( 'cboxol_ver', CBOXOL_PLUGIN_VER );
}
