<?php

namespace CBOX\OL;

class GroupType extends ItemTypeBase implements ItemType {
	protected $post_type = 'cboxol_group_type';

	protected $defaults = array(
		'can_be_cloned' => false,
		'directory_filters' => array(),
		'enable_portfolio_list' => false,
		'enable_site_by_default' => false,
		'is_course' => false,
		'is_portfolio' => false,
		'requires_site' => false,
		'supports_additional_faculty' => false,
		'supports_course_information' => false,
		'supports_group_contact' => true,
		'supports_mol_link' => false,
		'supports_profile_column' => false,
		'template_site_id' => 0,
	);

	protected $boolean_props = array(
		'can_be_cloned',
		'enable_portfolio_list',
		'enable_site_by_default',
		'is_course',
		'is_portfolio',
		'requires_site',
		'supports_additional_faculty',
		'supports_course_information',
		'supports_group_contact',
		'supports_mol_link',
		'supports_profile_column',
	);

	public static function get_instance_from_wp_post( \WP_Post $post ) {
		$type = new self();
		$type->set_up_instance_from_wp_post( $post );

		$type->set_directory_filters( get_post_meta( $post->ID, 'cboxol_group_type_directory_filters', true ) );
		$type->set_template_site_id( get_post_meta( $post->ID, 'cboxol_group_type_template_site_id', true ) );

		return $type;
	}

	public function get_for_endpoint() {
		// @todo This doesn't need to go in every one.
		$types = cboxol_get_group_types( array(
			'enabled' => null,
		) );

		$all_types = array_map( function( $type ) {
			return array(
				'slug' => $type->get_slug(),
				'name' => $type->get_name(),
				'id' => $type->get_wp_post_id(),
			);
		}, $types );

		return array(
			'id' => $this->get_wp_post_id(),
			'isCollapsed' => true,
			'isCourse' => $this->get_is_course(),
			'isPortfolio' => $this->get_is_portfolio(),
			'isEnabled' => $this->get_is_enabled(),
			'isLoading' => false,
			'isModified' => false,
			'canBeDeleted' => $this->get_can_be_deleted(),
			'settings' => array(
				'Order' => array(
					'component' => 'Order',
					'data' => $this->get_order(),
				),
			),
			'name' => $this->get_name(),
			'slug' => $this->get_slug(),
			'labels' => $this->get_labels(),
			'templateSite' => $this->get_template_site_info(),
		);
	}

	public function get_directory_filters() {
		return $this->data['directory_filters'];
	}

	public function get_template_site_id() {
		return (int) $this->data['template_site_id'];
	}

	public function get_template_site_info() {
		$site_id = $this->get_template_site_id();

		return array(
			'siteId' => $site_id,
			'name' => get_site_option( $site_id, 'blogname' ),
			'url' => get_home_url( $site_id ),
			'adminUrl' => get_admin_url( $site_id ),
		);
	}

	/**
	 * Overridden here because we filter based on group type.
	 */
	public function get_labels() {
		$map = array(
			'course' => array(
				'singular',
				'plural',
				'my_groups',
				'course_information',
				'course_information_description',
				'course_code',
				'section_code',
			),
			'portfolio' => array(
				'singular',
				'plural',
				'create_item',
				'my_portfolio',
				'my_portfolio_site',
				'visit_portfolio_site',
			),
			'default' => array(
				'singular',
				'plural',
				'my_groups',
			),
		);

		if ( $this->get_is_course() ) {
			$type_labels = $map['course'];
		} elseif ( $this->get_is_portfolio() ) {
			$type_labels = $map['portfolio'];
		} else {
			$type_labels = $map['default'];
		}

		// this is a real mess
		$retval = array();
		foreach ( $this->data['labels'] as $label_slug => $label ) {
			if ( is_array( $label ) && isset( $label['slug'] ) ) {
				$ls = $label['slug'];
			} else {
				$ls = $label_slug;
			}

			if ( in_array( $ls, $type_labels, true ) ) {
				$retval[ $ls ] = $label;
			}
		}

		// this continues to be a real mess
		$sorted = array();
		foreach ( $type_labels as $value ) {
			$sorted[ $value ] = $retval[ $value ];
		}

		return $sorted;
	}

	public function save() {
		$this->save_to_wp_post();

		$wp_post_id = $this->get_wp_post_id();

		update_post_meta( $wp_post_id, 'cboxol_group_type_directory_filters', $this->get_directory_filters() );
	}

	public static function get_dummy() {
		$dummy = new self();

		foreach ( $dummy->get_label_types() as $label_type => $label_labels ) {
			$label_labels['value'] = '';
			$dummy->set_label( $label_type, $label_labels );
		}

		return $dummy;
	}

	public function get_label_types() {
		return array(
			'course_code' => array(
				'slug' => 'course_code',
				'label' => _x( 'Course Code', 'Group Type label', 'cbox-openlab-core' ),
				'description' => __( 'The label for the "Course Code" input when editing Course settings.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'course_information' => array(
				'slug' => 'course_information',
				'label' => __( 'Course Information', 'cbox-openlab-core' ),
				'description' => __( 'The label for the course settings section containing Course Code and other catalog data.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'course_information_description' => array(
				'slug' => 'course_information_description',
				'label' => __( 'Course Information Help Text', 'cbox-openlab-core' ),
				'description' => __( 'The helper text in the Course Information admin section of a Course.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'create_item' => array(
				'slug' => 'create_item',
				'label' => __( 'Create Item', 'cbox-openlab-core' ),
				'description' => __( 'The text used for "Create" links.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'my_groups' => array(
				'slug' => 'my_groups',
				'label' => _x( 'My Groups', 'Group Type label', 'cbox-openlab-core' ),
				'description' => __( 'Used in personal navigation and on member profiles.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'my_portfolio' => array(
				'slug' => 'my_portfolio',
				'label' => _x( 'My Portfolio', 'Group Type label', 'cbox-openlab-core' ),
				'description' => __( 'Used in personal navigation and on member profiles.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'my_portfolio_site' => array(
				'slug' => 'my_portfolio_site',
				'label' => _x( 'My Portfolio Site', 'Group Type label', 'cbox-openlab-core' ),
				'description' => __( 'Used as the link to a user\'s own portfolio site.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'plural' => array(
				'slug' => 'plural',
				'label' => _x( 'Plural', 'Member Type plural label', 'cbox-openlab-core' ),
				'description' => __( 'Used in directory titles.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'section_code' => array(
				'slug' => 'section_code',
				'label' => _x( 'Section Code', 'Group Type label', 'cbox-openlab-core' ),
				'description' => __( 'The label for the "Section Code" input when editing Course settings.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'singular' => array(
				'slug' => 'singular',
				'label' => _x( 'Singular', 'Member Type singular label', 'cbox-openlab-core' ),
				'description' => __( 'Used wherever a specific member\'s Type is mentioned, such as the User Edit interface.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'visit_portfolio_site' => array(
				'slug' => 'visit_portfolio_site',
				'label' => _x( 'Visit Portfolio Site', 'Group Type label', 'cbox-openlab-core' ),
				'description' => __( 'Used as the link to another user\'s portfolio site.', 'cbox-openlab-core' ),
				'value' => '',
			),
		);
	}

	public function set_directory_filters( $directory_filters ) {
		$this->data['directory_filters'] = $directory_filters;
	}

	public function set_template_site_id( $template_site_id ) {
		$this->data['template_site_id'] = (int) $template_site_id;
	}

	public function create_template_site() {
		$current_network = get_network();

		// Use timestamp as a hash to ensure uniqueness.
		$slug = sprintf( 'site-template-%s-%s', $this->get_slug(), time() );
		if ( is_subdomain_install() ) {
			$site_domain = preg_replace( '|^www\.|', '', $current_network->domain );
			$domain = $slug . '.' . $site_domain;
			$path = '/';
		} else {
			$domain = $current_network->domain;
			$path = $current_network->path . $slug . '/';
		}

		$site_id = wpmu_create_blog(
			$domain,
			$path,
			sprintf( __( 'Site Template - %s', 'cbox-openlab-core' ), $this->get_name() ),
			get_current_user_id()
		);

		if ( ! $site_id ) {
			return;
		}

		update_post_meta( $this->get_wp_post_id(), 'cboxol_group_type_template_site_id', $site_id );
		$this->set_template_site_id( $site_id );
	}
}
