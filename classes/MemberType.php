<?php

namespace CBOX\OL;

class MemberType extends ItemTypeBase implements ItemType {
	protected $post_type = 'cboxol_member_type';

	protected $defaults = array(
		'can_create_courses' => false,
		'can_be_deleted' => true,
		'selectable_types' => array(),
	);

	protected $boolean_props = array(
		'can_create_courses',
	);

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

	public static function get_instance_from_wp_post( \WP_Post $post ) {
		$type = new self();
		$type->set_up_instance_from_wp_post( $post );

		// Can create courses.
		$can_create_courses_db = get_post_meta( $post->ID, 'cboxol_member_type_can_create_courses', true );
		$can_create_courses = 'yes' === $can_create_courses_db;
		$type->set_can_create_courses( $can_create_courses );

		// Selectable types ("Member may change Type to...").
		$selectable_types_db = get_post_meta( $post->ID, 'cboxol_member_type_selectable_types', true );
		$type->set_selectable_types( $selectable_types_db );

		return $type;
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

	public function save() {
		$this->save_to_wp_post();

		$wp_post_id = $this->get_wp_post_id();

		update_post_meta( $wp_post_id, 'cboxol_member_type_selectable_types', $this->get_selectable_types() );

		delete_post_meta( $wp_post_id, 'cboxol_member_type_can_create_courses' );
		if ( $this->get_can_create_courses() ) {
			add_post_meta( $wp_post_id, 'cboxol_member_type_can_create_courses', 'yes' );
		}
	}

	public function set_selectable_types( $types ) {
		if ( ! is_array( $types ) ) {
			$types = array();
		}

		$this->data['selectable_types'] = $types;
	}

	public static function get_dummy() {
		$dummy = new self();

		foreach ( $dummy->get_label_types() as $label_type => $label_labels ) {
			$label_labels['value'] = '';
			$dummy->set_label( $label_type, $label_labels );
		}

		return $dummy;
	}

	public function get_label_types() {
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
