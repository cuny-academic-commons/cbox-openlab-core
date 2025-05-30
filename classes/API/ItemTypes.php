<?php

namespace CBOX\OL\API;

use \CBOX\OL\ItemType;
use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

class ItemTypes extends WP_REST_Controller {
	public function register_routes() {
		$version   = '1';
		$namespace = 'cboxol/v' . $version;

		register_rest_route(
			$namespace,
			'/item-type',
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
			'/item-type/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'context' => array(
							'default' => 'view',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( false ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => array(
						'force' => array(
							'default' => false,
						),
					),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/item-type/schema',
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	public function create_item( $request ) {
		$params = $request->get_params();
		$class  = $this->get_class_for_object_type( $params['objectType'] );
		$type   = $class::get_dummy();
		return $this->create_update_helper( $type, $params['typeData'], $params['objectType'] );
	}

	public function update_item( $request ) {
		$params = $request->get_params();
		$class  = $this->get_class_for_object_type( $params['objectType'] );

		$wp_post = get_post( $params['id'] );
		$type    = $class::get_instance_from_wp_post( $wp_post );

		return $this->create_update_helper( $type, $params['typeData'], $params['objectType'] );
	}

	public function create_update_helper( ItemType $type, $type_data, $object_type ) {
		// Slugs should be immutable.
		$old_name = $type->get_name();
		$type->set_name( $type_data['name'] );

		foreach ( $type_data['labels'] as $label_type => $label_data ) {
			$type->set_label( $label_type, $label_data['value'] );
		}

		$type->set_order( $type_data['settings']['Order']['data'] );
		$type->set_is_enabled( $type_data['isEnabled'] );

		if ( 'member' === $object_type ) {
			$type->set_can_create_courses( $type_data['settings']['MayCreateCourses']['data'] );
			$type->set_can_import_group_users( $type_data['settings']['MayImportGroupUsers']['data'] );
			$type->set_selectable_types( $type_data['settings']['MayChangeMemberTypeTo']['data']['selectableTypes'] );
		}

		if ( 'group' === $object_type ) {
			$type->set_site_template_id( $type_data['siteTemplateId'] );
			$type->set_available_privacy_options( $type_data['availablePrivacyOptions'] );
			$type->set_available_site_privacy_options( $type_data['availableSitePrivacyOptions'] );
			$type->set_default_privacy_option( $type_data['defaultPrivacyOption'] );
			$type->set_default_site_privacy_option( $type_data['defaultSitePrivacyOption'] );
		}

		$type->save();

		$retval   = $type->get_for_endpoint();
		$response = rest_ensure_response( $retval );
		return $response;
	}

	public function delete_item( $request ) {
		$params = $request->get_params();

		$wp_post = get_post( $params['id'] );
		if ( ! $wp_post ) {
			return new WP_Error( 'no_item_type_found', __( 'No item type found by that ID.', 'commons-in-a-box' ) );
		}

		$class = $this->get_class_for_object_type( $wp_post->post_type );
		$type  = $class::get_instance_from_wp_post( $wp_post );

		if ( ! $type->get_can_be_deleted() ) {
			$data   = __( 'Type cannot be deleted', 'commons-in-a-box' );
			$status = 403;
		} else {
			wp_delete_post( $params['id'] );
			$data   = __( 'OK', 'commons-in-a-box' );
			$status = 200;
		}

		$response = new WP_REST_Response( $data );
		$response->set_status( $status );

		return $response;
	}

	public function create_item_permissions_check( $request ) {
		$params = $request->get_params();

		if ( ! isset( $params['objectType'] ) || ! in_array( $params['objectType'], array( 'group', 'member' ), true ) ) {
			return false;
		}

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
			case 'member':
			case 'cboxol_member_type':
				return '\CBOX\OL\MemberType';

			case 'group':
			case 'cboxol_group_type':
				return 'CBOX\OL\GroupType';
		}
	}
}
