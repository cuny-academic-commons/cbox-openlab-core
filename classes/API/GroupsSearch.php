<?php

namespace CBOX\OL\API;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

class GroupsSearch extends WP_REST_Controller {
	public function register_routes() {
		$version = '1';
		$namespace = 'cboxol/v' . $version;

		register_rest_route( $namespace, '/groups-search', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );
	}

	public function get_items( $request ) {
		$params = $request->get_params();
		$q = $params['q'];

		$found = groups_get_groups( array(
			'max' => 5,
			'search_terms' => $q,
		) );

		$retval = array();
		foreach ( $found['groups'] as $group ) {
			$retval[] = array(
				'label' => $group->name,
				'value' => $group->slug,
			);
		}

		return rest_ensure_response( $retval );
	}

	public function get_items_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}
}
