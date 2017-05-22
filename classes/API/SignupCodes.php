<?php

namespace CBOX\OL\API;

use CBOX\OL\SignupCode;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;
use \BP_Groups_Group;

class SignupCodes extends WP_REST_Controller {
	public function register_routes() {
		$version = '1';
		$namespace = 'cboxol/v' . $version;

		register_rest_route( $namespace, '/signup-code', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );

		register_rest_route( $namespace, '/signup-code/(?P<domain>\d+)', array(
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

		$signup_code = new SignupCode();
		$signup_code->set_code( $params['newSignupCode'] );
		$signup_code->set_member_type( $params['newMemberType'] );

		$group_id = BP_Groups_Group::get_id_from_slug( $params['newGroup'] );
		$signup_code->set_group_id( $group_id );

		if ( ! $signup_code->save() ) {
			return new WP_Error( 'signup_code_save_failure', __( 'Could not create signup code.', 'cbox-openlab-core' ) );
		}

		$response = rest_ensure_response( $signup_code->get_for_endpoint() );

		return $response;
	}

	public function delete_item( $request ) {
		$params = $request->get_params();

//		$response = rest_ensure_response( $domain );

		if ( ! $success ) {
			$response->set_status( 500 );
		}

		return $response;
	}

	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	public function delete_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}
}
