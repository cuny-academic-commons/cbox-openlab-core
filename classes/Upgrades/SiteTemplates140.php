<?php
/**
 * Upgrade Group Type settings for 1.4.0
 *
 * Ensures that a Site Template object exists for the legacy group-type site template.
 *
 * @package cbox-openlab-core
 * @since 1.4.0
 */

namespace CBOX\OL\Upgrades;

use CBOX\OL\GroupType;
use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

/**
 * Upgrade Group Type settings.
 */
class SiteTemplates140 extends Upgrade {

	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'site_templates_140';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_140_site_templates_migrated';

	/**
	 * Setup method.
	 */
	public function setup() {
		$this->name = __( 'Group Type Site Templates', 'commons-in-a-box' );

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

		$legacy_site_id = get_post_meta( $type_id, 'cboxol_group_type_template_site_id', true );

		// Ensure that we have a 'General' category.
		// translators: term name
		$term_name = sprintf( __( 'General: %s', 'commons-in-a-box' ), $group_type->get_label( 'plural' ) );

		// @todo It might be easier if group types were associated with template ids
		// rather than site ids, but this will need a migration step

		$general_category = get_term_by( 'name', $term_name, 'cboxol_template_category' );
		if ( ! $general_category ) {
			$inserted = wp_insert_term( $term_name, 'cboxol_template_category' );

			add_term_meta( $inserted['term_id'], 'cboxol_group_type', $group_type->get_slug() );

			$general_category = get_term_by( 'id', $inserted['term_id'], 'cboxol_template_category' );
		}

		if ( ! $general_category || ! ( $general_category instanceof \WP_Term ) ) {
			return false;
		}

		// If there's already a template with this site ID, do nothing.
		// This should never happen unless the install process is run late.
		$existing_templates = $group_type->get_site_templates();
		foreach ( $existing_templates as $existing_template ) {
			if ( $legacy_site_id === $existing_template['siteId'] ) {
				return false;
			}
		}

		$template_id = wp_insert_post(
			[
				'post_type'   => 'cboxol_site_template',
				'post_title'  => get_blog_option( $legacy_site_id, 'blogname' ),
				'post_status' => 'publish',
			]
		);

		if ( ! $template_id || is_wp_error( $template_id ) ) {
			return false;
		}

		update_post_meta( $template_id, '_template_site_id', $legacy_site_id );
		update_post_meta( $type_id, 'cboxol_group_type_site_template_id', $template_id );

		wp_set_post_terms( $template_id, [ $general_category->term_id ], 'cboxol_template_category' );

		// Lazy technique.
		$default_category_id = cboxol_get_default_site_template_category_id();
		if ( ! $default_category_id ) {
			$inserted = wp_insert_term( _x( 'General', 'Name for general site template category', 'commons-in-a-box' ), 'cboxol_template_category' );

			if ( is_wp_error( $inserted ) ) {
				return;
			}

			foreach ( cboxol_get_group_types() as $group_type ) {
				add_term_meta( $inserted['term_id'], 'cboxol_group_type', $group_type->get_slug() );
			}

			update_option( 'cboxol_default_site_template_category', $inserted['term_id'] );
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

	/**
	 * Get new labels for Groyp Type.
	 *
	 * @param string $slug
	 * @return array
	 */
	public function get_labels( $slug ) {
		$labels = [
			'portfolio' => [
				'add_to_portfolio'      => __( 'Add to Portfolio', 'commons-in-a-box' ),
				'added_to_my_portfolio' => __( 'Added to My Portfolio', 'commons-in-a-box' ),
			],
		];

		return isset( $labels[ $slug ] ) ? $labels[ $slug ] : null;
	}
}
