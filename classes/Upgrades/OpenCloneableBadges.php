<?php
/**
 * Install Open and Cloneable badges in 1.3.0.
 *
 * @package cbox-openlab-core
 * @since 1.3.0
 */

namespace CBOX\OL\Upgrades;

use OpenLab\Badges\Badge;

use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

/**
 * Upgrade Group Type settings.
 */
class OpenCloneableBadges extends Upgrade {

	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'open_cloneable_badges';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_130_open_cloneable_badges';

	/**
	 * Setup method.
	 */
	public function setup() {
		$this->name = __( 'Open and Cloneable Badges', 'commons-in-a-box' );

		// Sanity check.
		if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'groups' ) ) {
			return;
		}

		if ( ! defined( 'OLBADGES_VERSION' ) ) {
			return;
		}

		$this->push( new Upgrade_Item( cboxol_get_main_site_id(), [] ) );
	}

	/**
	 * Process item handler.
	 *
	 * @param CBOX\Upgrades\Upgrade_Item $item Item.
	 */
	public function process( $item ) {
		// Open and Cloneable go first, so we bump all other badges up two positions.
		$existing_badges = Badge::get();
		foreach ( $existing_badges as $existing_badge ) {
			$position = $existing_badge->get_position();

			$existing_badge->set_position( $position + 2 );
			$existing_badge->save();
		}

		$group_types = array_map(
			function( $group_type ) {
				return $group_type->get_slug();
			},
			cboxol_get_group_types()
		);

		$cloneable_badge = new Badge();
		$cloneable_badge->set_name( _x( 'Cloneable', 'Cloneable badge name', 'commons-in-a-box' ) );
		$cloneable_badge->set_short_name( _x( 'Cloneable', 'Cloneable badge short name', 'commons-in-a-box' ) );
		$cloneable_badge->set_link( '' );
		$cloneable_badge->set_position( 1 );
		$cloneable_badge->set_group_types( $group_types );
		$cloneable_badge->set_can_be_deleted( false );
		$cloneable_badge->set_can_be_granted( false );
		$cloneable_badge->save();

		update_term_meta( $cloneable_badge->get_id(), 'cboxol_is_cloneable_badge', 1 );

		$open_badge = new Badge();
		$open_badge->set_name( _x( 'Open', 'Open badge name', 'commons-in-a-box' ) );
		$open_badge->set_short_name( _x( 'Open', 'Open badge short name', 'commons-in-a-box' ) );
		$open_badge->set_link( '' );
		$open_badge->set_position( 0 );
		$open_badge->set_group_types( $group_types );
		$open_badge->set_can_be_deleted( false );
		$open_badge->set_can_be_granted( false );
		$open_badge->save();

		update_term_meta( $open_badge->get_id(), 'cboxol_is_open_badge', 1 );

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
