<?php

namespace CBOX\OL\API;

use CBOX\OL\GroupCategory;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

class GroupCategories extends WP_REST_Controller {
	public function register_routes() {
		$version = '1';
		$namespace = 'cboxol/v' . $version;

		register_rest_route( $namespace, '/group-category', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );

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
	}

	public function create_item( $request ) {
		$params = $request->get_params();

		$data = $params['typeData'];

		$group_category = new GroupCategory();
		$group_category->set_group_types( $data['groupTypes'] );

		// Let WordPress set the slug.
		$name = $data['name'];
		$group_category->set_name( $name );

		$group_category->set_order( $data['settings']['Order']['data'] );

		$group_category->save();

		$retval = $group_category->get_for_endpoint();
		$response = rest_ensure_response( $retval );
		return $response;
	}

	public function edit_item( $request ) {
		$params = $request->get_params();

		$post = get_post( $params['id'] );
		$signup_code = \CBOX\OL\SignupCode::get_instance_from_wp_post( $post );

		$signup_code = $this->create_edit_helper( $signup_code, $params );

		$response = rest_ensure_response( $signup_code->get_for_endpoint() );

		return $response;
	}

	protected function create_edit_helper( \CBOX\OL\SignupCode $signup_code, $params ) {
		$signup_code->set_code( $params['newSignupCode'] );
		$signup_code->set_member_type( $params['newMemberType'] );

		$group_id = BP_Groups_Group::get_id_from_slug( $params['newGroup'] );
		$signup_code->set_group_id( $group_id );

		$signup_code->set_author_id( bp_loggedin_user_id() );

		$signup_code->save();

		return $signup_code;
	}

	public function delete_item( $request ) {
		$params = $request->get_params();
		$wp_post_id = $params['id'];

		$success = (bool) wp_delete_post( $wp_post_id );

		$response = rest_ensure_response( $success );

		if ( ! $success ) {
			$response->set_status( 500 );
		}

		return $response;
	}

	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	public function edit_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	public function delete_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}
}
