<?php
/**
 * Upgrade Group Type settings for 1.6.0
 *
 * Adds new labels.
 *
 * @package cbox-openlab-core
 * @since 1.6.0
 */

namespace CBOX\OL\Upgrades;

use CBOX\OL\GroupType;
use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

/**
 * Upgrade Group Type settings.
 */
class GroupTypeSettings160 extends Upgrade {

	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'group_type_settings_160';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_160_group_type_settings_updated';

	/**
	 * Setup method.
	 */
	public function setup() {
		$this->name = __( 'Group Type Settings', 'commons-in-a-box' );

		// Sanity check.
		if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'groups' ) ) {
			return;
		}

		$type_ids = get_posts(
			[
				'post_type'      => 'cboxol_group_type',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'fields'         => 'ids',
			]
		);

		foreach ( $type_ids as $type_id ) {
			$this->push( new Upgrade_Item( $type_id, array( 'type_id' => $type_id ) ) );
		}
	}

	/**
	 * Process item handler.
	 *
	 * @param CBOX\Upgrades\Upgrade_Item $item Item.
	 */
	public function process( $item ) {
		$type_id    = $item->get_value( 'type_id' );
		$type_post  = get_post( $type_id );
		$group_type = GroupType::get_instance_from_wp_post( $type_post );

		$labels = $this->get_labels( $group_type->get_slug() );

		// Mark process successful if we don't have labels to update.
		if ( ! $labels ) {
			return true;
		}

		foreach ( $labels as $label_type => $label_data ) {
			$group_type->set_label( $label_type, $label_data );
		}

		// Save updates.
		$group_type->save();

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

	/**
	 * Get new labels for Groyp Type.
	 *
	 * @param string $slug
	 * @return array
	 */
	public function get_labels( $slug ) {
		$labels = [
			'portfolio' => [
				// We are no longer suggesting a portfolio name, so we need a new string.
				'name_help_text' => __( 'Choose a name for your Portfolio. You may use your name ("Jane Smith\'s Portfolio"), or any descriptive title you\'d like.', 'commons-in-a-box' ),
			],
		];

		return isset( $labels[ $slug ] ) ? $labels[ $slug ] : null;
	}
}
