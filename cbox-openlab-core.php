<?php
/**
 * Plugin Name:     CBOX-OpenLab Core
 * Plugin URI:      http://commonsinabox.org
 * Description:     Core functionality for CBOX-OpenLab
 * Text Domain:     cbox-openlab-core
 * Domain Path:     /languages
 * Version:         1.7.0
 * Network:         true
 */

define( 'CBOXOL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CBOXOL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CBOXOL_PLUGIN_ROOT_FILE', __FILE__ );
define( 'CBOXOL_PLUGIN_VER', '1.7.0-1754576756485' );
define( 'CBOXOL_ASSET_VER', '1.7.0-1754576756485' );

// @todo Organize this in a meaningful way.
function cboxol_init() {
	// @todo cbox checks
	if ( version_compare( PHP_VERSION, '5.3', '<' ) && current_user_can( 'install_plugins' ) ) {
		add_action( 'admin_notices', 'bhssh_php_admin_notice' );
		return;
	}

	// Abort loading if commons-in-a-box is not active.
	if ( ! function_exists( 'cbox_is_main_site' ) ) {
		return;
	}

	require dirname( __FILE__ ) . '/autoload.php';

	require CBOXOL_PLUGIN_DIR . 'includes/functions.php';
	require CBOXOL_PLUGIN_DIR . 'includes/member-types.php';
	require CBOXOL_PLUGIN_DIR . 'includes/group-categories.php';

	if ( function_exists( 'buddypress' ) && bp_is_active( 'groups' ) ) {
		if ( ! class_exists( 'Bp_Customizable_Group_Categories' ) ) {
			require CBOXOL_PLUGIN_DIR . 'lib/bp-customizable-group-categories/bp-customizable-group-categories.php';
			$bpcgc_plugin = new Bp_Customizable_Group_Categories();
			$bpcgc_plugin->run();
		}
		require CBOXOL_PLUGIN_DIR . 'includes/group-types.php';
		require CBOXOL_PLUGIN_DIR . 'includes/group-sites.php';
		require CBOXOL_PLUGIN_DIR . 'includes/private-group-membership.php';
	}

	require CBOXOL_PLUGIN_DIR . 'includes/brand-settings.php';
	require CBOXOL_PLUGIN_DIR . 'includes/academic-terms.php';
	require CBOXOL_PLUGIN_DIR . 'includes/academic-units.php';
	require CBOXOL_PLUGIN_DIR . 'includes/dashboard-panel.php';
	require CBOXOL_PLUGIN_DIR . 'includes/related-links.php';
	require CBOXOL_PLUGIN_DIR . 'includes/registration.php';
	require CBOXOL_PLUGIN_DIR . 'includes/communication-settings.php';
	require CBOXOL_PLUGIN_DIR . 'includes/profile-fields.php';
	require CBOXOL_PLUGIN_DIR . 'includes/search.php';
	require CBOXOL_PLUGIN_DIR . 'includes/site-template.php';

	// @todo Better loading for these libraries.
	require CBOXOL_PLUGIN_DIR . 'includes/portfolios.php';
	require CBOXOL_PLUGIN_DIR . 'includes/clone.php';

	// Upgrades.
	add_action(
		'cboxol_register_upgrader',
		function() {
			require CBOXOL_PLUGIN_DIR . 'includes/upgrades.php';
		}
	);

	if ( is_admin() ) {
		include CBOXOL_PLUGIN_DIR . 'includes/admin.php';
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		include CBOXOL_PLUGIN_DIR . 'tools/cli.php';
	}

	if ( function_exists( 'bbpress' ) ) {
		require CBOXOL_PLUGIN_DIR . 'plugins/bbpress.php';
	}

	if ( function_exists( 'pressforward' ) ) {
		require CBOXOL_PLUGIN_DIR . 'plugins/pressforward.php';
	}

	require CBOXOL_PLUGIN_DIR . 'includes/network-toolbar.php';

	// Must wait until WP is set up.
	remove_action( 'after_switch_theme', '_wp_sidebars_changed' );
	add_action( 'after_switch_theme', 'cboxol_maybe_install', 200 );
}
add_action( 'plugins_loaded', 'cboxol_init' );
