<?php

namespace CBOX\OL;

class ItemTypeBase {
	protected $post_type = '';

	protected $data = array(
		'slug' => '',
		'name' => '',
		'labels' => array(),
		'can_create_courses' => false,
		'can_be_deleted' => true,
		'selectable_types' => array(),
		'is_enabled' => true,
		'order' => 0,
		'wp_post_id' => 0,
	);

	protected $defaults = array();

	public function __construct() {
		$this->data = array_merge( $this->data, $this->defaults );
	}

	public function get_slug() {
		return $this->data['slug'];
	}

	public function get_name() {
		return $this->data['name'];
	}

	public function get_label( $label_type ) {
		$label = null;
		if ( isset( $this->data['labels'][ $label_type ] ) ) {
			$label = $this->data['labels'][ $label_type ]['value'];
		}

		return $label;
	}

	public function get_labels() {
		return $this->data['labels'];
	}

	public function get_is_enabled() {
		return (bool) $this->data['is_enabled'];
	}

	public function get_wp_post_id() {
		return (int) $this->data['wp_post_id'];
	}

	public function get_order() {
		return (int) $this->data['order'];
	}

	public function get_can_be_deleted() {
		return (bool) $this->data['can_be_deleted'];
	}

	public function set_up_instance_from_wp_post( \WP_Post $post ) {
		$this->set_slug( $post->post_name );
		$this->set_name( $post->post_title );

		// Labels.
		$saved_labels = get_post_meta( $post->ID, 'cboxol_item_type_labels', true );
		if ( empty( $saved_labels ) ) {
			$saved_labels = array();
		}

		foreach ( $this->get_label_types() as $label_type => $label_labels ) {
			if ( isset( $saved_labels[ $label_type ] ) ) {
				$label_labels['value'] = $saved_labels[ $label_type ];
			}

			$this->set_label( $label_type, $label_labels );
		}

		// Enabled.
		$this->set_is_enabled( 'publish' === $post->post_status );

		// Order
		$this->set_order( $post->menu_order );

		// Can be deleted.
		$can_be_deleted_db = get_post_meta( $post->ID, 'cboxol_item_type_is_builtin', true );
		$can_be_deleted = 'yes' !== $can_be_deleted_db;
		$this->set_can_be_deleted( $can_be_deleted );

		// WP post ID.
		$this->set_wp_post_id( $post->ID );
	}

	public function get_for_endpoint() {
		// @todo This doesn't need to go in every one.
		$types = cboxol_get_member_types( array(
			'enabled' => null,
		) );

		$all_types = array_map( function( $type ) {
			return array(
				'slug' => $type->get_slug(),
				'name' => $type->get_name(),
				'id' => $type->get_wp_post_id(),
			);
		}, $types );

		return array(
			'id' => $this->get_wp_post_id(),
			'isCollapsed' => true,
			'isEnabled' => $this->get_is_enabled(),
			'isLoading' => false,
			'isModified' => false,
			'canBeDeleted' => $this->get_can_be_deleted(),
			'settings' => array(
				'MayCreateCourses' => array(
					'component' => 'MayCreateCourses',
					'data' => $this->get_can_create_courses(),
				),
				'MayChangeMemberTypeTo' => array(
					'component' => 'MayChangeMemberTypeTo',
					'data' => array(
						'selectableTypes' => $this->get_selectable_types(),
						'allTypes' => $all_types,
					),
				),
				'Order' => array(
					'component' => 'Order',
					'data' => $this->get_order(),
				),
			),
			'name' => $this->get_name(),
			'slug' => $this->get_slug(),
			'labels' => $this->get_labels(),
		);
	}

	public function save_to_wp_post() {
		// @todo slug?

		$wp_post_id = $this->get_wp_post_id();

		$post_params = array(
			'post_title' => $this->get_name(),
			'menu_order' => $this->get_order(),
		);

		if ( $this->get_is_enabled() ) {
			$post_params['post_status'] = 'publish';
		} else {
			$post_params['post_stauts'] = 'draft';
		}

		if ( $wp_post_id ) {
			$post_params['ID'] = $wp_post_id;
			wp_update_post( $post_params );
		} else {
			$post_params['post_type'] = $this->post_type;
			$wp_post_id = wp_insert_post( $post_params );
			$wp_post = get_post( $wp_post_id );
			$this->set_wp_post_id( $wp_post_id );
			$this->set_slug( $wp_post->post_name );
		}

		$meta_value = array();
		foreach ( $this->get_labels() as $label_type => $label_data ) {
			$meta_value[ $label_type ] = $label_data;
		}
		update_post_meta( $wp_post_id, 'cboxol_item_type_labels', $meta_value );
	}

	public function set_slug( $slug ) {
		$this->data['slug'] = $slug;
	}

	public function set_name( $name ) {
		$this->data['name'] = $name;
	}

	public function set_label( $label_type, $label ) {
		$this->data['labels'][ $label_type ] = $label;
	}

	public function set_is_enabled( $is_enabled ) {
		$this->data['is_enabled'] = (bool) $is_enabled;
	}

	public function set_order( $order ) {
		$this->data['order'] = (int) $order;
	}

	protected function set_wp_post_id( $wp_post_id ) {
		$this->data['wp_post_id'] = (int) $wp_post_id;
	}

	protected function set_can_be_deleted( $can_be_deleted ) {
		$this->data['can_be_deleted'] = (bool) $can_be_deleted;
	}
}
