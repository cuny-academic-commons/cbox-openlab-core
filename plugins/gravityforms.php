<?php

/**
 * Gravity Forms-specific mods.
 */

/**
 * Keeps CBOX-OL's global admin styles from being stripped by Gravity Forms's "No Conflict Mode".
 *
 * When the 'gform_enable_noconflict' option is enabled, GFForms::no_conflict_mode_style() rebuilds
 * $wp_styles->queue *and* $wp_styles->registered on GF admin pages to only contain a hardcoded
 * allow-list of WP core and Gravity Forms handles. Any style not on that list - including the ones
 * registered by cboxol_enqueue_global_styles() (the network toolbar/header styles that get loaded
 * on every wp-admin page) - is silently unregistered, so it never gets close to being printed.
 *
 * Registering our handles with the 'gform_noconflict_styles' filter is a no-op when No Conflict
 * Mode is disabled (the default), since GFForms::no_conflict_mode_style() bails out before ever
 * calling this filter in that case.
 *
 * @param array $styles Style handles that Gravity Forms's No Conflict Mode should not strip out.
 * @return array
 */
function cboxol_gform_noconflict_styles( $styles ) {
	$cboxol_styles = array(
		'google-open-sans',
		'font-awesome',
		'cboxol-font-awesome',
		'admin-bar-custom',
		'openlab-toolbar',
		'openlab-toolbar-legacy',
	);

	return array_merge( $styles, $cboxol_styles );
}
add_filter( 'gform_noconflict_styles', 'cboxol_gform_noconflict_styles' );
