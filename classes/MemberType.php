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
	);

	public function get_slug() {
		return $this->data['slug'];
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

	protected static function get_label_types() {
		return array(
			'singular' => array(
				'label' => _x( 'Singular', 'Member Type singular label', 'cbox-openlab-core' ),
				'description' => __( 'Used wherever a specific member\'s Type is mentioned, such as the User Edit interface.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'plural' => array(
				'label' => _x( 'Plural', 'Member Type plural label', 'cbox-openlab-core' ),
				'description' => __( 'Used in directory titles.', 'cbox-openlab-core' ),
				'value' => '',
			),
		);
	}
}
