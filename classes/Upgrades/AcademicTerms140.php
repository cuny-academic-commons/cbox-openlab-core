<?php
/**
 * Migrate legacy Academic Terms to new system.
 *
 * @package cbox-openlab-core
 *
 * @since 1.4.0
 */

namespace CBOX\OL\Upgrades;

use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

use CBOX\OL\AcademicTerm;

/**
 * Record group site's blog_public setting in groupmeta.
 */
class AcademicTerms140 extends Upgrade {

	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'academic_terms_140';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_140_academic_terms_140';

	/**
	 * Setup method.
	 */
	public function setup() {
		$this->name = __( 'Migration of legacy Academic Terms', 'commons-in-a-box' );

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

		$legacy_term = openlab_get_group_term( $group_id );
		if ( ! $legacy_term ) {
			return true;
		}

		// Never use cache here.
		wp_cache_set( 'last_changed', 'microtime', 'terms' );
		$all_terms         = cboxol_get_academic_terms();
		$matching_new_term = null;
		foreach ( $all_terms as $all_term ) {
			if ( $legacy_term === $all_term->get_name() ) {
				$matching_new_term = $all_term->get_wp_post_id();
				break;
			}
		}

		// If no matching term is found, we must create one.
		if ( ! $matching_new_term ) {
			$new_term_object = new AcademicTerm();
			$new_term_object->set_name( $legacy_term );
			$new_term_object->save();

			$matching_new_term = $new_term_object->get_wp_post_id();
		}

		// Something has gone wrong.
		if ( ! $matching_new_term ) {
			return false;
		}

		cboxol_associate_group_with_academic_term( $group_id, $matching_new_term );

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
