<?php

/**
 * Miscellaneous functions.
 */

/**
 * Gets a version string for use in asset enqueuing.
 *
 * @return string
 */
function cboxol_get_asset_version() {
	return apply_filters( 'cboxol_get_asset_version', CBOXOL_ASSET_VER );
}

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

	$signup_codes_endpoint = new \CBOX\OL\API\SignupCodes();
	$signup_codes_endpoint->register_routes();

	$registration_form_settings_endpoint = new \CBOX\OL\API\RegistrationFormSettings();
	$registration_form_settings_endpoint->register_routes();

	$group_categories_endpoint = new \CBOX\OL\API\GroupCategories();
	$group_categories_endpoint->register_routes();

	$academic_unit_types_endpoint = new \CBOX\OL\API\AcademicUnitTypes();
	$academic_unit_types_endpoint->register_routes();

	$academic_units_endpoint = new \CBOX\OL\API\AcademicUnits();
	$academic_units_endpoint->register_routes();
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
	if ( get_option( 'cboxol_installing' ) ) {
		return;
	}

	if ( ! bp_is_active( 'groups' ) ) {
		return;
	}

	if ( ! function_exists( 'openlab_core_setup' ) ) {
		return;
	}

	$ver = get_site_option( 'cboxol_ver' );

	if ( ! $ver ) {
		$install = \CBOX\OL\Install::get_instance();
		update_option( 'cboxol_installing', 1 );
		$install->install();
	} elseif ( version_compare( CBOXOL_PLUGIN_VER, $ver, '>' ) ) {
		update_option( 'cboxol_installing', 1 );
		$install = \CBOX\OL\Install::get_instance();
		$install->upgrade();
	}

	delete_option( 'cboxol_installing' );
	update_site_option( 'cboxol_ver', CBOXOL_PLUGIN_VER );
}

// Hack - disable BuddyPress Docs attachments.
add_filter( 'bp_docs_enable_attachments', '__return_false' );

/**
 * Force all BP components to be enabled.
 */
function cboxol_force_bp_components( $components ) {
	$core_components = array(
		'xprofile',
		'settings',
		'friends',
		'messages',
		'activity',
		'notifications',
		'groups',
		'blogs',
		'members',
	);

	foreach ( $core_components as $component ) {
		$components[ $component ] = 1;
	}

	return $components;
}
add_action( 'bp_active_components', 'cboxol_force_bp_components' );
add_action( 'pre_update_option_bp-active-components', 'cboxol_force_bp_components' );

/**
 * Wrapper for cbox_get_main_site_id().
 *
 * @since 1.1.1
 */
function cboxol_get_main_site_id() {
	if ( function_exists( 'cbox_get_main_site_id' ) ) {
		return cbox_get_main_site_id();
	} else {
		return (int) get_current_site()->blog_id;
	}
}
