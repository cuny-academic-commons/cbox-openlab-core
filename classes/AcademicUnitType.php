<?php

namespace CBOX\OL;

class AcademicUnitType {
	protected $data = array(
		'group_types' => array(),
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
		return $this->data['name'];
	}

	/**
	 * Get parent.
	 *
	 * @return string
	 */
	public function get_parent() {
		return $this->data['parent'];
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

			// @todo
			'units' => array(
				array(
					'slug' => 'foo',
					'label' => 'Foo',
				),
				array(
					'slug' => 'bar',
					'label' => 'Bar',
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
	public function set_order( int $order ) {
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
	 * Set WP post ID.
	 *
	 * @param int
	 */
	public function set_wp_post_id( int $wp_post_id ) {
		$this->data['wp_post_id'] = $wp_post_id;
	}
}
