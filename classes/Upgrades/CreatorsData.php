<?php
/**
 * Adds 'Creators' data for existing groups.
 *
 * @package cbox-openlab-core
 *
 * @since 1.3.0
 */

namespace CBOX\OL\Upgrades;

use CBOX\OL\GroupType;
use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

/**
 * Add Creators data for existing groups.
 */
class CreatorsData extends Upgrade {

	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'creators_data';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_130_creators_data';

	/**
	 * Setup method.
	 */
	public function setup() {
		$this->name = __( 'Group Creators', 'commons-in-a-box' );

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

		$existing = openlab_get_group_creators( $group_id );
		if ( $existing ) {
			return;
		}

		$contacts = cboxol_get_all_group_contact_ids( $group_id );

		$group      = groups_get_group( $group_id );
		$contacts[] = $group->creator_id;

		$contacts = array_unique( $contacts );

		$creators = array_map(
			function( $user_id ) {
				$user = get_user_by( 'ID', $user_id );

				if ( $user ) {
					return [
						'type'         => 'member',
						'member-login' => $user->user_nicename,
					];
				}
			},
			$contacts
		);

		openlab_save_group_creators( $group_id, $creators );

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
