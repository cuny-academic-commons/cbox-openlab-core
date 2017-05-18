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

		register_rest_route( $namespace, '/email-domain/(?P<existing>[^/]+)', array(
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );
	}

	public function create_item( $request ) {
		$params = $request->get_params();
		$domain = $params['domain'];

		$limited_email_domains = get_site_option( 'limited_email_domains' );
		$old_led = $limited_email_domains;

		if ( ! is_array( $limited_email_domains ) ) {
			$limited_email_domains = explode( "\n", $limited_email_domains );
		}

		$limited_email_domains[] = trim( $domain );

		$limited_email_domains = array_unique( $limited_email_domains );

		update_site_option( 'limited_email_domains', $limited_email_domains );

		// Verify that it was saved.
		$new_led = get_site_option( 'limited_email_domains' );

		$success = ( $new_led !== $old_led ) && in_array( $domain, $new_led, true );

		$response = rest_ensure_response( $domain );

		if ( ! $success ) {
			$response->set_status( 500 );
		}

		return $response;
	}

	public function update_item( $request ) {
		$params = $request->get_params();

		$limited_email_domains = get_site_option( 'limited_email_domains' );
		$old_led = $limited_email_domains;

		_b( $params );
		return;

		$limited_email_domains = get_site_option( 'limited_email_domains' );
		$old_led = $limited_email_domains;

		if ( ! is_array( $limited_email_domains ) ) {
			$limited_email_domains = explode( "\n", $limited_email_domains );
		}

		$limited_email_domains[] = trim( $params['domain'] );

		$limited_email_domains = array_unique( $limited_email_domains );

		update_site_option( 'limited_email_domains', $limited_email_domains );

		// Verify that it was saved.
		$new_led = get_site_option( 'limited_email_domains' );

		$success = ( $new_led !== $old_led ) && in_array( $params['domain'], $new_led, true );

		$response = rest_ensure_response( $new_led );
		if ( ! $success ) {
			$response->set_status( 500 );
		}

		return $response;
	}

	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	public function update_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}
}
