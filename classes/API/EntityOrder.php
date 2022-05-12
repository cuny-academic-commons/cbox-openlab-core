<?php

namespace CBOX\OL\API;

use \WP_REST_Controller;
use \WP_REST_Server;
use \WP_REST_Request;
use \WP_REST_Response;

class EntityOrder extends WP_REST_Controller {
	public function register_routes() {
		$version   = '1';
		$namespace = 'cboxol/v' . $version;

		register_rest_route(
			$namespace,
			'/entity-order',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'edit_item' ),
					'permission_callback' => array( $this, 'edit_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( true ),
				),
			)
		);
	}

	public function edit_item( $request ) {
		$entity_type = $request->get_param( 'entityType' );
		$ordered_ids = $request->get_param( 'orderedIds' );

		if ( ! $entity_type || ! $ordered_ids ) {
			return rest_ensure_response( [ 'success' => false ] );
		}

		$type_map = [
			'academicTerm' => 'cboxol_acad_term',
		];

		$post_type = isset( $type_map[ $entity_type ] ) ? $type_map[ $entity_type ] : '';
		if ( ! $post_type ) {
			return rest_ensure_response( [ 'success' => false ] );
		}

		$posts = get_posts(
			[
				'post_type'      => $post_type,
				'posts_per_page' => -1,
				'post__in'       => $ordered_ids,
				'orderby'        => 'post__in',
			]
		);

		foreach ( $posts as $index => $post ) {
			if ( $index === $post->menu_order ) {
				continue;
			}

			wp_update_post(
				[
					'ID'         => $post->ID,
					'menu_order' => $index,
				]
			);
		}

		$response = rest_ensure_response( [ 'success' => true ] );

		return $response;
	}

	public function edit_item_permissions_check( $request ) {
		return current_user_can( 'manage_network_options' );
	}
}
