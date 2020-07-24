<?php
/**
 * Upgrade Group Type settings.
 * Adds new labels and enables shared cloning.
 *
 * @package cbox-openlab-core
 *
 * @since 1.2.0
 */

namespace CBOX\OL\Upgrades;

use CBOX\OL\GroupType;
use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

/**
 * Upgrade Group Type settings.
 */
class GroupTypeSettings120 extends Upgrade {

	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'group_type_settings';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_120_group_type_settings_migrated';

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

		// Enable cloning.
		if ( ! $group_type->get_is_portfolio() ) {
			$group_type->set_can_be_cloned( true );
		}

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
			'course'  => [
				'settings_help_text_sharing' => __( 'This setting enables other faculty to clone your Course. If enabled, other faculty can reuse, remix, transform, and build upon the material in this course. Attribution to original Course authors will be included.', 'commons-in-a-box' ),
			],
			'project' => [
				'clone_help_text'            => __( 'Note: Cloning copies the project home, site set-up, and all documents, files, discussions and posts you\'ve created. Posts will be set to "draft" mode. The clone will not copy membership or member-created documents, files, discussions, comments or posts.', 'commons-in-a-box' ),
				'settings_help_text_sharing' => __( 'This setting enables other members to clone your Project. If enabled, other members can reuse, remix, transform, and build upon the material in this project. Attribution to original Project authors will be included.', 'commons-in-a-box' ),
			],
			'club'    => [
				'clone_help_text'            => __( 'Note: Cloning copies the club home, site set-up, and all documents, files, discussions and posts you\'ve created. Posts will be set to "draft" mode. The clone will not copy membership or member-created documents, files, discussions, comments or posts.', 'commons-in-a-box' ),
				'settings_help_text_sharing' => __( 'This setting enables other members to clone your Club. If enabled, other members can reuse, remix, transform, and build upon the material in this club. Attribution to original Club authors will be included.', 'commons-in-a-box' ),
			],
		];

		return isset( $labels[ $slug ] ) ? $labels[ $slug ] : null;
	}
}
