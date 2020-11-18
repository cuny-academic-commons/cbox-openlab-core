<?php
/**
 * Install 'Search Results' page.
 *
 * @package cbox-openlab-core
 *
 * @since 1.2.2
 */

namespace CBOX\OL\Upgrades;

use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

use \WP_Error;

/**
 * Search results page handler.
 */
class SearchResultsPage extends Upgrade {
	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'install_search_results_page';

	/**
	 * Name used on admin page.
	 *
	 * @var string
	 */
	public $name = 'Search Results Page';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_120_install_search_results_page';

	/**
	 * Setup method.
	 */
	public function setup() {
		// Prevent running during activation.
		if ( ! function_exists( 'cboxol_get_main_site_id' ) ) {
			return;
		}

		$this->push( new Upgrade_Item( cboxol_get_main_site_id(), array( 'site_id' => cboxol_get_main_site_id() ) ) );
	}

	/**
	 * Process item handler.
	 *
	 * @param CBOX\Upgrades\Upgrade_Item $item Item.
	 */
	public function process( $item ) {
		$site_id = $item->get_value( 'site_id' );

		switch_to_blog( $site_id );

		$pages = get_site_option( 'cboxol_brand_page_ids' );
		if ( ! isset( $pages['search-results'] ) ) {
			$page_id = wp_insert_post(
				[
					'post_type'    => 'page',
					'post_title'   => 'Search Results',
					'post_content' => '',
					'post_name'    => 'search-results',
					'post_status'  => 'publish',
				]
			);

			if ( $page_id ) {
				update_post_meta( $page_id, '_wp_page_template', 'search-results.php' );

				$pages['search-results'] = $page_id;
				update_site_option( 'cboxol_brand_page_ids', $pages );
			}
		}

		restore_current_blog();
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

