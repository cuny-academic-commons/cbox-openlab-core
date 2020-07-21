<?php

namespace CBOX\OL;

class ItemTypeBase {
	protected $post_type = '';

	protected $data = array(
		'slug'           => '',
		'name'           => '',
		'labels'         => array(),
		'can_be_deleted' => true,
		'is_enabled'     => true,
		'order'          => 0,
		'wp_post_id'     => 0,
	);

	// can_be_deleted and is_enabled have special logic, so can't be lumped in.
	// phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore
	protected $_boolean_props = array();

	protected $boolean_props = array();

	protected $defaults = array();

	public function __construct() {
		$this->data          = array_merge( $this->data, $this->defaults );
		$this->boolean_props = array_merge( $this->_boolean_props, $this->boolean_props );
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
			$label = $this->data['labels'][ $label_type ];
		}

		return $label;
	}

	public function get_labels() {
		$retval     = array();
		$label_info = $this->get_label_types_info();
		foreach ( $this->data['labels'] as $label_slug => $label_value ) {
			$label_data            = $label_info[ $label_slug ];
			$label_data['value']   = $label_value;
			$retval[ $label_slug ] = $label_data;
		}

		return $retval;
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

		foreach ( $saved_labels as $label_type => $label_value ) {
			$this->set_label( $label_type, $label_value );
		}

		// Enabled.
		$this->set_is_enabled( 'publish' === $post->post_status );

		// Order
		$this->set_order( $post->menu_order );

		foreach ( $this->boolean_props as $bool ) {
			$method = 'set_' . $bool;
			$val    = get_post_meta( $post->ID, 'cboxol_group_type_' . $bool, true );
			$this->$method( 'yes' === $val );
		}

		// Can be deleted.
		$can_be_deleted_db = get_post_meta( $post->ID, 'cboxol_item_type_is_builtin', true );
		$can_be_deleted    = 'yes' !== $can_be_deleted_db;
		$this->set_can_be_deleted( $can_be_deleted );

		// WP post ID.
		$this->set_wp_post_id( $post->ID );
	}

	public function save_to_wp_post() {
		$wp_post_id = $this->get_wp_post_id();

		$name = $this->get_name();

		// @todo Should we enforce slug immutability here?
		$slug = $this->get_slug();

		$post_params = array(
			'post_title' => $name,
			'post_name'  => $slug,
			'menu_order' => $this->get_order(),
		);

		if ( $this->get_is_enabled() ) {
			$post_params['post_status'] = 'publish';
		} else {
			$post_params['post_status'] = 'draft';
		}

		if ( $wp_post_id ) {
			$post_params['ID'] = $wp_post_id;
			wp_update_post( $post_params );
		} else {
			$post_params['post_type'] = $this->post_type;
			$wp_post_id               = wp_insert_post( $post_params );
			$wp_post                  = get_post( $wp_post_id );
			$this->set_wp_post_id( $wp_post_id );
			$this->set_slug( $wp_post->post_name );
		}

		$meta_value = array();
		foreach ( $this->get_labels() as $label_type => $label_data ) {
			// A total mess. Prevents double saving of an array.
			if ( is_array( $label_data ) && isset( $label_data['value'] ) ) {
				$label_data = $label_data['value'];
			}
			$meta_value[ $label_type ] = $label_data;
		}
		update_post_meta( $wp_post_id, 'cboxol_item_type_labels', $meta_value );

		// Boolean props are saved to 'yes' or deleted.
		foreach ( $this->boolean_props as $bool ) {
			$method   = 'get_' . $bool;
			$meta_key = 'cboxol_group_type_' . $bool;
			if ( $this->$method() ) {
				update_post_meta( $wp_post_id, $meta_key, 'yes' );
			} else {
				delete_post_meta( $wp_post_id, $meta_key, 'yes' );
			}
		}
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

	public function set_order( $order ) {
		$this->data['order'] = (int) $order;
	}

	public function set_is_enabled( $is_enabled ) {
		$this->data['is_enabled'] = (bool) $is_enabled;
	}

	protected function set_wp_post_id( $wp_post_id ) {
		$this->data['wp_post_id'] = (int) $wp_post_id;
	}

	/**
	 * Used to set boolean props.
	 *
	 * Most props are boolean. This helps to avoid boilerplate.
	 */
	public function __call( $name, $args ) {
		$method_type = null;
		if ( 'get_' === substr( $name, 0, 4 ) ) {
			$method_type = 'get';
		} elseif ( 'set_' === substr( $name, 0, 4 ) ) {
			$method_type = 'set';
		}

		if ( ! $method_type ) {
			return null;
		}

		$prop = substr( $name, 4 );
		if ( ! in_array( $prop, $this->boolean_props, true ) ) {
			return null;
		}

		switch ( $method_type ) {
			case 'get':
				return (bool) $this->data[ $prop ];

			case 'set':
				$this->data[ $prop ] = (bool) $args[0];
				break;
		}
	}

}
