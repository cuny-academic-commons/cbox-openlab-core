<?php

namespace CBOX\OL;

class AcademicUnitType {
	protected $data = array(
		'group_types' => array(),
		'labels' => array(),
		'member_types' => array(),
		'order' => null,
		'name' => null,
		'parent' => null,
		'slug' => null,
		'wp_post_id' => null,
	);

	/**
	 * Save to the database.
	 *
	 * @return bool
	 */
	public function save() {
		$post_id = $this->get_wp_post_id();

		$post_params = array(
			'menu_order' => $this->get_order(),
			'post_type' => 'cboxol_acadunit_type',
			'post_title' => $this->get_name(),
			'post_status' => 'publish',
		);

		if ( $post_id ) {
			$post_params['ID'] = $post_id;
			$updated = wp_update_post( $post_params );

			if ( is_wp_error( $updated ) ) {
				return $updated;
			}
		} else {
			$created = wp_insert_post( $post_params );

			if ( is_wp_error( $created ) ) {
				return $created;
			}

			$post_id = (int) $created;
			$this->set_wp_post_id( $post_id );
		}

		// @todo validate?
		$meta_value = array();
		foreach ( $this->get_labels() as $label_type => $label_data ) {
			// A total mess. Prevents double saving of an array.
			if ( is_array( $label_data ) && isset( $label_data['value'] ) ) {
				$label_data = $label_data['value'];
			}
			$meta_value[ $label_type ] = $label_data;
		}
		update_post_meta( $post_id, 'cboxol_item_type_labels', $meta_value );

		update_post_meta( $post_id, 'cboxol_associated_member_types', $this->get_member_types() );
		update_post_meta( $post_id, 'cboxol_associated_group_types', $this->get_group_types() );
		update_post_meta( $post_id, 'cboxol_academic_unit_type_parent', $this->get_parent() );

		return true;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name() {
		return (string) $this->data['name'];
	}

	/**
	 * Get parent.
	 *
	 * @return string
	 */
	public function get_parent() {
		return (string) $this->data['parent'];
	}

	/**
	 * Get slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->data['slug'];
	}

	/**
	 * Get slug.
	 *
	 * @return int
	 */
	public function get_order() {
		return (int) $this->data['order'];
	}

	/**
	 * Get group types.
	 *
	 * @return array
	 */
	public function get_group_types() {
		return $this->data['group_types'];
	}

	/**
	 * Get member types.
	 *
	 * @return array
	 */
	public function get_member_types() {
		return $this->data['member_types'];
	}

	/**
	 * Get a specific label.
	 *
	 * @param string $label_type
	 * @return string|null Null on failure.
	 */
	public function get_label( $label_type ) {
		$label = null;
		if ( isset( $this->data['labels'][ $label_type ] ) ) {
			$label = $this->data['labels'][ $label_type ]['value'];
		}

		return $label;
	}

	/**
	 * Get all labels for this unit type.
	 *
	 * @return array
	 */
	public function get_labels() {
		return $this->data['labels'];
	}

	/**
	 * Get post ID.
	 *
	 * @return int
	 */
	public function get_wp_post_id() {
		return (int) $this->data['wp_post_id'];
	}

	public static function get_dummy() {
		return new self();
	}

	public static function get_instance_from_wp_post( \WP_Post $post ) {
		$type = new self();

		$type->set_wp_post_id( $post->ID );
		$type->set_name( $post->post_title );
		$type->set_slug( $post->post_name );
		$type->set_order( $post->menu_order );

		// Labels.
		$saved_labels = get_post_meta( $post->ID, 'cboxol_item_type_labels', true );
		if ( empty( $saved_labels ) ) {
			$saved_labels = array();
		}

		foreach ( $type->get_label_types() as $label_type => $label_labels ) {
			if ( isset( $saved_labels[ $label_type ] ) ) {
				$label_labels['value'] = $saved_labels[ $label_type ];
			}

			$type->set_label( $label_type, $label_labels );
		}

		$parent = get_post_meta( $post->ID, 'cboxol_academic_unit_type_parent', true );
		$type->set_parent( $parent );

		$group_types = get_post_meta( $post->ID, 'cboxol_associated_group_types', true );
		$type->set_group_types( $group_types );

		$member_types = get_post_meta( $post->ID, 'cboxol_associated_member_types', true );
		$type->set_member_types( $member_types );

		return $type;
	}

	public function get_for_endpoint() {
		$retval = array(
			'groupTypes' => array(),
			'labels' => $this->get_labels(),
			'memberTypes' => array(),
			'name' => $this->get_name(),
			'parent' => $this->get_parent(),
			'slug' => $this->get_slug(),
			'settings' => array(
				'Order' => array(
					'component' => 'Order',
					'data' => $this->get_order(),
				),
			),

			'id' => $this->get_wp_post_id(),

			'canBeDeleted' => true,
			'isCollapsed' => true,
			'isEnabled' => true,
			'isLoading' => false,
			'isModified' => false,
		);

		$group_types = $this->get_group_types();
		$retval['groupTypes'] = $group_types;

		$member_types = $this->get_member_types();
		$retval['memberTypes'] = $member_types;

		return $retval;
	}

	/**
	 * Set name.
	 *
	 * @param string
	 */
	public function set_name( $name ) {
		$this->data['name'] = $name;
	}

	/**
	 * Set parent ID.
	 *
	 * @param string
	 */
	public function set_parent( $parent ) {
		$this->data['parent'] = $parent;
	}

	/**
	 * Set order.
	 *
	 * @param int
	 */
	public function set_order( $order ) {
		$this->data['order'] = $order;
	}

	/**
	 * Set slug.
	 *
	 * @param slug
	 */
	public function set_slug( $slug ) {
		$this->data['slug'] = $slug;
	}

	/**
	 * Set group types.
	 *
	 * @param array
	 */
	public function set_group_types( $group_types ) {
		$this->data['group_types'] = $group_types;
	}

	/**
	 * Set member types.
	 *
	 * @param array
	 */
	public function set_member_types( $member_types ) {
		$this->data['member_types'] = $member_types;
	}

	/**
	 * Set label.
	 *
	 * @param string $label_type
	 * @param string $label
	 */
	public function set_label( $label_type, $label ) {
		$this->data['labels'][ $label_type ] = $label;
	}

	/**
	 * Set WP post ID.
	 *
	 * @param int
	 */
	public function set_wp_post_id( $wp_post_id ) {
		$this->data['wp_post_id'] = $wp_post_id;
	}

	/**
	 * Is the current type required for members of a given type?
	 *
	 * @param string $member_type_slug
	 * @return bool
	 */
	public function is_required_for_member_type( $member_type_slug ) {
		$type_settings = $this->get_member_types();
		return isset( $type_settings[ $member_type_slug ] ) && 'required' === $type_settings[ $member_type_slug ];
	}

	/**
	 * Is the current type required for groups of a given type?
	 *
	 * @param string $group_type_slug
	 * @return bool
	 */
	public function is_required_for_group_type( $group_type_slug ) {
		$type_settings = $this->get_group_types();
		return isset( $type_settings[ $group_type_slug ] ) && 'required' === $type_settings[ $group_type_slug ];
	}

	/**
	 * Is the current type optional for members of a given type?
	 *
	 * @param string $member_type_slug
	 * @return bool
	 */
	public function is_optional_for_member_type( $member_type_slug ) {
		$type_settings = $this->get_member_types();
		return isset( $type_settings[ $member_type_slug ] ) && 'optional' === $type_settings[ $member_type_slug ];
	}

	/**
	 * Is the current type optional for groups of a given type?
	 *
	 * @param string $group_type_slug
	 * @return bool
	 */
	public function is_optional_for_group_type( $group_type_slug ) {
		$type_settings = $this->get_group_types();
		return isset( $type_settings[ $group_type_slug ] ) && 'optional' === $type_settings[ $group_type_slug ];
	}

	/**
	 * Is the current type selectable by a member of a given type?
	 *
	 * Returns true for cases of 'required' or 'optional'.
	 *
	 * @param string $member_type_slug
	 */
	public function is_selectable_by_member_type( $member_type_slug ) {
		$type_settings = $this->get_member_types();
		return isset( $type_settings[ $member_type_slug ] ) && in_array( $type_settings[ $member_type_slug ], array( 'required', 'optional' ), true );
	}

	/**
	 * Is the current type selectable by a group of a given type?
	 *
	 * Returns true for cases of 'required' or 'optional'.
	 *
	 * @param string $member_type_slug
	 */
	public function is_selectable_by_group_type( $group_type_slug ) {
		$type_settings = $this->get_group_types();
		return isset( $type_settings[ $group_type_slug ] ) && in_array( $type_settings[ $group_type_slug ], array( 'required', 'optional' ), true );
	}

	/**
	 * Get label types.
	 *
	 * @return array
	 */
	public static function get_label_types() {
		return array(
			'plural' => array(
				'slug' => 'plural',
				'label' => _x( 'Plural', 'Academic Unit Type plural label', 'cbox-openlab-core' ),
				'description' => __( 'Used in directory titles.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'singular' => array(
				'slug' => 'singular',
				'label' => _x( 'Singular', 'Academic Unit Type singular label', 'cbox-openlab-core' ),
				'description' => __( 'Used on group and member profiles.', 'cbox-openlab-core' ),
				'value' => '',
			),
		);
	}
}
