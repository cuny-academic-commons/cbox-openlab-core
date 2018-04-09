<?php

namespace CBOX\OL\API;

use CBOX\OL\SignupCode;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;
use \BP_Groups_Group;

class RegistrationFormSettings extends WP_REST_Controller {
	public function register_routes() {
		$version = '1';
		$namespace = 'cboxol/v' . $version;

		register_rest_route( $namespace, '/registration-form-settings', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'edit_item' ),
				'permission_callback' => array( $this, 'edit_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );
	}

	public function edit_item( $request ) {
		$params = $request->get_params();

		update_site_option( 'cboxol_registration_form_settings', $params['settings'] );

		$response = rest_ensure_response( $params['settings'] );

		return $response;
	}

	public function edit_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}
}
