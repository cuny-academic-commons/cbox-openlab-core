<?php

namespace CBOX\OL;

class AcademicUnit {
	protected $data = array(
		'count'      => 0,
		'order'      => 0,
		'name'       => null,
		'parent'     => null,
		'slug'       => null,
		'type'       => null,
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
			'post_type'   => 'cboxol_acadunit',
			'post_title'  => $this->get_name(),
			'post_status' => 'publish',
			'menu_order'  => $this->get_order(),
		);

		if ( $post_id ) {
			$post_params['ID'] = $post_id;
			$updated           = wp_update_post( $post_params );

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

			$post = get_post( $post_id );
			$this->set_slug( $post->post_name );
		}

		// @todo validate?
		update_post_meta( $post_id, 'cboxol_academic_unit_type', $this->get_type() );
		update_post_meta( $post_id, 'cboxol_academic_unit_parent', $this->get_parent() );

		return true;
	}

	/**
	 * Get count.
	 *
	 * @return string
	 */
	public function get_count() {
		return (int) $this->data['count'];
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
	 * Get order.
	 *
	 * @return int
	 */
	public function get_order() {
		return (int) $this->data['order'];
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
	 * Get type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->data['type'];
	}

	/**
	 * Get post ID.
	 *
	 * @return int
	 */
	public function get_wp_post_id() {
		return (int) $this->data['wp_post_id'];
	}

	public function get_for_endpoint() {
		$retval = array(
			'count'      => $this->get_count(),
			'id'         => $this->get_wp_post_id(),
			'name'       => $this->get_name(),
			'order'      => $this->get_order(),
			'parent'     => $this->get_parent(),
			'slug'       => $this->get_slug(),
			'type'       => $this->get_type(),

			'isLoading'  => false,
			'isModified' => false,
		);

		return $retval;
	}

	public static function get_instance_from_wp_post( \WP_Post $post ) {
		$type = new self();

		$type->set_wp_post_id( $post->ID );
		$type->set_name( $post->post_title );
		$type->set_slug( $post->post_name );
		$type->set_order( $post->menu_order );

		$parent = get_post_meta( $post->ID, 'cboxol_academic_unit_parent', true );
		$type->set_parent( $parent );

		$acad_unit_type = get_post_meta( $post->ID, 'cboxol_academic_unit_type', true );
		$type->set_type( $acad_unit_type );

		return $type;
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
	 * Set order.
	 *
	 * @param int
	 */
	public function set_order( $order ) {
		$this->data['order'] = $order;
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
	 * Set slug.
	 *
	 * @param slug
	 */
	public function set_slug( $slug ) {
		$this->data['slug'] = $slug;
	}

	/**
	 * Set type.
	 *
	 * @param slug
	 */
	public function set_type( $type ) {
		$this->data['type'] = $type;
	}

	/**
	 * Set WP post ID.
	 *
	 * @param int
	 */
	public function set_wp_post_id( $wp_post_id ) {
		$this->data['wp_post_id'] = $wp_post_id;
	}
}
