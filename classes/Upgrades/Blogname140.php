<?php
/**
 * Mirror 'blogname' in blogmeta for existing sites.
 *
 * @package cbox-openlab-core
 *
 * @since 1.4.0
 */

namespace CBOX\OL\Upgrades;

use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

/**
 * Record each site's 'blogname' in blogmeta.
 */
class Blogname140 extends Upgrade {

	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'blogname_140';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_140_blogname_140';

	/**
	 * Setup method.
	 */
	public function setup() {
		global $wpdb;

		$this->name = __( 'Mirroring site names for improved search', 'commons-in-a-box' );

		// No way to get this raw value in API?
		$site_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
		_b( $site_ids );

		foreach ( $site_ids as $site_id ) {
			$this->push( new Upgrade_Item( $site_id, array( 'site_id' => $site_id ) ) );
		}
	}

	/**
	 * Process item handler.
	 *
	 * @param CBOX\Upgrades\Upgrade_Item $item Item.
	 */
	public function process( $item ) {
		$site_id = $item->get_value( 'site_id' );

		$blogname = get_blog_option( $site_id, 'blogname' );

		update_site_meta( $site_id, 'blogname', $blogname );

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
