<?php
/**
 * Upgrade nav menus for CBOX-OL.
 *
 * @package cbox-openlab-core
 *
 * @since 1.2.0
 */

namespace CBOX\OL\Upgrades;

use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

/**
 * Upgrade nav menus handler.
 */
class NavMenus extends Upgrade {

	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'upgrade_nav_menus';

	/**
	 * Name used on admin page.
	 *
	 * @var string
	 */
	public $name = 'Group Nav Menus';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_120_nav_menus_migrated';

	/**
	 * Setup method.
	 */
	public function setup() {
		// Sanity check.
		if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'groups' ) ) {
			return;
		}

		$groups = \BP_Groups_Group::get(
			array(
				'per_page'    => null,
				'show_hidden' => true,
				'fields'      => 'ids',
			)
		);

		foreach ( $groups['groups'] as $group_id ) {
			$this->push( new Upgrade_Item( $group_id, array( 'group_id' => $group_id ) ) );
		}
	}

	/**
	 * Process item handler.
	 *
	 * @param CBOX\Upgrades\Upgrade_Item $item Item.
	 */
	public function process( $item ) {
		$group_id = $item->get_value( 'group_id' );
		$site_id  = cboxol_get_group_site_id( $group_id );

		if ( ! $site_id ) {
			return new WP_Error( 'upgrade_skipped', 'Skipped: group has no site.' );
		}

		switch_to_blog( $site_id );

		$locations = get_theme_mod( 'nav_menu_locations' );
		$menu_id   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;

		if ( ! $menu_id ) {
			restore_current_blog();
			return new WP_Error( 'upgrade_skipped', 'Missing primary menu location.' );
		}

		$group      = groups_get_group( $group_id );
		$group_type = cboxol_get_group_group_type( $group_id );

		// Create Group Profile URL.
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'    => '[ ' . $group_type->get_label( 'group_home' ) . ' ]',
				'menu-item-url'      => bp_get_group_permalink( $group ),
				'menu-item-status'   => 'publish',
				'menu-item-position' => -1,
				'menu-item-classes' => 'group-profile-link',
			)
		);

		// Create the Home URL.
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'    => __( 'Home', 'cbox-openlab-core' ),
				'menu-item-url'      => home_url( '/' ),
				'menu-item-status'   => 'publish',
				'menu-item-position' => -1,
				'menu-item-classes' => 'home',
			)
		);

		restore_current_blog();
		return true;
	}

	/**
	 * Mark upgrade as finished.
	 *
	 * @return void
	 */
	public function finish() {
		update_option( static::FLAG, '1' );
	}
}
