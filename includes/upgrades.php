<?php
/**
 * Upgrade routine for CBOX-OL
 *
 * @package cbox-openlab-core
 *
 * @since 1.2.0
 */

$registry = CBOX\Upgrades\Upgrade_Registry::get_instance();

// v1.3.0 - Initial setup routine moved to upgrader.
if ( ! get_option( CBOX\OL\Upgrades\InitConfig::FLAG, false ) ) {
	// Legacy check.
	$ver = get_site_option( 'cboxol_ver' );
	if ( ! $ver ) {
		$init_config = new CBOX\OL\Upgrades\InitConfig();
		$registry->register( $init_config->id, $init_config );
	}
}

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
if ( ! get_option( CBOX\OL\Upgrades\GroupTypeSettings120::FLAG, false ) ) {
	$upgrade_group_type_settings = new CBOX\OL\Upgrades\GroupTypeSettings120();
	$registry->register( $upgrade_group_type_settings->id, $upgrade_group_type_settings );
}

// v1.2.2 - Install Search Results page.
if ( ! get_option( CBOX\OL\Upgrades\SearchResultsPage::FLAG, false ) ) {
	$search_results_page = new CBOX\OL\Upgrades\SearchResultsPage();
	$registry->register( $search_results_page->id, $search_results_page );
}

// v1.2.3 - Install Search Results page.
if ( ! get_option( CBOX\OL\Upgrades\GroupTypeSettings123::FLAG, false ) ) {
	$upgrade_group_type_settings_123 = new CBOX\OL\Upgrades\GroupTypeSettings123();
	$registry->register( $upgrade_group_type_settings_123->id, $upgrade_group_type_settings_123 );
}

// v1.3.0 - Install Open + Cloneable badges.
if ( ! get_option( CBOX\OL\Upgrades\OpenCloneableBadges::FLAG, false ) ) {
	$open_cloneable_pages = new CBOX\OL\Upgrades\OpenCloneableBadges();
	$registry->register( $open_cloneable_pages->id, $open_cloneable_pages );
}

// v1.3.0 - Fill 'Creators' data for existing groups.
if ( ! get_option( CBOX\OL\Upgrades\CreatorsData::FLAG, false ) ) {
	$creators_data = new CBOX\OL\Upgrades\CreatorsData();
	$registry->register( $creators_data->id, $creators_data );
}

// v1.3.0 - Add new group type strings.
if ( ! get_option( CBOX\OL\Upgrades\GroupTypeSettings130::FLAG, false ) ) {
	$upgrade_group_type_settings_130 = new CBOX\OL\Upgrades\GroupTypeSettings130();
	$registry->register( $upgrade_group_type_settings_130->id, $upgrade_group_type_settings_130 );
}
