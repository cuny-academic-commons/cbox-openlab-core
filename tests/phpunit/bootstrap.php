<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Cbox_Openlab_Core
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

if ( ! defined( 'BP_TESTS_DIR' ) ) {
	$_bp_tests_dir = getenv( 'BP_TESTS_DIR' );
	if ( $_bp_tests_dir ) {
		define( 'BP_TESTS_DIR', $_bp_tests_dir );
	} else {
		define( 'BP_TESTS_DIR', dirname( __FILE__ ) . '/../../buddypress/tests/phpunit' );
	}
}

if ( ! file_exists( BP_TESTS_DIR . '/bootstrap.php' ) ) {
	return;
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require BP_TESTS_DIR . '/includes/loader.php';
	require dirname( dirname( __FILE__ ) ) . '/cbox-openlab-core.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require BP_TESTS_DIR . '/includes/testcase.php';
