<?php
/**
 * Plugin Name:     CBOX-OpenLab Core
 * Plugin URI:      http://commonsinabox.org
 * Description:     Core functionality for CBOX-OpenLab
 * Text Domain:     cbox-openlab-core
 * Domain Path:     /languages
 * Version:         0.1.0
 * Network:         true
 */

define( 'CBOXOL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CBOXOL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// @todo Organize this in a meaningful way.
function cboxol_init() {
	// @todo cbox checks
	if ( version_compare( PHP_VERSION, '5.3', '<' ) && current_user_can( 'install_plugins' ) ) {
		add_action( 'admin_notices', 'bhssh_php_admin_notice' );
		return;
	}

	require dirname( __FILE__ ) . '/autoload.php';

	require CBOXOL_PLUGIN_DIR . 'includes/member-types.php';

	if ( is_admin() ) {
		include CBOXOL_PLUGIN_DIR . 'includes/admin.php';
	}
}
add_action( 'plugins_loaded', 'cboxol_init' );
