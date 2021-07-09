<?php
/**
 * Initialization of site configuration.
 *
 * @package cbox-openlab-core
 *
 * @since 1.3.0
 */

namespace CBOX\OL\Upgrades;

use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

/**
 * Install initial configuration.
 */
class InitConfig extends Upgrade {

	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'init_config';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_init_config';

	/**
	 * Setup method.
	 */
	public function setup() {
		$this->name = __( 'Initial Setup', 'commons-in-a-box' );

		$this->push( new Upgrade_Item( 'member_types' ) );
		$this->push( new Upgrade_Item( 'group_types' ) );
		$this->push( new Upgrade_Item( 'group_categories' ) );
		$this->push( new Upgrade_Item( 'academic_types' ) );
		$this->push( new Upgrade_Item( 'brand_pages' ) );
		$this->push( new Upgrade_Item( 'search' ) );
		$this->push( new Upgrade_Item( 'settings' ) );
		$this->push( new Upgrade_Item( 'widgets' ) );
		$this->push( new Upgrade_Item( 'nav_menus' ) );
		$this->push( new Upgrade_Item( 'slides' ) );
		$this->push( new Upgrade_Item( 'footer' ) );
	}

	/**
	 * Process item handler.
	 *
	 * @param CBOX\Upgrades\Upgrade_Item $item Item.
	 */
	public function process( $item ) {
		$install = \CBOX\OL\Install::get_instance();

		switch ( $item->id ) {
			case 'member_types' :
				$install->install_default_member_types();
			break;

			case 'group_types' :
				$install->install_default_group_types();
			break;

			case 'group_categories' :
				$install->install_default_group_categories();
			break;

			case 'academic_types' :
				$install->install_default_academic_types();
			break;

			case 'brand_pages' :
				$install->install_default_brand_pages();
			break;

			case 'search' :
				$install->install_default_search();
			break;

			case 'settings' :
				$install->install_default_settings();
			break;

			case 'widgets' :
				$install->install_default_widgets();
			break;

			case 'nav_menus' :
				$install->install_default_nav_menus();
			break;

			case 'default_slides' :
				$install->install_default_slides();
			break;

			case 'default_footer' :
				$install->install_default_footer();
			break;
		}

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
