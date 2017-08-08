<?php

namespace CBOX\OL\API;

use CBOX\OL\AcademicUnit;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

class AcademicUnits extends WP_REST_Controller {
	public function register_routes() {
		$version = '1';
		$namespace = 'cboxol/v' . $version;

		register_rest_route( $namespace, '/academic-unit', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );

		register_rest_route( $namespace, '/academic-unit/(?P<id>\d+)', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
			array(
				'methods'         => WP_REST_Server::DELETABLE,
				'callback'        => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );
	}

	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	public function create_item( $request ) {
		$params = $request->get_params();
		$data = $params['typeData'];

		$academic_unit = new AcademicUnit();

		return $this->create_update_helper( $academic_unit, $data );
	}

	protected function create_update_helper( AcademicUnit $academic_unit, $data ) {
		$academic_unit->set_name( $data['name'] );
		$academic_unit->set_parent( $data['parent'] );
		$academic_unit->set_type( $data['type'] );

		$academic_unit->save();

		$retval = $academic_unit->get_for_endpoint();
		$response = rest_ensure_response( $retval );
		return $response;
	}

	public function delete_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	public function delete_item( $request ) {
		$params = $request->get_params();

		$deleted = wp_delete_post( $params['id'] );

		if ( $deleted ) {
			$data = __( 'OK', 'cbox-openlab-core' );
			$status = 200;
		} else {
			$data = __( 'Cannot delete academic unit.', 'cbox-openlab-core' );
			$status = 403;
		}

		$response = new WP_REST_Response( $data );
		$response->set_status( $status );

		return $response;
	}
}
