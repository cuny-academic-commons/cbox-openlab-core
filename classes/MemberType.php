<?php

namespace CBOX\OL;

use \WP_Error;

class MemberType extends ItemTypeBase implements ItemType {
	protected $post_type = 'cboxol_member_type';

	protected $defaults = array(
		'can_be_deleted'         => true,
		'can_create_courses'     => false,
		'can_import_group_users' => false,
		'requires_signup_code'   => null,
		'selectable_types'       => array(),
	);

	protected $boolean_props = array(
		'can_create_courses',
		'can_import_group_users',
	);

	public function __construct() {
		$this->taxonomy = bp_get_member_type_tax_name();
		parent::__construct();
	}

	public function get_selectable_types() {
		// @todo Should validate types here (can't do on setup because it will trigger a loop).
		return $this->data['selectable_types'];
	}

	/**
	 * Get a human-readable, comma-separated list of labels for this type's selectable types.
	 *
	 * @return string
	 */
	public function get_selectable_types_list() {
		$list = '';

		$selectable_types = $this->get_selectable_types();
		$labels           = array();
		foreach ( $selectable_types as $selectable_type ) {
			$selectable_type_obj = cboxol_get_member_type( $selectable_type );
			if ( $selectable_type_obj ) {
				$labels[] = $selectable_type_obj->get_name();
			}
		}

		if ( $labels ) {
			$list = implode( ', ', $labels );
		}

		return $list;
	}

	/**
	 * Determines whether a member type requires validation with a Signup Code.
	 *
	 * @return bool
	 */
	public function get_requires_signup_code() {
		if ( ! is_null( $this->data['requires_signup_code'] ) ) {
			return $this->data['requires_signup_code'];
		}

		$requires_signup_code = false;

		$signup_codes = cboxol_get_signup_codes();
		foreach ( $signup_codes as $signup_code ) {
			$code_member_type = $signup_code->get_member_type();
			if ( is_wp_error( $code_member_type ) ) {
				continue;
			}

			if ( $code_member_type->get_slug() === $this->get_slug() ) {
				$requires_signup_code = true;
				break;
			}
		}

		$this->data['requires_signup_code'] = $requires_signup_code;
		return $requires_signup_code;
	}

	public static function get_instance_from_wp_post( \WP_Post $post ) {
		$type = new self();
		$type->set_up_instance_from_wp_post( $post );

		// Can create courses.
		$can_create_courses_db = get_post_meta( $post->ID, 'cboxol_member_type_can_create_courses', true );
		$can_create_courses    = 'yes' === $can_create_courses_db;
		$type->set_can_create_courses( $can_create_courses );

		// If there's no value, default to false if the slug is 'faculty' or 'staff'.
		// This is to ensure the proper default when upgrading from an earlier version.
		$can_import_group_users_db = get_post_meta( $post->ID, 'cboxol_member_type_can_import_group_users', true );
		if ( ! $can_import_group_users_db ) {
			$can_import_group_users = in_array( $type->get_slug(), [ 'faculty', 'staff' ], true );
		} else {
			$can_import_group_users = 'yes' === $can_import_group_users_db;
		}

		$type->set_can_import_group_users( $can_import_group_users );

		// Selectable types ("Member may change Type to...").
		$selectable_types_db = get_post_meta( $post->ID, 'cboxol_member_type_selectable_types', true );
		$type->set_selectable_types( $selectable_types_db );

		return $type;
	}

	public function get_for_endpoint() {
		// @todo This doesn't need to go in every one.
		$types = cboxol_get_member_types(
			array(
				'enabled' => null,
			)
		);

		$all_types = array_map(
			function( $type ) {
				return array(
					'slug' => $type->get_slug(),
					'name' => $type->get_name(),
					'id'   => $type->get_wp_post_id(),
				);
			},
			$types
		);

		$data = array(
			'id'           => $this->get_wp_post_id(),
			'isCollapsed'  => true,
			'isEnabled'    => $this->get_is_enabled(),
			'isLoading'    => false,
			'isModified'   => false,
			'canBeDeleted' => $this->get_can_be_deleted(),
			'settings'     => array(
				'MayImportGroupUsers'   => array(
					'component' => 'MayImportGroupUsers',
					'data'      => $this->get_can_import_group_users(),
				),
				'MayChangeMemberTypeTo' => array(
					'component' => 'MayChangeMemberTypeTo',
					'data'      => array(
						'selectableTypes' => $this->get_selectable_types(),
						'allTypes'        => $all_types,
					),
				),
				'Order'                 => array(
					'component' => 'Order',
					'data'      => $this->get_order(),
				),
			),
			'name'         => $this->get_name(),
			'slug'         => $this->get_slug(),
			'labels'       => $this->get_labels(),
		);

		$course_group_type = cboxol_get_course_group_type();
		if ( $course_group_type ) {
			$data['settings'] = array(
				'MayCreateCourses' => array(
					'component' => 'MayCreateCourses',
					'data'      => $this->get_can_create_courses(),
				),
			) + $data['settings'];
		}

		return $data;
	}

	public function save() {
		$this->save_to_wp_post();

		$wp_post_id = $this->get_wp_post_id();

		update_post_meta( $wp_post_id, 'cboxol_member_type_selectable_types', $this->get_selectable_types() );

		delete_post_meta( $wp_post_id, 'cboxol_member_type_can_create_courses' );
		if ( $this->get_can_create_courses() ) {
			add_post_meta( $wp_post_id, 'cboxol_member_type_can_create_courses', 'yes' );
		}

		$can_import_group_users = $this->get_can_import_group_users() ? 'yes' : 'no';
		update_post_meta( $wp_post_id, 'cboxol_member_type_can_import_group_users', $can_import_group_users );
	}

	public function set_selectable_types( $types ) {
		if ( ! is_array( $types ) ) {
			$types = array();
		}

		$this->data['selectable_types'] = $types;
	}

	public static function get_dummy() {
		$dummy = new self();

		foreach ( $dummy->get_label_types() as $label_type => $label_labels ) {
			$dummy->set_label( $label_type, '' );
		}

		return $dummy;
	}

	public function get_label_types() {
		return array(
			'singular' => array(
				'value' => '',
			),
			'plural'   => array(
				'value' => '',
			),
		);
	}

	public function get_label_types_info() {
		return array(
			'singular' => array(
				'slug'        => 'singular',
				'label'       => _x( 'Singular', 'Member Type singular label', 'commons-in-a-box' ),
				'description' => __( 'Used wherever a specific member\'s Type is mentioned, such as the User Edit interface.', 'commons-in-a-box' ),
			),
			'plural'   => array(
				'slug'        => 'plural',
				'label'       => _x( 'Plural', 'Member Type plural label', 'commons-in-a-box' ),
				'description' => __( 'Used in directory titles.', 'commons-in-a-box' ),
			),
		);
	}

	/**
	 * Validate a signup code against this member type.
	 *
	 * @param string $code
	 * @return bool|\WP_Error
	 */
	public function validate_signup_code( $code ) {
		$the_code = null;

		// @todo There should be a better way to fetch.
		$signup_codes = cboxol_get_signup_codes();
		foreach ( $signup_codes as $signup_code ) {
			if ( $signup_code->get_code() === $code ) {
				$the_code = $signup_code;
				break;
			}
		}

		if ( ! $the_code ) {
			return new WP_Error( 'signup_code_not_found', __( 'Signup code is incorrect.', 'commons-in-a-box' ), $code );
		}

		if ( $the_code->get_member_type()->get_slug() !== $this->get_slug() ) {
			return new WP_Error( 'signup_code_does_not_match', __( 'Signup code is incorrect.', 'commons-in-a-box' ), $code );
		}

		return true;
	}
}
