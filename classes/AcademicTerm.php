<?php

namespace CBOX\OL;

class AcademicTerm {
	protected $data = array(
		'labels'       => array(),
		'order'        => null,
		'name'         => null,
		'slug'         => null,
		'wp_post_id'   => null,
	);

	/**
	 * Save to the database.
	 *
	 * @return bool
	 */
	public function save() {
		$post_id = $this->get_wp_post_id();

		$post_params = array(
			'menu_order'  => $this->get_order(),
			'post_type'   => 'cboxol_acad_term',
			'post_title'  => $this->get_name(),
			'post_status' => 'publish',
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
		}

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

		return $type;
	}

	public function get_for_endpoint() {
		$retval = array(
			'name'         => $this->get_name(),
			'slug'         => $this->get_slug(),
			'settings'     => array(
				'Order' => array(
					'component' => 'Order',
					'data'      => $this->get_order(),
				),
			),

			'id'           => $this->get_wp_post_id(),

			'canBeDeleted' => true,
			'isCollapsed'  => true,
			'isEnabled'    => true,
			'isLoading'    => false,
			'isModified'   => false,
		);

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
	 * Set WP post ID.
	 *
	 * @param int
	 */
	public function set_wp_post_id( $wp_post_id ) {
		$this->data['wp_post_id'] = $wp_post_id;
	}
}