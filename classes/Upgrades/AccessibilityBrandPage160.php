<?php
/**
 * Install 'Accessibility' brand page.
 *
 * @package cbox-openlab-core
 *
 * @since 1.6.0
 */

namespace CBOX\OL\Upgrades;

use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

use CBOX\OL\Install;

use \WP_Error;

/**
 * Accessibility brand page handler.
 */
class AccessibilityBrandPage160 extends Upgrade {
	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'install_accessibility_brand_page_160';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_160_install_accessibility_brand_page';

	/**
	 * Setup method.
	 */
	public function setup() {
		// Prevent running during activation.
		if ( ! function_exists( 'cboxol_get_main_site_id' ) ) {
			return;
		}

		$this->name = __( 'Install Accessibility Page', 'commons-in-a-box' );

		$this->push( new Upgrade_Item( cboxol_get_main_site_id(), array( 'site_id' => cboxol_get_main_site_id() ) ) );
	}

	/**
	 * Process item handler.
	 *
	 * @param CBOX\Upgrades\Upgrade_Item $item Item.
	 */
	public function process( $item ) {
		_b( $item );
		$site_id = $item->get_value( 'site_id' );

		switch_to_blog( $site_id );

		$pages = get_site_option( 'cboxol_brand_page_ids' );
		if ( ! isset( $pages['accessibility'] ) ) {
			$parent = isset( $pages['about'] ) ? $pages['about'] : 0;

			$page_id = wp_insert_post(
				[
					'post_type'    => 'page',
					'post_title'   => __( 'Accessibility', 'commons-in-a-box' ),
					'post_content' => cboxol_get_default_accessibility_brand_page_content(),
					'post_name'    => 'accessibility',
					'post_status'  => 'publish',
					'post_parent'  => $parent,
				]
			);

			$pages['accessibility'] = $page_id;
			update_site_option( 'cboxol_brand_page_ids', $pages );

			// Add to the 'About' nav menu.
			$menu_name = __( 'About Menu', 'commons-in-a-box' );
			$menu_obj  = wp_get_nav_menu_object( $menu_name );
			if ( $menu_obj ) {
				$menu_id = $menu_obj->term_id;

				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'   => __( 'Accessibility', 'commons-in-a-box' ),
						'menu-item-classes' => 'about accessibility',
						'menu-item-url'     => get_permalink( $page_id ),
						'menu-item-status'  => 'publish',
					)
				);
			}
		}

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

