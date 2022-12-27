<?php

namespace CBOX\OL;

class AcademicTerm {
	protected $data = [
		'labels'     => [],
		'order'      => null,
		'name'       => null,
		'slug'       => null,
		'wp_post_id' => null,
	];

	/**
	 * Save to the database.
	 *
	 * @return bool
	 */
	public function save() {
		$post_id = $this->get_wp_post_id();

		$post_params = [
			'menu_order'  => $this->get_order(),
			'post_type'   => 'cboxol_acad_term',
			'post_title'  => $this->get_name(),
			'post_status' => 'publish',
		];

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
		// Get a fresh copy.
		$post = get_post( $this->get_wp_post_id() );
		if ( $post ) {
			$item = self::get_instance_from_wp_post( $post );
		} else {
			$item = $this;
		}

		$retval = array(
			'name'         => $item->get_name(),
			'slug'         => $item->get_slug(),
			'id'           => $item->get_wp_post_id(),

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

	/**
	 * Calculate the default order for an item.
	 *
	 * The default order should be one more than the existing max.
	 */
	public function calculate_default_order() {
		$existing_terms = cboxol_get_academic_terms();

		// Should already be sorted by order, but we will iterate to be sure.
		$max_order = 0;
		foreach ( $existing_terms as $existing_term ) {
			$term_order = $existing_term->get_order();
			if ( $term_order > $max_order ) {
				$max_order = $term_order;
			}
		}

		$this->set_order( $max_order );
	}
}
