<?php

namespace CBOX\OL\API;

use \WP_Site_Query;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

class Sites extends WP_REST_Controller {
	public function register_routes() {
		$version   = '1';
		$namespace = 'cboxol/v' . $version;

		register_rest_route(
			$namespace,
			'/sites/',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
					'args'                => $this->get_endpoint_args_for_item_schema( true ),
				],
			]
		);
	}

	public function get_items( $request ) {
		$params = $request->get_params();

		$q    = $params['search'];
		$page = $params['page'];

		$per_page = 10;

		$query = new WP_Site_Query(
			[
				'number'        => $per_page,
				'search'        => $q,
				'site__not_in'  => [ cbox_get_main_site_id() ],
				'no_found_rows' => false,
				'offset'        => $per_page * ( $page - 1 ),
			]
		);

		$retval = [
			'results' => [],
		];

		foreach ( $query->sites as $site ) {
			$label = sprintf(
				// translators: 1. Numeric ID of site, 2. Name of site, 3. URL of site
				__( '#%1$s %2$s (%3$s)', 'commons-in-a-box' ),
				$site->blog_id,
				$site->blogname,
				$site->siteurl
			);

			$retval['results'][] = [
				'text' => $label,
				'id'   => $site->blog_id,
			];
		}

		if ( $query->max_num_pages > $page ) {
			$retval['pagination'] = [
				'more' => true,
			];
		}

		return rest_ensure_response( $retval );
	}

	public function get_items_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}
}
