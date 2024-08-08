<?php
/**
 * Create a new table for private group memberships.
 *
 * @package cbox-openlab-core
 * @since 1.6.0
 */

namespace CBOX\OL\Upgrades;

use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

/**
 * Create a new table for private group memberships.
 */
class MembershipPrivacy160 extends Upgrade {

	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'membership_privacy_160';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_membership_privacy_160';

	/**
	 * Setup method.
	 */
	public function setup() {
		$this->name = __( 'Group Membership Privacy', 'commons-in-a-box' );
		$this->push( new Upgrade_Item( 'create-membership-privacy-table' ) );
	}

	/**
	 * Process item handler.
	 *
	 * @param CBOX\Upgrades\Upgrade_Item $item Item.
	 */
	public function process( $item ) {
		openlab_create_private_membership_table();
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
