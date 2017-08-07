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

		register_rest_route( $namespace, '/academic-unit-type/(?P<id>\d+)', array(
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

	public function create_item( $request ) {
		$params = $request->get_params();
		$data = $params['typeData'];
		$academic_unit_type = new AcademicUnitType();

		return $this->create_update_helper( $academic_unit_type, $data );
	}

	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	public function update_item( $request ) {
		$params = $request->get_params();
		$data = $params['typeData'];
		$id = $params['id'];

		$post = get_post( $id );
		if ( ! $post || 'cboxol_acadunit_type' !== $post->post_type ) {
			return new WP_Error( 'no_academic_unit_type_found', __( 'No academic unit type found', 'cbox-openlab-core' ) );
		}

		$academic_unit_type = AcademicUnitType::get_instance_from_wp_post( $post );

		return $this->create_update_helper( $academic_unit_type, $data );
	}

	public function delete_item( $request ) {
		$params = $request->get_params();

		$deleted = wp_delete_post( $params['id'] );

		if ( $deleted ) {
			$data = __( 'OK', 'cbox-openlab-core' );
			$status = 200;
		} else {
			$data = __( 'Cannot delete type.', 'cbox-openlab-core' );
			$status = 403;
		}

		$response = new WP_REST_Response( $data );
		$response->set_status( $status );

		return $response;
	}

	public function update_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	public function delete_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	protected function create_update_helper( AcademicUnitType $academic_unit_type, $data ) {
		$academic_unit_type->set_group_types( $data['groupTypes'] );
		$academic_unit_type->set_member_types( $data['memberTypes'] );

		// Let WordPress set the slug.
		$name = $data['name'];
		$academic_unit_type->set_name( $name );

		$academic_unit_type->set_parent( $data['parent'] );
		$academic_unit_type->set_order( $data['settings']['Order']['data'] );

		$academic_unit_type->save();

		$retval = $academic_unit_type->get_for_endpoint();
		$response = rest_ensure_response( $retval );
		return $response;
	}
}
