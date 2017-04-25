<?php

namespace CBOX\OL;

class Install {
	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	public function install() {
		$this->install_default_member_types();
	}

	public function upgrade() { }

	public function install_default_member_types() {
		// todo - move to save() method
		$types_data = array(
			'faculty' => array(
				'name' => __( 'Faculty', 'cbox-openlab-core' ),
				'labels' => array(
					'singular' => __( 'Faculty', 'cbox-openlab-core' ),
					'plural' => __( 'Faculty', 'cbox-openlab-core' ),
				),
				'can_create_courses' => true,
				'selectable_types' => array(),
				'is_enabled' => true,
				'order' => 1,
			),
			'staff' => array(
				'name' => __( 'Staff', 'cbox-openlab-core' ),
				'labels' => array(
					'singular' => __( 'Staff', 'cbox-openlab-core' ),
					'plural' => __( 'Staff', 'cbox-openlab-core' ),
				),
				'can_create_courses' => false,
				'selectable_types' => array(),
				'is_enabled' => true,
				'order' => 2,
			),
			'student' => array(
				'name' => __( 'Students', 'cbox-openlab-core' ),
				'labels' => array(
					'singular' => __( 'Student', 'cbox-openlab-core' ),
					'plural' => __( 'Students', 'cbox-openlab-core' ),
				),
				'can_create_courses' => false,
				'selectable_types' => array( 'student', 'alumni' ),
				'is_enabled' => true,
				'order' => 3,
			),
			'alumni' => array(
				'name' => __( 'Alumni', 'cbox-openlab-core' ),
				'labels' => array(
					'singular' => __( 'Alumni', 'cbox-openlab-core' ),
					'plural' => __( 'Alumni', 'cbox-openlab-core' ),
				),
				'can_create_courses' => false,
				'selectable_types' => array( 'student', 'alumni' ),
				'is_enabled' => true,
				'order' => 4,
			),
		);

		foreach ( $types_data as $type_slug => $type_data ) {
			// Don't overwrite existing item.
			$existing = get_posts( array(
				'post_type' => 'cboxol_member_type',
				'post_status' => array( 'publish', 'draft' ),
				'name' => $type_slug,
			) );

			if ( $existing ) {
				continue;
			}

			$type = MemberType::get_dummy();
			$type->set_name( $type_data['name'] );
			$type->set_slug( $type_slug );

			foreach ( $type_data['labels'] as $label_type => $label_data ) {
				$type->set_label( $label_type, $label_data );
			}

			$type->set_order( $type_data['order'] );
			$type->set_is_enabled( $type_data['is_enabled'] );
			$type->set_can_create_courses( $type_data['can_create_courses'] );
			$type->set_can_be_deleted( false );

			$type->save();
		}

		// Selectable types must be set after creation to make ID associations.
		foreach ( $types_data as $type_slug => $type_data ) {
			if ( ! empty( $type_data['selectable_types'] ) ) {
				$selectable_types = array_map( 'cboxol_get_member_type', $type_data['selectable_types'] );
				if ( ! $selectable_types ) {
					continue;
				}

				$type_ids = array();
				foreach ( $selectable_types as $st ) {
					$type_ids = $st->get_wp_post_id();
				}

				if ( ! $type_ids ) {
					continue;
				}

				$this_type = cboxol_get_member_type( $type_slug );
				$this_type->set_selectable_types( $type_ids );
				$this_type->save();
			}
		}

	}
}
