<?php

namespace CBOX\OL\API;

use CBOX\OL\AcademicTerm;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;
use \WP_Error;

class AcademicTerms extends WP_REST_Controller {
	public function register_routes() {
		$version   = '1';
		$namespace = 'cboxol/v' . $version;

		register_rest_route(
			$namespace,
			'/academic-term',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( true ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/academic-term/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( true ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( true ),
				),
			)
		);
	}

	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	public function create_item( $request ) {
		$params = $request->get_params();
		$data   = $params['typeData'];

		$academic_term = new AcademicTerm();

		return $this->create_update_helper( $academic_term, $data );
	}

	public function update_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	public function update_item( $request ) {
		$params = $request->get_params();
		$data   = $params['typeData'];
		$id     = $params['id'];

		$academic_term = new AcademicTerm();

		$post = get_post( $id );
		if ( ! $post || 'cboxol_acad_term' !== $post->post_type ) {
			return new WP_Error( 'no_academic_term_found', __( 'No academic term found', 'commons-in-a-box' ) );
		}

		$academic_term = AcademicTerm::get_instance_from_wp_post( $post );

		return $this->create_update_helper( $academic_term, $data );
	}

	protected function create_update_helper( AcademicTerm $academic_term, $data ) {
		$academic_term->set_name( $data['name'] );

		$academic_term->save();

		$retval   = $academic_term->get_for_endpoint();
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
			$data   = __( 'OK', 'commons-in-a-box' );
			$status = 200;
		} else {
			$data   = __( 'Cannot delete academic term.', 'commons-in-a-box' );
			$status = 403;
		}

		$response = new WP_REST_Response( $data );
		$response->set_status( $status );

		return $response;
	}
}
