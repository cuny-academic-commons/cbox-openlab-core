<?php

namespace CBOX\OL;

use \CBox_Widget_Setter;

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
		$this->install_default_group_categories();
		$this->install_default_academic_types();
		$this->install_default_brand_pages();

		$this->install_default_widgets();
		$this->install_default_nav_menus();
		$this->install_default_slides();
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

	public function install_default_group_types() {
		$types_data = array(
			'course' => array(
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
					'group_site' => __( 'Course Site', 'cbox-openlab-core' ),
					'status_open' => __( 'This Course is OPEN', 'cbox-openlab-core' ),
					'status_open_community_site' => __( 'This Course is OPEN, but only logged-in community members may view the corresponding Site.', 'cbox-openlab-core' ),
					'status_open_private_site' => __( 'This Course is OPEN, but the corresponding Site is PRIVATE.', 'cbox-openlab-core' ),
					'status_private' => __( 'This Course is PRIVATE.', 'cbox-openlab-core' ),
					'status_private_community_site' => __( 'This Course is PRIVATE, but all logged-in community members may view the corresponding Site.', 'cbox-openlab-core' ),
					'status_private_open_site' => __( 'This Course is PRIVATE, but the corresponding Site is OPEN to all visitors.', 'cbox-openlab-core' ),
					'status_private_private_site' => __( 'This Course is PRIVATE, and you must be a member to view the corresponding Site.', 'cbox-openlab-core' ),
					'visit_group_site' => __( 'Visit Course Site', 'cbox-openlab-core' ),
					'group_profile' => __( 'Course Profile', 'cbox-openlab-core' ),
				),

				'can_be_cloned' => true,
				'directory_filters' => array( 'term' ),
				'enable_portfolio_list_by_default' => true,
				'enable_site_by_default' => true,
				'is_course' => true, // for "Can create course" member type field
				'is_portfolio' => false,

				'requires_site' => false,
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
			'portfolio' => array(
				'name' => __( 'Portfolios', 'cbox-openlab-core' ),
				'is_enabled' => true,
				'order' => 2,

				'labels' => array(
					'singular' => __( 'Portfolio', 'cbox-openlab-core' ),
					'plural' => __( 'Portfolios', 'cbox-openlab-core' ),
					'create_item' => __( 'Create Portfolio', 'cbox-openlab-core' ),
					'my_portfolio' => __( 'My Portfolio', 'cbox-openlab-core' ),
					'my_portfolio_site' => __( 'My Portfolio Site', 'cbox-openlab-core' ),
					'status_open' => __( 'This Portfolio is OPEN', 'cbox-openlab-core' ),
					'status_open_community_site' => __( 'This Portfolio is OPEN, but only logged-in community members may view the corresponding Site.', 'cbox-openlab-core' ),
					'status_open_private_site' => __( 'This Portfolio is OPEN, but the corresponding Site is PRIVATE.', 'cbox-openlab-core' ),
					'status_private' => __( 'This Portfolio is PRIVATE.', 'cbox-openlab-core' ),
					'status_private_community_site' => __( 'This Portfolio is PRIVATE, but all logged-in community members may view the corresponding Site.', 'cbox-openlab-core' ),
					'status_private_open_site' => __( 'This Portfolio is PRIVATE, but the corresponding Site is OPEN to all visitors.', 'cbox-openlab-core' ),
					'status_private_private_site' => __( 'This Portfolio is PRIVATE, and you must be a member to view the corresponding Site.', 'cbox-openlab-core' ),
					'visit_portfolio_site' => __( 'Visit Portfolio Site', 'cbox-openlab-core' ),
					'visit_group_site' => __( 'Visit Portfolio Site', 'cbox-openlab-core' ),
					'group_site' => __( 'Portfolio Site', 'cbox-openlab-core' ),
					'group_profile' => __( 'Portfolio Profile', 'cbox-openlab-core' ),
				),

				'can_be_cloned' => false,
				'directory_filters' => array( 'member_type' ),
				'enable_portfolio_list_by_default' => false,
				'enable_site_by_default' => true,
				'is_course' => false,
				'is_portfolio' => true,

				'requires_site' => true,
				'supports_additional_faculty' => false,
				'supports_course_information' => false,
				'supports_group_contact' => false,
				'supports_mol_link' => false,
				'supports_profile_column' => false,
			),

			'club' => array(
				'name' => __( 'Clubs', 'cbox-openlab-core' ),
				'is_enabled' => true,
				'order' => 3,

				'labels' => array(
					'singular' => __( 'Club', 'cbox-openlab-core' ),
					'plural' => __( 'Clubs', 'cbox-openlab-core' ),
					'my_groups' => __( 'My Clubs', 'cbox-openlab-core' ),
					'group_site' => __( 'Club Site', 'cbox-openlab-core' ),
					'status_open' => __( 'This Club is OPEN', 'cbox-openlab-core' ),
					'status_open_community_site' => __( 'This Club is OPEN, but only logged-in community members may view the corresponding Site.', 'cbox-openlab-core' ),
					'status_open_private_site' => __( 'This Club is OPEN, but the corresponding Site is PRIVATE.', 'cbox-openlab-core' ),
					'status_private' => __( 'This Club is PRIVATE.', 'cbox-openlab-core' ),
					'status_private_community_site' => __( 'This Club is PRIVATE, but all logged-in community members may view the corresponding Site.', 'cbox-openlab-core' ),
					'status_private_open_site' => __( 'This Club is PRIVATE, but the corresponding Site is OPEN to all visitors.', 'cbox-openlab-core' ),
					'status_private_private_site' => __( 'This Club is PRIVATE, and you must be a member to view the corresponding Site.', 'cbox-openlab-core' ),
					'visit_group_site' => __( 'Visit Club Site', 'cbox-openlab-core' ),
					'group_profile' => __( 'Club Profile', 'cbox-openlab-core' ),
				),

				'can_be_cloned' => false,
				'directory_filters' => array( 'category' ),
				'enable_portfolio_list_by_default' => false,
				'enable_site_by_default' => false,
				'is_course' => false,
				'is_portfolio' => false,

				'requires_site' => false,
				'supports_additional_faculty' => false,
				'supports_course_information' => false,
				'supports_group_contact' => true,
				'supports_mol_link' => true,
				'supports_profile_column' => true,
			),

			'project' => array(
				'name' => __( 'Projects', 'cbox-openlab-core' ),
				'is_enabled' => true,
				'order' => 4,

				'labels' => array(
					'singular' => __( 'Project', 'cbox-openlab-core' ),
					'plural' => __( 'Projects', 'cbox-openlab-core' ),
					'my_groups' => __( 'My Projects', 'cbox-openlab-core' ),
					'group_site' => __( 'Project Site', 'cbox-openlab-core' ),
					'status_open' => __( 'This Project is OPEN', 'cbox-openlab-core' ),
					'status_open_community_site' => __( 'This Project is OPEN, but only logged-in community members may view the corresponding Site.', 'cbox-openlab-core' ),
					'status_open_private_site' => __( 'This Project is OPEN, but the corresponding Site is PRIVATE.', 'cbox-openlab-core' ),
					'status_private' => __( 'This Project is PRIVATE.', 'cbox-openlab-core' ),
					'status_private_community_site' => __( 'This Project is PRIVATE, but all logged-in community members may view the corresponding Site.', 'cbox-openlab-core' ),
					'status_private_open_site' => __( 'This Project is PRIVATE, but the corresponding Site is OPEN to all visitors.', 'cbox-openlab-core' ),
					'status_private_private_site' => __( 'This Project is PRIVATE, and you must be a member to view the corresponding Site.', 'cbox-openlab-core' ),
					'visit_group_site' => __( 'Visit Project Site', 'cbox-openlab-core' ),
					'group_profile' => __( 'Project Profile', 'cbox-openlab-core' ),
				),

				'can_be_cloned' => false,
				'directory_filters' => array( 'category' ),
				'enable_portfolio_list_by_default' => false,
				'enable_site_by_default' => false,
				'is_course' => false,
				'is_portfolio' => false,

				'requires_site' => false,
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
			$type->set_enable_portfolio_list_by_default( $type_data['enable_portfolio_list_by_default'] );
			$type->set_enable_site_by_default( $type_data['enable_site_by_default'] );
			$type->set_is_course( $type_data['is_course'] );
			$type->set_is_portfolio( $type_data['is_portfolio'] );
			$type->set_requires_site( $type_data['requires_site'] );
			$type->set_supports_group_contact( $type_data['supports_group_contact'] );
			$type->set_supports_additional_faculty( $type_data['supports_additional_faculty'] );
			$type->set_supports_course_information( $type_data['supports_course_information'] );
			$type->set_supports_mol_link( $type_data['supports_mol_link'] );
			$type->set_supports_profile_column( $type_data['supports_profile_column'] );

			$type->set_can_be_deleted( false );

			$type->save();

			$type->create_template_site();
		}

		cboxol_grouptypes_register_group_types();
	}

	protected function install_default_group_categories() {
		$cats = array(
			'coursework' => array(
				'name' => __( 'Coursework', 'cbox-openlab-core' ),
				'types' => array( 'course', 'portfolio' ),
			),
			'research' => array(
				'name' => __( 'Research', 'cbox-openlab-core' ),
				'types' => array( 'portfolio', 'project' ),
			),
		);

		foreach ( $cats as $cat_slug => $cat ) {
			$c = new GroupCategory();
			$c->set_slug( $cat_slug );
			$c->set_name( $cat['name'] );
			$c->set_group_types( $cat['types'] );
			$c->save();
		}
	}

	protected function install_default_academic_types() {
		$types = array(
			'schools' => array(
				'name' => __( 'Schools', 'cbox-openlab-core' ),
				'labels' => array(
					'singular' => __( 'School', 'cbox-openlab-core' ),
					'plural' => __( 'Schools', 'cbox-openlab-core' ),
				),
				'parent' => '',
				'order' => 1,
				'member_types' => array(
					'student' => 'optional',
					'faculty' => 'required',
					'staff' => '',
					'alumni' => '',
				),
				'group_types' => array(
					'course' => 'required',
					'project' => 'optional',
					'club' => '',
					'portfolios' => '',
				),
			),
			'departments' => array(
				'name' => __( 'Departments', 'cbox-openlab-core' ),
				'labels' => array(
					'singular' => __( 'Department', 'cbox-openlab-core' ),
					'plural' => __( 'Departments', 'cbox-openlab-core' ),
				),
				'parent' => 'schools',
				'order' => 2,
				'member_types' => array(
					'student' => 'optional',
					'faculty' => 'required',
					'staff' => '',
					'alumni' => '',
				),
				'group_types' => array(
					'course' => 'required',
					'project' => 'optional',
					'club' => '',
					'portfolios' => '',
				),
			),
		);

		foreach ( $types as $type_slug => $type_data ) {
			// Don't overwrite existing item.
			$existing = get_posts( array(
				'post_type' => 'cboxol_acad_unit_type',
				'post_status' => array( 'publish', 'draft' ),
				'name' => $type_slug,
			) );

			if ( $existing ) {
				continue;
			}

			$type_obj = new AcademicUnitType();
			$type_obj->set_slug( $type_slug );
			$type_obj->set_name( $type_data['name'] );
			$type_obj->set_parent( $type_data['parent'] );
			$type_obj->set_order( $type_data['order'] );
			$type_obj->set_member_types( $type_data['member_types'] );
			$type_obj->set_group_types( $type_data['group_types'] );

			foreach ( $type_data['labels'] as $label_type => $label_data ) {
				$type_obj->set_label( $label_type, $label_data );
			}

			$type_obj->save();
		}

		$units = array(
			'arts-and-sciences' => array(
				'type' => 'schools',
				'name' => __( 'Arts and Sciences', 'cbox-openlab-core' ),
				'parent' => '',
			),
			'english' => array(
				'type' => 'departments',
				'name' => __( 'English', 'cbox-openlab-core' ),
				'parent' => 'arts-and-sciences',
			),
		);

		foreach ( $units as $unit_slug => $unit_data ) {
			// Don't overwrite existing item.
			$existing = get_posts( array(
				'post_type' => 'cboxol_acad_unit',
				'post_status' => array( 'publish', 'draft' ),
				'name' => $type_slug,
			) );

			if ( $existing ) {
				continue;
			}

			$unit_obj = new AcademicUnit();
			$unit_obj->set_slug( $unit_slug );
			$unit_obj->set_name( $unit_data['name'] );
			$unit_obj->set_parent( $unit_data['parent'] );
			$unit_obj->set_type( $unit_data['type'] );
			$unit_obj->save();
		}
	}

	protected function install_default_brand_pages() {
		$brand_page_types = cboxol_get_brand_page_types();
		$pages = array(
			'about' => array(
				'post_title' => __( 'About', 'cbox-openlab-core' ),
				'post_content' => __( '<p>This page can contain an introduction to your site, institution, or organization.</p><p>This text can be edited in the Pages area of the dashboard</p>.', 'cbox-openlab-core' ),
			),
			'help' => array(
				'post_title' => __( 'Help', 'cbox-openlab-core' ),
				'post_content' => __( 'This is the content of your Help page.', 'cbox-openlab-core' ),
			),
			'terms-of-use' => array(
				'post_title' => __( 'Terms of Use', 'cbox-openlab-core' ),
				'post_content' => __( 'This is the content of your Terms of Use page.', 'cbox-openlab-core' ),
			),
			'contact-us' => array(
				'post_title' => __( 'Contact Us', 'cbox-openlab-core' ),
				'post_content' => __( 'This is the content of your Contact Us page.', 'cbox-openlab-core' ),
			),
		);

		$page_ids = array();
		foreach ( $brand_page_types as $brand_page_type_name => $brand_page_type_info ) {
			if ( ! isset( $pages[ $brand_page_type_name ] ) ) {
				continue;
			}

			$page = $pages[ $brand_page_type_name ];

			$page_args = array(
				'post_type' => 'page',
				'post_title' => $page['post_title'],
				'post_content' => $page['post_content'],
				'post_name' => $brand_page_type_name,
				'post_status' => 'publish',
			);

			if ( isset( $brand_page_type_info['parent'] ) ) {
				$parent_page_name = $brand_page_type_info['parent'];
				if ( isset( $page_ids[ $parent_page_name ] ) ) {
					$page_args['post_parent'] = $page_ids[ $parent_page_name ];
				}
			}

			$page_id = wp_insert_post( $page_args );

			if ( $page_id ) {
				$page_ids[ $brand_page_type_name ] = $page_id;
			}
		}

		update_site_option( 'cboxol_brand_page_ids', $page_ids );
	}

	protected function install_default_widgets() {
		openlab_register_sidebars();

		require CBOXOL_PLUGIN_DIR . '/lib/cbox-widget-setter.php';

		// Group Type widgets.
		if ( ! CBox_Widget_Setter::is_sidebar_populated( 'home-main' ) ) {
			$group_types = cboxol_get_group_types();
			foreach ( $group_types as $group_type ) {
				$result = CBox_Widget_Setter::set_widget( array(
					'id_base'    => 'openlab_group_type',
					'sidebar_id' => 'home-main',
					'settings'   => array(
						'title' => $group_type->get_label( 'plural' ),
						'group_type' => $group_type->get_slug(),
					),
				) );
			}
		}

		// Home sidebar.
		if ( ! CBox_Widget_Setter::is_sidebar_populated( 'home-sidebar' ) ) {
			CBox_Widget_Setter::set_widget( array(
				'id_base'    => 'cac_featured_content_widget',
				'sidebar_id' => 'home-sidebar',
				'settings'   => array(
					'crop_length' => 300,
					'custom_description' => __( 'Use this space to highlight content from around your network.', 'openlab-theme' ),
					'display_images' => true,
					'featured_content_type' => 'resource',
					'featured_resource_title' => __( 'Featured Item', 'openlab-theme' ),
					'featured_resource_link' => home_url(),
					'image_url' => bp_core_avatar_default(),
					'image_height' => 50,
					'image_width' => 50,
					'read_more' => '',
					'title' => __( 'In The Spotlight', 'openlab-theme' ),
					'title_element' => 'h2',
				),
			) );

			CBox_Widget_Setter::set_widget( array(
				'id_base'    => 'openlab-whats-happening',
				'sidebar_id' => 'home-sidebar',
			) );

			CBox_Widget_Setter::set_widget( array(
				'id_base'    => 'openlab-whos-online',
				'sidebar_id' => 'home-sidebar',
			) );

			CBox_Widget_Setter::set_widget( array(
				'id_base'    => 'openlab-new-members',
				'sidebar_id' => 'home-sidebar',
			) );
		}

		// Footer sidebar.
		if ( ! CBox_Widget_Setter::is_sidebar_populated( 'footer' ) ) {
			$welcome_text = __( 'The footer areas can be used to display general information about your site, such as contact information and links to terms of service.', 'openlab-theme' );

			CBox_Widget_Setter::set_widget( array(
				'id_base'    => 'text',
				'sidebar_id' => 'footer',
				'settings'   => array(
					'title' => __( 'Footer area 1', 'openlab-theme' ),
					'text'  => $welcome_text,
					'filter' => false,
				),
			) );

			$welcome_text = sprintf( __( 'Modify the text of this and other widgets using the <a href="%s">Customizer</a>.', 'openlab-theme' ), get_admin_url( cbox_get_main_site_id(), 'customize.php?autofocus[section]=sidebar-widgets-footer' ) );

			CBox_Widget_Setter::set_widget( array(
				'id_base'    => 'text',
				'sidebar_id' => 'footer',
				'settings'   => array(
					'title' => __( 'Footer area 2', 'openlab-theme' ),
					'text'  => $welcome_text,
					'filter' => false,
				),
			) );
		}
	}

	protected function install_default_nav_menus() {
		// Main Menu.
		$menu_name = wp_slash( __( 'Main Menu', 'cbox-openlab-core' ) );
		$menu_id = wp_create_nav_menu( $menu_name );

		if ( is_wp_error( $menu_id ) ) {
			return;
		}

		$brand_pages = cboxol_get_brand_pages();
		if ( isset( $brand_pages['about'] ) ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title' => $brand_pages['about']['title'],
					'menu-item-classes' => 'about',
					'menu-item-url' => $brand_pages['about']['preview_url'],
					'menu-item-status' => 'publish',
				)
			);
		}

		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title' => bp_get_directory_title( 'members' ),
				'menu-item-classes' => 'home',
				'menu-item-url' => bp_get_members_directory_permalink(),
				'menu-item-status' => 'publish',
			)
		);

		$group_types = cboxol_get_group_types();
		foreach ( $group_types as $group_type ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title' => $group_type->get_label( 'plural' ),
					'menu-item-classes' => 'group-type ' . $group_type->get_slug(),
					'menu-item-url' => bp_get_group_type_directory_permalink( $group_type->get_slug() ),
					'menu-item-status' => 'publish',
				)
			);
		}

		if ( isset( $brand_pages['help'] ) ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title' => $brand_pages['help']['title'],
					'menu-item-classes' => 'help',
					'menu-item-url' => $brand_pages['help']['preview_url'],
					'menu-item-status' => 'publish',
				)
			);
		}

		$locations = get_theme_mod( 'nav_menu_locations' );
		$locations['main'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );

		// About Menu.
		if ( isset( $brand_pages['about'] ) ) {
			$menu_name = wp_slash( __( 'About Menu', 'cbox-openlab-core' ) );
			$menu_id = wp_create_nav_menu( $menu_name );

			if ( is_wp_error( $menu_id ) ) {
				return;
			}

			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title' => $brand_pages['about']['title'],
					'menu-item-classes' => 'about',
					'menu-item-url' => $brand_pages['about']['preview_url'],
					'menu-item-status' => 'publish',
				)
			);

			foreach ( $brand_pages as $brand_page_name => $brand_page ) {
				if ( ! isset( $brand_page['parent'] ) || 'about' !== $brand_page['parent'] ) {
					continue;
				}

				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title' => $brand_page['title'],
						'menu-item-classes' => 'about ' . $brand_page_name,
						'menu-item-url' => $brand_page['preview_url'],
						'menu-item-status' => 'publish',
					)
				);
			}

			$locations = get_theme_mod( 'nav_menu_locations' );
			$locations['aboutmenu'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	protected function install_default_slides() {
		$slides = array(
			array(
				'title' => __( 'Your Second Sample Slide', 'openlab-theme' ),
				'content' => 'Ex consequatur ipsam iusto id impedit nesciunt. Velit perspiciatis laborum et culpa rem earum. Beatae fugit perspiciatis dolorum. Incidunt voluptate officia cupiditate ipsum. Officiis eius quo incidunt voluptatem vitae deleniti aut. Non dolorem iste qui voluptates id ratione unde accusantium.',
				'image' => get_template_directory() . '/images/default-slide-1.jpeg',
			),
			array(
				'title' => __( 'Your First Sample Slide', 'openlab-theme' ),
				'content' => 'Ipsam et voluptas sed qui vel voluptatem quam. Qui pariatur occaecati consequatur quibusdam reiciendis aut asperiores nam. Esse et et id amet et quis. Beatae quaerat a ea expedita blanditiis quia. Doloremque ad nemo culpa. Quia at qui et.',
				'image' => get_template_directory() . '/images/default-slide-2.jpeg',
			),
		);

		// only need these if performing outside of admin environment
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		foreach ( $slides as $slide ) {
			$slide_id = wp_insert_post( array(
				'post_type' => 'slider',
				'post_status' => 'publish',
				'post_title' => $slide['title'],
				'post_content' => $slide['content'],
			) );

			$file_path = $slide['image'];

			// Generate attachment and set as featured post.
			$tmpfname = wp_tempnam( $file_path );
			copy( $file_path, $tmpfname );

			$file = array(
				'error' => null,
				'tmp_name' => $tmpfname,
				'size' => filesize( $file_path ),
				'name' => basename( $file_path ),
			);

			$overrides = array(
				'test_form' => false,
				'test_size' => false,
			);

			$sideloaded = wp_handle_sideload( $file, $overrides );

			$attachment = array(
				'post_mime_type' => $sideloaded['type'],
				'post_title' => basename( $tmpfname ),
				'post_content' => '',
				'post_status' => 'inherit',
				'post_parent' => $slide_id,
			);

			$attachment_id = wp_insert_attachment( $attachment, $sideloaded['file'] );
			$attach_data = wp_generate_attachment_metadata( $attachment_id, $sideloaded );
			wp_update_attachment_metadata( $attachment_id, $attach_data );

			set_post_thumbnail( $slide_id, $attachment_id );
		}
	}
}
