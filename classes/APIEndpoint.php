<?php

namespace CBOX\OL;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;

class APIEndpoint extends WP_REST_Controller {
	public function register_routes() {
		$version = '1';
		$namespace = 'cboxol/v' . $version;

		register_rest_route( $namespace, '/item-type', array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );

		register_rest_route( $namespace, '/item-type/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'            => array(
					'context'          => array(
						'default'      => 'view',
					),
				),
			),
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( false ),
			),
			array(
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'     => array(
					'force'    => array(
						'default'      => false,
					),
				),
			),
		) );

		register_rest_route( $namespace, '/item-type/schema', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array( $this, 'get_public_item_schema' ),
		) );
	}

	public function create_item( $request ) {
		$params = $request->get_params();

		$type = MemberType::get_dummy();

		return $this->create_update_helper( $type, $params );
	}

	public function update_item( $request ) {
		$params = $request->get_params();

		$wp_post = get_post( $params['id'] );
		$type = MemberType::get_instance_from_wp_post( $wp_post );

		return $this->create_update_helper( $type, $params );
	}

	public function create_update_helper( MemberType $type, $params ) {
		$type->set_name( $params['name'] );

		foreach ( $params['labels'] as $label_type => $label_data ) {
			$type->set_label( $label_type, $label_data['value'] );
		}

		$type->set_can_create_courses( $params['settings']['MayCreateCourses']['data'] );
		$type->set_selectable_types( $params['settings']['MayChangeMemberTypeTo']['data']['selectableTypes'] );

		$type->set_order( $params['settings']['Order']['data'] );

		$type->save();

		$retval = array();
		$response = rest_ensure_response( $retval );
	}

	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	public function update_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	public function get_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}

	public function delete_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}
}
