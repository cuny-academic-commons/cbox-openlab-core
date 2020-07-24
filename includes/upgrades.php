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

// v1.2.0 - Save blog_public to groupmeta.
if ( ! get_option( CBOX\OL\Upgrades\GroupSiteBlogPublic::FLAG, false ) ) {
	$upgrade_blog_public = new CBOX\OL\Upgrades\GroupSiteBlogPublic();
	$registry->register( $upgrade_blog_public->id, $upgrade_blog_public );
}

// v1.2.0 - Upgrade group type settings.
if ( ! get_option( CBOX\OL\Upgrades\GroupTypeSettings::FLAG, false ) ) {
	$upgrade_group_type_settings = new CBOX\OL\Upgrades\GroupTypeSettings();
	$registry->register( $upgrade_group_type_settings->id, $upgrade_group_type_settings );
}
