<?php

namespace CBOX\OL;

class GroupCategory {
	protected $data = array(
		'group_types' => array(),
		'name' => null,
		'order' => null,
		'slug' => null,
		'wp_term_id' => null,
	);

	/**
	 * Save to the database.
	 *
	 * @return bool
	 */
	public function save() {
		$created = wp_insert_term( $this->get_name(), 'bp_group_categories' );

		if ( is_wp_error( $created ) ) {
			return $created;
		}

		$term = get_term( $created['term_id'], 'bp_group_categories' );
		update_term_meta( $term->term_id, 'cboxol_order', $this->get_order() );

		// Must delete existing group type associations first.
		$meta = get_term_meta( $term->term_id );
		$group_types = array();
		foreach ( $meta as $meta_key => $_ ) {
			if ( 0 === strpos( $meta_key, 'bpcgc_group_' ) ) {
				delete_term_meta( $term->term_id, $meta_key );
			}
		}

		foreach ( $this->get_group_types() as $group_type ) {
			add_term_meta( $term->term_id, 'bpcgc_group_' . $group_type->get_slug(), 1 );
		}

		$this->set_wp_term_id( $term->term_id );

		return true;
	}

	public function get_for_endpoint() {
		$retval = array(
			'groupTypes' => array(),
			'name' => $this->get_name(),
			'slug' => $this->get_slug(),
			'settings' => array(
				'Order' => array(
					'component' => 'Order',
					'data' => $this->get_order(),
				),
			),
			'wpTermId' => $this->get_wp_term_id(),

			'canBeDeleted' => true,
			'isCollapsed' => true,
			'isEnabled' => true,
			'isLoading' => false,
			'isModified' => false,
		);

		$group_types = $this->get_group_types();
		foreach ( $group_types as $group_type ) {
			$retval['groupTypes'][] = $group_type->get_slug();
		}

		return $retval;
	}

	public static function get_instance_from_wp_term( \WP_Term $term ) {
		$cat = new self();

		$cat->set_wp_term_id( $term->term_id );
		$cat->set_name( $term->name );
		$cat->set_slug( $term->slug );

		$order = get_term_meta( $term->term_id, 'cboxol_order', true );
		if ( $order ) {
			$cat->set_order( $term->order );
		}

		// Yikes - this is the way the data is stored by the plugin.
		$meta = get_term_meta( $term->term_id );

		$group_types = array();
		foreach ( $meta as $meta_key => $_ ) {
			if ( 0 === strpos( $meta_key, 'bpcgc_group_' ) ) {
				$group_types[] = substr( $meta_key, 12 );
			}
		}
		$cat->set_group_types( $group_types );

		return $cat;
	}

	public static function get_dummy() {
		return new self();
	}

	/**
	 * Get group types.
	 *
	 * @return array
	 */
	public function get_group_types() {
		$group_types = array();
		foreach ( $this->data['group_types'] as $gt ) {
			$group_type = cboxol_get_group_type( $gt );
			if ( ! is_wp_error( $group_type ) ) {
				$group_types[ $gt ] = $group_type;
			}
		}

		return $group_types;
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
	 * Get slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->data['slug'];
	}

	/**
	 * Get term ID.
	 *
	 * @return int
	 */
	public function get_wp_term_id() {
		return (int) $this->data['wp_term_id'];
	}

	/**
	 * Set author ID.
	 *
	 * @param array
	 */
	public function set_group_types( $group_types ) {
		$this->data['group_types'] = $group_types;
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
		$this->data['order'] = (int) $order;
	}

	/**
	 * Set slug.
	 *
	 * @param string
	 */
	public function set_slug( $slug ) {
		$this->data['slug'] = $slug;
	}

	/**
	 * Set WP term ID.
	 *
	 * @param int
	 */
	public function set_wp_term_id( $wp_term_id ) {
		$this->data['wp_term_id'] = (int) $wp_term_id;
	}
}
