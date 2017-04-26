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
		$this->install_default_group_types();
	}

	public function upgrade() { }

	public function install_default_member_types() {
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
			'students' => array(
				'name' => __( 'Students', 'cbox-openlab-core' ),
				'labels' => array(
					'singular' => __( 'Student', 'cbox-openlab-core' ),
					'plural' => __( 'Students', 'cbox-openlab-core' ),
				),
				'can_create_courses' => false,
				'selectable_types' => array( 'students', 'alumni' ),
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
				'selectable_types' => array( 'students', 'alumni' ),
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

	public function install_default_group_types() {
		$types_data = array(
			'courses' => array(
				'name' => __( 'Courses', 'cbox-openlab-core' ),
				'is_enabled' => true,
				'order' => 1,

				'labels' => array(
					'singular' => __( 'Course', 'cbox-openlab-core' ),
					'plural' => __( 'Courses', 'cbox-openlab-core' ),
					'my_groups' => __( 'My Courses', 'cbox-openlab-core' ),
					'course_code' => __( 'Course Code', 'cbox-openlab-core' ),
					'course_information' => __( 'Course Information', 'cbox-openlab-core' ),
					'course_information_description' => __( 'The following fields are not required, but including this information will make it easier for others to find your Course.', 'cbox-openlab-core' ),
					'section_code' => __( 'Section Code', 'cbox-openlab-core' ),
				),

				'can_be_cloned' => true,
				'directory_filters' => array( 'term' ),
				'enable_portfolio_list' => true,
				'enable_site_by_default' => true,
				'is_course' => true, // for "Can create course" member type field
				'is_portfolio' => false,

				'supports_additional_faculty' => true,
				'supports_course_information' => true,
				'supports_group_contact' => false,
				'supports_mol_link' => true,
				'supports_profile_column' => true,
			),

			/*
			 * Note: For the time being, I'm not separating out the
			 * Portfolio-specific features into their own settings.
			 * is_portfolio checks will be used as a proxy.
			 */
			'portfolios' => array(
				'name' => __( 'Portfolios', 'cbox-openlab-core' ),
				'is_enabled' => true,
				'order' => 2,

				'labels' => array(
					'singular' => __( 'Portfolio', 'cbox-openlab-core' ),
					'plural' => __( 'Portfolios', 'cbox-openlab-core' ),
					'my_portfolio' => __( 'My Portfolio', 'cbox-openlab-core' ),
				),

				'can_be_cloned' => false,
				'directory_filters' => array( 'member_type' ),
				'enable_portfolio_list' => false,
				'enable_site_by_default' => true,
				'is_course' => false,
				'is_portfolio' => true,

				'supports_additional_faculty' => false,
				'supports_course_information' => false,
				'supports_group_contact' => false,
				'supports_mol_link' => false,
				'supports_profile_column' => false,
			),

			'clubs' => array(
				'name' => __( 'Clubs', 'cbox-openlab-core' ),
				'is_enabled' => true,
				'order' => 3,

				'labels' => array(
					'singular' => __( 'Club', 'cbox-openlab-core' ),
					'plural' => __( 'Clubs', 'cbox-openlab-core' ),
					'my_groups' => __( 'My Clubs', 'cbox-openlab-core' ),
				),

				'can_be_cloned' => false,
				'directory_filters' => array( 'category' ),
				'enable_portfolio_list' => false,
				'enable_site_by_default' => false,
				'is_course' => false,
				'is_portfolio' => false,

				'supports_additional_faculty' => false,
				'supports_course_information' => false,
				'supports_group_contact' => true,
				'supports_mol_link' => true,
				'supports_profile_column' => true,
			),

			'projects' => array(
				'name' => __( 'Projects', 'cbox-openlab-core' ),
				'is_enabled' => true,
				'order' => 4,

				'labels' => array(
					'singular' => __( 'Project', 'cbox-openlab-core' ),
					'plural' => __( 'Projects', 'cbox-openlab-core' ),
					'my_groups' => __( 'My Projects', 'cbox-openlab-core' ),
				),

				'can_be_cloned' => false,
				'directory_filters' => array( 'category' ),
				'enable_portfolio_list' => false,
				'enable_site_by_default' => false,
				'is_course' => false,
				'is_portfolio' => false,

				'supports_additional_faculty' => false,
				'supports_course_information' => false,
				'supports_group_contact' => true,
				'supports_mol_link' => true,
				'supports_profile_column' => true,
			),
		);

		foreach ( $types_data as $type_slug => $type_data ) {
			// Don't overwrite existing item.
			$existing = get_posts( array(
				'post_type' => 'cboxol_group_type',
				'post_status' => array( 'publish', 'draft' ),
				'name' => $type_slug,
			) );

			if ( $existing ) {
				continue;
			}

			$type = GroupType::get_dummy();
			$type->set_name( $type_data['name'] );
			$type->set_slug( $type_slug );

			foreach ( $type_data['labels'] as $label_type => $label_data ) {
				$type->set_label( $label_type, $label_data );
			}

			$type->set_order( $type_data['order'] );
			$type->set_is_enabled( $type_data['is_enabled'] );

			$type->set_can_be_cloned( $type_data['can_be_cloned'] );
			$type->set_directory_filters( $type_data['directory_filters'] );
			$type->set_enable_portfolio_list( $type_data['enable_portfolio_list'] );
			$type->set_is_course( $type_data['is_course'] );
			$type->set_is_portfolio( $type_data['is_portfolio'] );
			$type->set_supports_additional_faculty( $type_data['supports_additional_faculty'] );
			$type->set_supports_course_information( $type_data['supports_course_information'] );
			$type->set_supports_mol_link( $type_data['supports_mol_link'] );
			$type->set_supports_profile_column( $type_data['supports_profile_column'] );

			$type->set_can_be_deleted( false );

			$type->save();

			$type->create_template_site();
		}
	}
}
