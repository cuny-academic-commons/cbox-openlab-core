<?php
/**
 * Record group site's blog_public setting in groupmeta.
 *
 * @package cbox-openlab-core
 *
 * @since 1.2.0
 */

namespace CBOX\OL\Upgrades;

use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

/**
 * Record group site's blog_public setting in groupmeta.
 */
class GroupSiteBlogPublic extends Upgrade {

	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'group_site_blog_public';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_120_blog_public_migrated';

	/**
	 * Setup method.
	 */
	public function setup() {
		$this->name = __( '"Open" setting for group sites', 'commons-in-a-box' );

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
		$group    = groups_get_group( $group_id );

		cboxol_sync_group_blog_public_on_group_save( $group );

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
