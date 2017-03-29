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
		$types_data = array(
			'faculty' => array(
				'name' => __( 'Faculty', 'cbox-openlab-core' ),
				'description' => '',
				'labels' => array(
					'singular' => __( 'Faculty', 'cbox-openlab-core' ),
					'plural' => __( 'Faculty', 'cbox-openlab-core' ),
				),
				'can_create_courses' => true,
				'selectable_types' => array(),
				'is_enabled' => true,
			),
			'staff' => array(
				'name' => __( 'Staff', 'cbox-openlab-core' ),
				'description' => '',
				'labels' => array(
					'singular' => __( 'Staff', 'cbox-openlab-core' ),
					'plural' => __( 'Staff', 'cbox-openlab-core' ),
				),
				'can_create_courses' => false,
				'selectable_types' => array(),
				'is_enabled' => true,
			),
			'student' => array(
				'name' => __( 'Students', 'cbox-openlab-core' ),
				'description' => '',
				'labels' => array(
					'singular' => __( 'Student', 'cbox-openlab-core' ),
					'plural' => __( 'Students', 'cbox-openlab-core' ),
				),
				'can_create_courses' => false,
				'selectable_types' => array( 'student', 'alumni' ),
				'is_enabled' => true,
			),
			'alumni' => array(
				'name' => __( 'Alumni', 'cbox-openlab-core' ),
				'description' => '',
				'labels' => array(
					'singular' => __( 'Alumni', 'cbox-openlab-core' ),
					'plural' => __( 'Alumni', 'cbox-openlab-core' ),
				),
				'can_create_courses' => false,
				'selectable_types' => array( 'student', 'alumni' ),
				'is_enabled' => true,
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

			$post_data = array(
				'post_type' => 'cboxol_member_type',
				'post_title' => $type_data['name'],
				'post_content' => $type_data['description'],
				'post_name' => $type_slug,
			);

			if ( $type_data['is_enabled'] ) {
				$post_data['post_status'] = 'publish';
			} else {
				$post_data['post_status'] = 'draft';
			}

			$post_id = wp_insert_post( $post_data );

			add_post_meta( $post_id, 'cboxol_member_type_labels', $type_data['labels'] );

			if ( $type_data['can_create_courses'] ) {
				add_post_meta( $post_id, 'cboxol_member_type_can_create_courses', 'yes' );
			}

			if ( $type_data['selectable_types'] ) {
				add_post_meta( $post_id, 'cboxol_member_type_selectable_types', $type_data['selectable_types'] );
			}
		}
	}
}
