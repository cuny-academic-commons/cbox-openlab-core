<?php

namespace CBOX\OL;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

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
		$class = $this->get_class_for_object_type( $params['objectType'] );
		$type = $class::get_dummy();
		return $this->create_update_helper( $type, $params['typeData'], $params['objectType'] );
	}

	public function update_item( $request ) {
		$params = $request->get_params();
		$class = $this->get_class_for_object_type( $params['objectType'] );

		$wp_post = get_post( $params['id'] );
		$type = $class::get_instance_from_wp_post( $wp_post );

		return $this->create_update_helper( $type, $params['typeData'], $params['objectType'] );
	}

	public function create_update_helper( ItemType $type, $type_data, $object_type ) {
		$old_name = $type->get_name();
		if ( $old_name !== $type_data['name'] ) {
			$type->set_slug( sanitize_title( $type_data['name'] ) );
		}

		$type->set_name( $type_data['name'] );

		foreach ( $type_data['labels'] as $label_type => $label_data ) {
			$type->set_label( $label_type, $label_data['value'] );
		}

		$type->set_order( $type_data['settings']['Order']['data'] );

		if ( 'member' === $object_type ) {
			$type->set_can_create_courses( $type_data['settings']['MayCreateCourses']['data'] );
			$type->set_selectable_types( $type_data['settings']['MayChangeMemberTypeTo']['data']['selectableTypes'] );
		}

		$type->save();

		$retval = $type->get_for_endpoint();
		$response = rest_ensure_response( $retval );
		return $response;
	}

	public function delete_item( $request ) {
		$params = $request->get_params();

		$wp_post = get_post( $params['id'] );
		$type = MemberType::get_instance_from_wp_post( $wp_post );

		if ( ! $type->get_can_be_deleted() ) {
			$data = __( 'Type cannot be deleted', 'cbox-openlab-core' );
			$status = 403;
		} else {
			wp_delete_post( $params['id'] );
			$data = __( 'OK', 'cbox-openlab-core' );
			$status = 200;
		}

		$response = new WP_REST_Response( $data );
		$response->set_status( $status );

		return $response;
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

	protected function get_class_for_object_type( $object_type ) {
		switch ( $object_type ) {
			case 'member' :
				return '\CBOX\OL\MemberType';

			case 'group' :
				return 'CBOX\OL\GroupType';
		}
	}
}
