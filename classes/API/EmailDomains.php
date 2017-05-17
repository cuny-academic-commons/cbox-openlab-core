<?php

namespace CBOX\OL\API;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

class EmailDomains extends WP_REST_Controller {
	public function register_routes() {
		$version = '1';
		$namespace = 'cboxol/v' . $version;

		register_rest_route( $namespace, '/email-domain', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );
	}

	public function create_item( $request ) {
		$params = $request->get_params();
		_b( $params );
//		$class = $this->get_class_for_object_type( $params['objectType'] );
//		$type = $class::get_dummy();
//		return $this->create_update_helper( $type, $params['typeData'], $params['objectType'] );
	}

	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}
}
