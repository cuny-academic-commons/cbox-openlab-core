<?php

namespace CBOX\OL;

class MemberType {
	protected $data = array(
		'slug' => '',
		'name' => '',
		'description' => '',
		'labels' => array(),
		'can_create_courses' => false,
		'selectable_types' => array(),
		'is_enabled' => true,
		'order' => 0,
		'wp_post_id' => 0,
	);

	public function get_slug() {
		return $this->data['slug'];
	}

	public function get_name() {
		return $this->data['name'];
	}

	public function get_description() {
		return $this->data['description'];
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

	public function get_can_create_courses() {
		return (bool) $this->data['can_create_courses'];
	}

	public function get_selectable_types() {
		// @todo Should validate types here (can't do on setup because it will trigger a loop).
		return $this->data['selectable_types'];
	}

	/**
	 * Get a human-readable, comma-separated list of labels for this type's selectable types.
	 *
	 * @return string
	 */
	public function get_selectable_types_list() {
		$list = '';

		$selectable_types = $this->get_selectable_types();
		$labels = array();
		foreach ( $selectable_types as $selectable_type ) {
			$selectable_type_obj = cboxol_get_member_type( $selectable_type );
			if ( $selectable_type_obj ) {
				$labels[] = $selectable_type_obj->get_name();
			}
		}

		if ( $labels ) {
			$list = implode( ', ', $labels );
		}

		return $list;
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

	public static function get_instance_from_wp_post( \WP_Post $post ) {
		$type = new self();

		$type->set_slug( $post->post_name );
		$type->set_name( $post->post_title );
		$type->set_description( $post->post_content );

		// Labels.
		$saved_labels = get_post_meta( $post->ID, 'cboxol_member_type_labels', true );
		if ( empty( $saved_labels ) ) {
			$saved_labels = array();
		}

		foreach ( self::get_label_types() as $label_type => $label_labels ) {
			if ( isset( $saved_labels[ $label_type ] ) ) {
				$label_labels['value'] = $saved_labels[ $label_type ];
			}

			$type->set_label( $label_type, $label_labels );
		}

		// Can create courses.
		$can_create_courses_db = get_post_meta( $post->ID, 'cboxol_member_type_can_create_courses', true );
		$can_create_courses = 'yes' === $can_create_courses_db;
		$type->set_can_create_courses( $can_create_courses );

		// Selectable types ("Member may change Type to...").
		$selectable_types_db = get_post_meta( $post->ID, 'cboxol_member_type_selectable_types', true );
		$type->set_selectable_types( $selectable_types_db );

		// Enabled.
		$type->set_is_enabled( 'publish' === $post->post_status );

		// Order
		$type->set_order( $post->menu_order );

		// WP post ID.
		$type->set_wp_post_id( $post->ID );

		return $type;
	}

	protected function set_slug( $slug ) {
		$this->data['slug'] = $slug;
	}

	protected function set_name( $name ) {
		$this->data['name'] = $name;
	}

	protected function set_description( $description ) {
		$this->data['description'] = $description;
	}

	protected function set_label( $label_type, $label ) {
		$this->data['labels'][ $label_type ] = $label;
	}

	protected function set_can_create_courses( $can ) {
		$this->data['can_create_courses'] = (bool) $can;
	}

	protected function set_selectable_types( $types ) {
		if ( ! is_array( $types ) ) {
			$types = array();
		}

		$this->data['selectable_types'] = $types;
	}

	protected function set_is_enabled( $is_enabled ) {
		$this->data['is_enabled'] = (bool) $is_enabled;
	}

	protected function set_order( $order ) {
		$this->data['order'] = (int) $order;
	}

	protected function set_wp_post_id( $wp_post_id ) {
		$this->data['wp_post_id'] = (int) $wp_post_id;
	}

	protected static function get_label_types() {
		return array(
			'singular' => array(
				'slug' => 'singular',
				'label' => _x( 'Singular', 'Member Type singular label', 'cbox-openlab-core' ),
				'description' => __( 'Used wherever a specific member\'s Type is mentioned, such as the User Edit interface.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'plural' => array(
				'slug' => 'plural',
				'label' => _x( 'Plural', 'Member Type plural label', 'cbox-openlab-core' ),
				'description' => __( 'Used in directory titles.', 'cbox-openlab-core' ),
				'value' => '',
			),
		);
	}
}
