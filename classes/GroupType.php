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
		'supports_additional_faculty' => false,
		'supports_course_information' => false,
		'supports_group_contact' => true,
		'supports_mol_link' => false,
		'supports_profile_column' => false,
	);

	protected $boolean_props = array(
		'can_be_cloned',
		'enable_portfolio_list',
		'enable_site_by_default',
		'is_course',
		'is_portfolio',
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
		);
	}

	public function get_directory_filters() {
		return $this->data['directory_filters'];
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
				'my_portfolio',
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
		);
	}

	public function set_directory_filters( $directory_filters ) {
		$this->data['directory_filters'] = $directory_filters;
	}
}
