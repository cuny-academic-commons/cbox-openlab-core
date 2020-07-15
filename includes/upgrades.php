<?php
/**
 * Upgrade routine for CBOX-OL
 *
 * @package cbox-openlab-core
 *
 * @since 1.2.0
 */

$registry = CBOX\Upgrades\Upgrade_Registry::get_instance();

// v1.2.0 - Upgrade nav menus.
if ( ! get_option( CBOX\OL\Upgrades\NavMenus::FLAG, false ) ) {
	$upgrade_nav_menus = new CBOX\OL\Upgrades\NavMenus();
	$registry->register( $upgrade_nav_menus->id, $upgrade_nav_menus );
}

// Add more upgrades...
