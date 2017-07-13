<?php

namespace CBOX\OL\API;

use CBOX\OL\AcademicUnitType;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

class AcademicUnitTypes extends WP_REST_Controller {
	public function register_routes() {
		$version = '1';
		$namespace = 'cboxol/v' . $version;

		register_rest_route( $namespace, '/academic-unit-type', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );
		/*
		register_rest_route( $namespace, '/group-category/(?P<id>\d+)', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'edit_item' ),
				'permission_callback' => array( $this, 'edit_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
			array(
				'methods'         => WP_REST_Server::DELETABLE,
				'callback'        => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );
		*/
	}

	public function create_item( $request ) {
		$params = $request->get_params();

		$data = $params['typeData'];

		$academic_unit_type = new AcademicUnitType();
		$academic_unit_type->set_group_types( $data['groupTypes'] );
		$academic_unit_type->set_member_types( $data['memberTypes'] );

		// Let WordPress set the slug.
		$name = $data['name'];
		$academic_unit_type->set_name( $name );

		$academic_unit_type->set_parent( $data['parent'] );

		$academic_unit_type->save();

		$retval = $academic_unit_type->get_for_endpoint();
		$response = rest_ensure_response( $retval );
		return $response;
	}

	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}
}
