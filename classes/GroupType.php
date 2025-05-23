<?php

namespace CBOX\OL;

class GroupType extends ItemTypeBase implements ItemType {
	protected $post_type = 'cboxol_group_type';

	/**
	 * BP item type taxonomy name.
	 *
	 * @since 1.3.0
	 * @var string
	 */
	protected $taxonomy = 'bp_group_type';

	protected $defaults = array(
		'can_be_cloned'                    => false,
		'directory_filters'                => array(),
		'enable_portfolio_list_by_default' => false,
		'enable_site_by_default'           => false,
		'is_course'                        => false,
		'is_portfolio'                     => false,
		'available_privacy_options'        => [ 'public', 'private', 'hidden' ],
		'available_site_privacy_options'   => [ '1', '0', '-1', '-2', '-3' ],
		'default_privacy_option'           => 'public',
		'default_site_privacy_option'      => '1',
		'requires_site'                    => false,
		'supports_course_information'      => false,
		'supports_mol_link'                => false,
		'supports_profile_column'          => false,
		'site_template_id'                 => 0,
	);

	protected $boolean_props = array(
		'can_be_cloned',
		'enable_portfolio_list_by_default',
		'enable_site_by_default',
		'is_course',
		'is_portfolio',
		'requires_site',
		'supports_course_information',
		'supports_mol_link',
		'supports_profile_column',
	);

	public function __construct() {
		$this->taxonomy = bp_get_group_type_tax_name();
		parent::__construct();
	}

	public static function get_instance_from_wp_post( \WP_Post $post ) {
		$type = new self();
		$type->set_up_instance_from_wp_post( $post );

		$type->set_directory_filters( get_post_meta( $post->ID, 'cboxol_group_type_directory_filters', true ) );
		$type->set_site_template_id( get_post_meta( $post->ID, 'cboxol_group_type_site_template_id', true ) );
		$type->set_available_privacy_options( get_post_meta( $post->ID, 'cboxol_group_type_available_privacy_options', true ) );
		$type->set_available_site_privacy_options( get_post_meta( $post->ID, 'cboxol_group_type_available_site_privacy_options', true ) );
		$type->set_default_privacy_option( get_post_meta( $post->ID, 'cboxol_group_type_default_privacy_option', true ) );
		$type->set_default_site_privacy_option( get_post_meta( $post->ID, 'cboxol_group_type_default_site_privacy_option', true ) );

		return $type;
	}

	public function get_for_endpoint() {
		// @todo This doesn't need to go in every one.
		$types = cboxol_get_group_types(
			array(
				'enabled' => null,
			)
		);

		return array(
			'availablePrivacyOptions'     => $this->get_available_privacy_options(),
			'availableSitePrivacyOptions' => $this->get_available_site_privacy_options(),
			'defaultPrivacyOption'        => $this->get_default_privacy_option(),
			'defaultSitePrivacyOption'    => $this->get_default_site_privacy_option(),
			'id'                          => $this->get_wp_post_id(),
			'isCollapsed'                 => true,
			'isCourse'                    => $this->get_is_course(),
			'isPortfolio'                 => $this->get_is_portfolio(),
			'isEnabled'                   => $this->get_is_enabled(),
			'isLoading'                   => false,
			'isModified'                  => false,
			'canBeDeleted'                => $this->get_can_be_deleted(),
			'settings'                    => array(
				'Order' => array(
					'component' => 'Order',
					'data'      => $this->get_order(),
				),
			),
			'name'                        => $this->get_name(),
			'slug'                        => $this->get_slug(),
			'labels'                      => $this->get_labels(),
			'siteTemplates'               => $this->get_site_templates(),
			'siteTemplateId'              => $this->get_site_template_id(),
		);
	}

	public function get_directory_filters() {
		return $this->data['directory_filters'];
	}

	/**
	 * Gets the available privacy options.
	 *
	 * @return array
	 */
	public function get_available_privacy_options() {
		$possible_options = [ 'public', 'private', 'hidden' ];

		$available_privacy_options = $this->data['available_privacy_options'];

		if ( ! is_array( $available_privacy_options ) ) {
			$available_privacy_options = $possible_options;
		}

		return array_intersect( $available_privacy_options, $possible_options );
	}

	/**
	 * Gets the available site privacy options.
	 *
	 * @return array
	 */
	public function get_available_site_privacy_options() {
		$possible_options = [ '1', '0', '-1', '-2', '-3' ];

		$available_privacy_options = $this->data['available_site_privacy_options'];

		if ( ! is_array( $available_privacy_options ) ) {
			$available_privacy_options = $possible_options;
		}

		return array_intersect( $available_privacy_options, $possible_options );
	}

	/**
	 * Gets the default privacy option.
	 *
	 * @return string
	 */
	public function get_default_privacy_option() {
		$available_privacy_options = $this->get_available_privacy_options();

		if ( ! in_array( $this->data['default_privacy_option'], $available_privacy_options, true ) ) {
			return reset( $available_privacy_options );
		}

		return $this->data['default_privacy_option'];
	}

	/**
	 * Gets the default site privacy option.
	 *
	 * @return string
	 */
	public function get_default_site_privacy_option() {
		$available_privacy_options = $this->get_available_site_privacy_options();

		if ( ! in_array( $this->data['default_site_privacy_option'], $available_privacy_options, true ) ) {
			return reset( $available_privacy_options );
		}

		return $this->data['default_site_privacy_option'];
	}

	public function get_template_site_id() {
		return cboxol_get_template_site_id( $this->get_site_template_id() );
	}

	public function get_site_template_id() {
		return (int) $this->data['site_template_id'];
	}

	public function get_site_template_info( $template_id ) {
		$site_id = cboxol_get_template_site_id( $template_id );

		$template = get_post( $template_id );

		if ( ! $site_id || ! $template ) {
			return null;
		}

		return [
			'id'       => $template_id,
			'siteId'   => $site_id,
			'name'     => $template->post_title,
			'url'      => get_home_url( $site_id ),
			'adminUrl' => get_admin_url( $site_id ),
		];
	}

	public function get_site_template_categories() {
		return get_terms(
			[
				'taxonomy'   => 'cboxol_template_category',
				'number'     => 0,
				'hide_empty' => false,
				'meta_query' => [
					[
						'key'   => 'cboxol_group_type',
						'value' => $this->get_slug(),
					],
				],
			]
		);
	}

	public function get_site_templates() {
		$site_template_categories = $this->get_site_template_categories();
		if ( $site_template_categories ) {
			$category_ids = wp_list_pluck( $site_template_categories, 'term_id' );
		} else {
			$category_ids = [ 0 ];
		}

		$site_template_posts = get_posts(
			[
				'post_type'      => 'cboxol_site_template',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'name',
				'tax_query'      => [
					[
						'taxonomy' => 'cboxol_template_category',
						'terms'    => $category_ids,
						'field'    => 'term_id',
					],
				],
			]
		);

		$this_object = $this;

		$site_templates = array_map(
			function( $template ) use ( $this_object ) {
				return $this_object->get_site_template_info( $template->ID );
			},
			$site_template_posts
		);

		/*
		 * Special case: If the current template is not in an associated category
		 * (ie it was unlinked somehow) it should be included in the list.
		 */
		$list_has_linked_template = false;
		$linked_site_template_id  = $this->get_site_template_id();
		foreach ( $site_templates as $site_template ) {
			if ( $linked_site_template_id === $site_template['id'] ) {
				$list_has_linked_template = true;
				break;
			}
		}

		if ( ! $list_has_linked_template ) {
			$site_templates[] = $this->get_site_template_info( $linked_site_template_id );
		}

		$site_templates = array_filter( $site_templates );

		return $site_templates;
	}

	/**
	 * Gets the group type labels.
	 *
	 * Overridden here because we filter based on group type. We also fall back on
	 * default values for default group types, if available.
	 *
	 * @return array
	 */
	public function get_labels() {
		$map = array(
			'course'    => array(
				'singular',
				'plural',
				'create_clone_item',
				'item_creation',
				'create_item_help_text',
				'name_help_text',
				'avatar_help_text',
				'avatar_help_text_cant_decide',
				'url_help_text',
				'privacy_help_text',
				'privacy_help_text_new',
				'privacy_help_text_public_content',
				'privacy_help_text_public_directory',
				'privacy_help_text_public_membership',
				'privacy_help_text_private_content',
				'privacy_help_text_byrequest_membership',
				'privacy_help_text_private_directory',
				'privacy_help_text_invited_membership',
				'allow_joining_private_label',
				'allow_joining_public_label',
				'privacy_membership_settings_private',
				'privacy_membership_settings_public',
				'group_details',
				'my_groups',
				'course_information',
				'course_information_description',
				'course_code',
				'section_code',
				'group_site',
				'status_open',
				'status_open_community_site',
				'status_open_private_site',
				'status_private',
				'status_private_community_site',
				'status_private_open_site',
				'status_private_private_site',
				'site_help_text',
				'site_address_help_text',
				'site_feed_check_help_text',
				'visit_group_site',
				'group_home',
				'settings_help_text_discussion',
				'settings_help_text_calendar',
				'settings_help_text_calendar_members',
				'settings_help_text_calendar_admins',
				'settings_help_text_relatedlinks',
				'settings_help_text_portfoliolist',
				'settings_help_text_sharing',
				'invite_members_to_group',
				'invite_community_members_to_group',
				'search_for_members_to_invite_to_group',
				'group_contact',
				'group_contact_help_text',
				'group_discussion',
				'clone_credits_widget_description',
				'shareable_content_widget_description',
				'clone_this_group',
			),
			'portfolio' => array(
				'singular',
				'plural',
				'create_item',
				'create_clone_item',
				'item_creation',
				'create_item_help_text',
				'name_help_text',
				'avatar_help_text',
				'avatar_help_text_cant_decide',
				'url_help_text',
				'privacy_help_text',
				'privacy_help_text_new',
				'privacy_help_text_public_content',
				'privacy_help_text_public_directory',
				'privacy_help_text_public_membership',
				'privacy_help_text_private_content',
				'privacy_help_text_byrequest_membership',
				'privacy_help_text_private_directory',
				'privacy_help_text_invited_membership',
				'allow_joining_private_label',
				'allow_joining_public_label',
				'privacy_membership_settings_private',
				'privacy_membership_settings_public',
				'group_details',
				'my_portfolio',
				'my_portfolio_site',
				'visit_portfolio_site',
				'group_site',
				'status_open',
				'status_open_community_site',
				'status_open_private_site',
				'status_private',
				'status_private_community_site',
				'status_private_open_site',
				'status_private_private_site',
				'site_help_text',
				'site_address_help_text',
				'site_feed_check_help_text',
				'visit_group_site',
				'group_home',
				'settings_help_text_relatedlinks',
				'settings_help_text_add_to_portfolio',
				'invite_members_to_group',
				'invite_community_members_to_group',
				'search_for_members_to_invite_to_group',
				'group_contact',
				'group_contact_help_text',
				'group_discussion',
				'clone_credits_widget_description',
				'shareable_content_widget_description',
				'clone_this_group',
				'add_to_portfolio',
				'added_to_my_portfolio',
			),
			'default'   => array(
				'singular',
				'plural',
				'create_clone_item',
				'create_item_help_text',
				'item_creation',
				'name_help_text',
				'avatar_help_text',
				'avatar_help_text_cant_decide',
				'url_help_text',
				'privacy_help_text',
				'privacy_help_text_new',
				'privacy_help_text_public_content',
				'privacy_help_text_public_directory',
				'privacy_help_text_public_membership',
				'privacy_help_text_private_content',
				'privacy_help_text_byrequest_membership',
				'privacy_help_text_private_directory',
				'privacy_help_text_invited_membership',
				'allow_joining_private_label',
				'allow_joining_public_label',
				'privacy_membership_settings_private',
				'privacy_membership_settings_public',
				'group_details',
				'my_groups',
				'group_site',
				'status_open',
				'status_open_community_site',
				'status_open_private_site',
				'status_private',
				'status_private_community_site',
				'status_private_open_site',
				'status_private_private_site',
				'site_help_text',
				'site_address_help_text',
				'site_feed_check_help_text',
				'visit_group_site',
				'group_home',
				'settings_help_text_discussion',
				'settings_help_text_calendar',
				'settings_help_text_calendar_members',
				'settings_help_text_calendar_admins',
				'settings_help_text_relatedlinks',
				'settings_help_text_portfoliolist',
				'settings_help_text_sharing',
				'invite_members_to_group',
				'invite_community_members_to_group',
				'search_for_members_to_invite_to_group',
				'group_contact',
				'group_contact_help_text',
				'group_discussion',
				'clone_credits_widget_description',
				'shareable_content_widget_description',
				'clone_this_group',
			),
		);

		if ( $this->get_is_course() ) {
			$type_labels = $map['course'];
		} elseif ( $this->get_is_portfolio() ) {
			$type_labels = $map['portfolio'];
		} else {
			$type_labels = $map['default'];
		}

		$retval     = array();
		$label_info = $this->get_label_types_info();

		$default_labels_for_group_type = self::get_group_type_default_labels( $this->get_slug() );

		foreach ( $label_info as $label_slug => $label_data ) {
			if ( ! in_array( $label_slug, $type_labels, true ) ) {
				continue;
			}

			// Value - prefer stored value, fall back on default for this group type.
			$label_value = '';
			if ( isset( $this->data['labels'][ $label_slug ] ) ) {
				$label_value = $this->data['labels'][ $label_slug ];
			} elseif ( isset( $default_labels_for_group_type[ $label_slug ] ) ) {
				$label_value = $default_labels_for_group_type[ $label_slug ];
			}

			$retval[ $label_slug ] = array(
				'slug'        => $label_slug,
				'label'       => $label_data['label'],
				'description' => $label_data['description'],
				'value'       => $label_value,
			);
		}

		// Sort based on the order in the array above.
		$sorted = array();
		foreach ( $type_labels as $value ) {
			if ( ! empty( $retval[ $value ] ) ) {
				$sorted[ $value ] = $retval[ $value ];
			}
		}

		return $sorted;
	}

	public function save() {
		$this->save_to_wp_post();

		$wp_post_id = $this->get_wp_post_id();

		update_post_meta( $wp_post_id, 'cboxol_group_type_directory_filters', $this->get_directory_filters() );
		update_post_meta( $wp_post_id, 'cboxol_group_type_site_template_id', $this->get_site_template_id() );
		update_post_meta( $wp_post_id, 'cboxol_group_type_available_privacy_options', $this->get_available_privacy_options() );
		update_post_meta( $wp_post_id, 'cboxol_group_type_available_site_privacy_options', $this->get_available_site_privacy_options() );
		update_post_meta( $wp_post_id, 'cboxol_group_type_default_privacy_option', $this->get_default_privacy_option() );
		update_post_meta( $wp_post_id, 'cboxol_group_type_default_site_privacy_option', $this->get_default_site_privacy_option() );
	}

	public static function get_dummy() {
		$dummy = new self();

		foreach ( $dummy->get_label_types_info() as $label_type => $label_labels ) {
			$label_labels['value'] = '';
			$dummy->set_label( $label_type, $label_labels );
		}

		return $dummy;
	}

	/**
	 * Reminder on how to add a new label type:
	 * 1. Add entry here and in get_label_types_info()
	 * 2. Add slug to each group type above
	 * 3. Add default values to classes/Install
	 * 4. Run wp cboxol reset
	 */
	public function get_label_types() {
		return array(
			'allow_joining_private_label'            => array(
				'value' => '',
			),
			'allow_joining_public_label'             => array(
				'value' => '',
			),
			'course_code'                            => array(
				'value' => '',
			),
			'course_information'                     => array(
				'value' => '',
			),
			'course_information_description'         => array(
				'value' => '',
			),
			'create_item'                            => array(
				'value' => '',
			),
			'create_clone_item'                      => array(
				'value' => '',
			),
			'item_creation'                          => array(
				'value' => '',
			),
			'create_item_help_text'                  => array(
				'value' => '',
			),
			'name_help_text'                         => array(
				'value' => '',
			),
			'avatar_help_text'                       => array(
				'value' => '',
			),
			'avatar_help_text_cant_decide'           => array(
				'value' => '',
			),
			'url_help_text'                          => array(
				'value' => '',
			),
			'privacy_help_text'                      => array(
				'value' => '',
			),
			'privacy_help_text_new'                  => array(
				'value' => '',
			),
			'privacy_help_text_public_content'       => array(
				'value' => '',
			),
			'privacy_help_text_public_directory'     => array(
				'value' => '',
			),
			'privacy_help_text_public_membership'    => array(
				'value' => '',
			),
			'privacy_help_text_private_content'      => array(
				'value' => '',
			),
			'privacy_help_text_byrequest_membership' => array(
				'value' => '',
			),
			'privacy_help_text_private_directory'    => array(
				'value' => '',
			),
			'privacy_help_text_invited_membership'   => array(
				'value' => '',
			),
			'privacy_membership_settings_private'    => array(
				'value' => '',
			),
			'privacy_membership_settings_public'     => array(
				'value' => '',
			),
			'group_home'                             => array(
				'value' => '',
			),
			'group_site'                             => array(
				'value' => '',
			),
			'group_details'                          => array(
				'value' => '',
			),
			'my_groups'                              => array(
				'value' => '',
			),
			'my_portfolio'                           => array(
				'value' => '',
			),
			'my_portfolio_site'                      => array(
				'value' => '',
			),
			'plural'                                 => array(
				'value' => '',
			),
			'section_code'                           => array(
				'value' => '',
			),
			'singular'                               => array(
				'value' => '',
			),
			'status_open'                            => array(
				'value' => '',
			),
			'status_open_community_site'             => array(
				'value' => '',
			),
			'status_open_private_site'               => array(
				'value' => '',
			),
			'status_private'                         => array(
				'value' => '',
			),
			'status_private_community_site'          => array(
				'value' => '',
			),
			'status_private_open_site'               => array(
				'value' => '',
			),
			'status_private_private_site'            => array(
				'value' => '',
			),
			'visit_group_site'                       => array(
				'value' => '',
			),
			'site_help_text'                         => array(
				'value' => '',
			),
			'site_address_help_text'                 => array(
				'value' => '',
			),
			'site_feed_check_help_text'              => array(
				'value' => '',
			),
			'visit_portfolio_site'                   => array(
				'value' => '',
			),
			'settings_help_text_discussion'          => array(
				'value' => '',
			),
			'settings_help_text_calendar'            => array(
				'value' => '',
			),
			'settings_help_text_calendar_members'    => array(
				'value' => '',
			),
			'settings_help_text_calendar_admins'     => array(
				'value' => '',
			),
			'settings_help_text_relatedlinks'        => array(
				'value' => '',
			),
			'settings_help_text_portfoliolist'       => array(
				'value' => '',
			),
			'settings_help_text_sharing'             => array(
				'value' => '',
			),
			'settings_help_text_add_to_portfolio'    => array(
				'value' => '',
			),
			'invite_members_to_group'                => array(
				'value' => '',
			),
			'invite_community_members_to_group'      => array(
				'value' => '',
			),
			'search_for_members_to_invite_to_group'  => array(
				'value' => '',
			),
			'group_contact'                          => array(
				'value' => '',
			),
			'group_contact_help_text'                => array(
				'value' => '',
			),
			'group_discussion'                       => array(
				'value' => '',
			),
			'clone_credits_widget_description'       => array(
				'value' => '',
			),
			'shareable_content_widget_description'   => array(
				'value' => '',
			),
			'clone_this_group'                       => array(
				'value' => '',
			),
			'add_to_portfolio'                       => array(
				'value' => '',
			),
			'added_to_my_portfolio'                  => array(
				'value' => '',
			),
		);
	}

	public function get_label_types_info() {
		return array(
			'allow_joining_private_label'            => array(
				'slug'        => 'allow_joining_private_label',
				'label'       => __( 'Allow Joining Private Label', 'commons-in-a-box' ),
				'description' => __( 'The label for the "Allow Joining Private" checkbox in Membership Settings.', 'commons-in-a-box' ),
			),
			'allow_joining_public_label'             => array(
				'slug'        => 'allow_joining_public_label',
				'label'       => __( 'Allow Joining Public Label', 'commons-in-a-box' ),
				'description' => __( 'The label for the "Allow Joining Public" checkbox in Membership Settings.', 'commons-in-a-box' ),
			),
			'course_code'                            => array(
				'slug'        => 'course_code',
				'label'       => _x( 'Course Code', 'Group Type label', 'commons-in-a-box' ),
				'description' => __( 'The label for the "Course Code" input when editing Course settings.', 'commons-in-a-box' ),
			),
			'course_information'                     => array(
				'slug'        => 'course_information',
				'label'       => __( 'Course Information', 'commons-in-a-box' ),
				'description' => __( 'The label for the course settings section containing Course Code and other catalog data.', 'commons-in-a-box' ),
			),
			'course_information_description'         => array(
				'slug'        => 'course_information_description',
				'label'       => __( 'Course Information Help Text', 'commons-in-a-box' ),
				'description' => __( 'The helper text in the Course Information admin section of a Course.', 'commons-in-a-box' ),
			),
			'create_item'                            => array(
				'slug'        => 'create_item',
				'label'       => __( 'Create Item', 'commons-in-a-box' ),
				'description' => __( 'The text used for "Create" links.', 'commons-in-a-box' ),
			),
			'create_clone_item'                      => array(
				'slug'        => 'create_clone_item',
				'label'       => __( 'Create/Clone Item', 'commons-in-a-box' ),
				'description' => __( 'The text used for "Create/Clone" links.', 'commons-in-a-box' ),
			),
			'item_creation'                          => array(
				'slug'        => 'item_creation',
				'label'       => __( 'Item Creation', 'commons-in-a-box' ),
				'description' => __( 'The label used for the first step of the creation/edit process.', 'commons-in-a-box' ),
			),
			'create_item_help_text'                  => array(
				'slug'        => 'create_item_help_text',
				'label'       => __( 'Creation Explanatory Text', 'commons-in-a-box' ),
				'description' => __( 'Displayed near the top of the creation screen.', 'commons-in-a-box' ),
			),
			'name_help_text'                         => array(
				'slug'        => 'name_help_text',
				'label'       => __( 'Name Help Text', 'commons-in-a-box' ),
				'description' => __( 'Used to clarify the "Name" field when creating or editing an item.', 'commons-in-a-box' ),
			),
			'avatar_help_text'                       => array(
				'slug'        => 'avatar_help_text',
				'label'       => __( 'Avatar Help Text', 'commons-in-a-box' ),
				'description' => __( 'Used to clarify the "Upload Avatar" field when creating or editing an item.', 'commons-in-a-box' ),
			),
			'avatar_help_text_cant_decide'           => array(
				'slug'        => 'avatar_help_text_cant_decide',
				'label'       => __( 'Avatar Help Text - "Can\'t Decide"', 'commons-in-a-box' ),
				'description' => __( 'Used below the avatar selection panel when creating or editing an item.', 'commons-in-a-box' ),
			),
			'url_help_text'                          => array(
				'slug'        => 'url_help_text',
				'label'       => __( 'URL Help Text', 'commons-in-a-box' ),
				'description' => __( 'Used to clarify the "URL" field when creating or editing an item.', 'commons-in-a-box' ),
			),
			'privacy_help_text'                      => array(
				'slug'        => 'privacy_help_text',
				'label'       => __( 'Privacy Help Text', 'commons-in-a-box' ),
				'description' => __( 'Describes group privacy settings when creating or editing a group.', 'commons-in-a-box' ),
			),
			'privacy_help_text_new'                  => array(
				'slug'        => 'privacy_help_text_new',
				'label'       => __( 'Privacy Help Text - New Group', 'commons-in-a-box' ),
				'description' => __( 'Provides additional context for privacy settings when creating a new group.', 'commons-in-a-box' ),
			),
			'privacy_help_text_public_content'       => array(
				'slug'        => 'privacy_help_text_public_content',
				'label'       => __( 'Privacy Help Text - Public Content', 'commons-in-a-box' ),
				'description' => __( 'Describes what "Public" means for content visibility during group creation or editing.', 'commons-in-a-box' ),
			),
			'privacy_help_text_public_directory'     => array(
				'slug'        => 'privacy_help_text_public_directory',
				'label'       => __( 'Privacy Help Text - Public Directory', 'commons-in-a-box' ),
				'description' => __( 'Describes what "Public" means for visibility in directories during group creation or editing.', 'commons-in-a-box' ),
			),
			'privacy_help_text_public_membership'    => array(
				'slug'        => 'privacy_help_text_public_membership',
				'label'       => __( 'Privacy Help Text - Public Membership', 'commons-in-a-box' ),
				'description' => __( 'Describes how "Public" affects community members\' ability to join the group during group creation or editing.', 'commons-in-a-box' ),
			),
			'privacy_help_text_private_content'      => array(
				'slug'        => 'privacy_help_text_private_content',
				'label'       => __( 'Privacy Help Text - Private Content', 'commons-in-a-box' ),
				'description' => __( 'Describes group content that is limited to group members.', 'commons-in-a-box' ),
			),
			'privacy_help_text_byrequest_membership' => array(
				'slug'        => 'privacy_help_text_byrequest_membership',
				'label'       => __( 'Privacy Help Text - Membership By Request', 'commons-in-a-box' ),
				'description' => __( 'Describes membership requirements for groups that allow for membership requests.', 'commons-in-a-box' ),
			),
			'privacy_help_text_private_directory'    => array(
				'slug'        => 'privacy_help_text_private_directory',
				'label'       => __( 'Privacy Help Text - Private Directory', 'commons-in-a-box' ),
				'description' => __( 'Describes groups that are hidden from directories and search results.', 'commons-in-a-box' ),
			),
			'privacy_help_text_invited_membership'   => array(
				'slug'        => 'privacy_help_text_invited_membership',
				'label'       => __( 'Privacy Help Text - Membership By Invitation', 'commons-in-a-box' ),
				'description' => __( 'Describes membership requirements for groups can only be joined by invitation.', 'commons-in-a-box' ),
			),
			'privacy_membership_settings_private'    => array(
				'slug'        => 'privacy_membership_settings_private',
				'label'       => __( 'Privacy Membership Settings: Private', 'commons-in-a-box' ),
				'description' => __( 'Describes the "Private" option for membership settings.', 'commons-in-a-box' ),
			),
			'privacy_membership_settings_public'     => array(
				'slug'        => 'privacy_membership_settings_public',
				'label'       => __( 'Privacy Membership Settings: Public', 'commons-in-a-box' ),
				'description' => __( 'Describes the "Public" option for membership settings.', 'commons-in-a-box' ),
			),
			'group_home'                             => array(
				'slug'        => 'group_home',
				'label'       => __( 'Group Home', 'commons-in-a-box' ),
				'description' => __( 'Used to create a Home link in a group\'s nav menus.', 'commons-in-a-box' ),
			),
			'group_site'                             => array(
				'slug'        => 'group_site',
				'label'       => __( 'Group Site', 'commons-in-a-box' ),
				'description' => __( 'Used in group directories and elsewhere to create links to the group\'s site.', 'commons-in-a-box' ),
			),
			'group_details'                          => array(
				'slug'        => 'group_details',
				'label'       => __( 'Group Details', 'commons-in-a-box' ),
				'description' => __( 'Used in group admin navigation.', 'commons-in-a-box' ),
			),
			'my_groups'                              => array(
				'slug'        => 'my_groups',
				'label'       => _x( 'My Groups', 'Group Type label', 'commons-in-a-box' ),
				'description' => __( 'Used in personal navigation and on member profiles.', 'commons-in-a-box' ),
			),
			'my_portfolio'                           => array(
				'slug'        => 'my_portfolio',
				'label'       => _x( 'My Portfolio', 'Group Type label', 'commons-in-a-box' ),
				'description' => __( 'Used in personal navigation and on member profiles.', 'commons-in-a-box' ),
			),
			'my_portfolio_site'                      => array(
				'slug'        => 'my_portfolio_site',
				'label'       => _x( 'My Portfolio Site', 'Group Type label', 'commons-in-a-box' ),
				'description' => __( 'Used as the link to a user\'s own portfolio site.', 'commons-in-a-box' ),
			),
			'plural'                                 => array(
				'slug'        => 'plural',
				'label'       => _x( 'Plural', 'Member Type plural label', 'commons-in-a-box' ),
				'description' => __( 'Used in directory titles.', 'commons-in-a-box' ),
			),
			'section_code'                           => array(
				'slug'        => 'section_code',
				'label'       => _x( 'Section Code', 'Group Type label', 'commons-in-a-box' ),
				'description' => __( 'The label for the "Section Code" input when editing Course settings.', 'commons-in-a-box' ),
			),
			'singular'                               => array(
				'slug'        => 'singular',
				'label'       => _x( 'Singular', 'Member Type singular label', 'commons-in-a-box' ),
				'description' => __( 'Used wherever a specific member\'s Type is mentioned, such as the User Edit interface.', 'commons-in-a-box' ),
			),
			'status_open'                            => array(
				'slug'        => 'status_open',
				'label'       => _x( 'Status: Open', 'Group Type label', 'commons-in-a-box' ),
				'description' => __( 'Used to describe a group that is open and either has no site or has a site that is also open.', 'commons-in-a-box' ),
			),
			'status_open_community_site'             => array(
				'slug'        => 'status_open_community_site',
				'label'       => _x( 'Status: Open, Community Site', 'Group Type label', 'commons-in-a-box' ),
				'description' => __( 'Used to describe a group that is open and has a site that is visible only to community members.', 'commons-in-a-box' ),
			),
			'status_open_private_site'               => array(
				'slug'        => 'status_open_private_site',
				'label'       => _x( 'Status: Open, Private Site', 'Group Type label', 'commons-in-a-box' ),
				'description' => __( 'Used to describe a group that is open and has a site that is private.', 'commons-in-a-box' ),
			),
			'status_private'                         => array(
				'slug'        => 'status_private',
				'label'       => _x( 'Status: Private', 'Group Type label', 'commons-in-a-box' ),
				'description' => __( 'Used to describe a group that is private and has no site.', 'commons-in-a-box' ),
			),
			'status_private_community_site'          => array(
				'slug'        => 'status_private_community_site',
				'label'       => _x( 'Status: Private, Community Site', 'Group Type label', 'commons-in-a-box' ),
				'description' => __( 'Used to describe a group that is private and has a site that is visible only to community members.', 'commons-in-a-box' ),
			),
			'status_private_open_site'               => array(
				'slug'        => 'status_private_open_site',
				'label'       => _x( 'Status: Private, Open Site', 'Group Type label', 'commons-in-a-box' ),
				'description' => __( 'Used to describe a group that is private and has an open site.', 'commons-in-a-box' ),
			),
			'status_private_private_site'            => array(
				'slug'        => 'status_private_private_site',
				'label'       => _x( 'Status: Private, Private Site', 'Group Type label', 'commons-in-a-box' ),
				'description' => __( 'Used to describe a group that is private and has a site that is private.', 'commons-in-a-box' ),
			),
			'visit_group_site'                       => array(
				'slug'        => 'visit_group_site',
				'label'       => __( 'Visit Group Site', 'commons-in-a-box' ),
				'description' => __( 'Used in group navigation and elsewhere to create links to the group\'s site.', 'commons-in-a-box' ),
			),
			'site_help_text'                         => array(
				'slug'        => 'site_help_text',
				'label'       => __( 'Site Help Text', 'commons-in-a-box' ),
				'description' => __( 'Help text displayed at the top of the Associated Site section of group edit/creation.', 'commons-in-a-box' ),
			),
			'site_address_help_text'                 => array(
				'slug'        => 'site_address_help_text',
				'label'       => __( 'Site Address Help Text', 'commons-in-a-box' ),
				'description' => __( 'Text describing the choice of URL when creating a group site.', 'commons-in-a-box' ),
			),
			'site_feed_check_help_text'              => array(
				'slug'        => 'site_feed_check_help_text',
				'label'       => __( 'Site Feed Check Help Text', 'commons-in-a-box' ),
				'description' => __( 'Text describing the "Check" button for external feeds when creating a group site.', 'commons-in-a-box' ),
			),
			'visit_portfolio_site'                   => array(
				'slug'        => 'visit_portfolio_site',
				'label'       => _x( 'Visit Portfolio Site', 'Group Type label', 'commons-in-a-box' ),
				'description' => __( 'Used as the link to another user\'s portfolio site.', 'commons-in-a-box' ),
			),
			'settings_help_text_discussion'          => array(
				'slug'        => 'settings_help_text_discussion',
				'label'       => __( 'Settings Help Text - Discussion', 'commons-in-a-box' ),
				'description' => __( 'Help text for the Discussion Settings panel.', 'commons-in-a-box' ),
			),
			'settings_help_text_calendar'            => array(
				'slug'        => 'settings_help_text_calendar',
				'label'       => __( 'Settings Help Text - Calendar', 'commons-in-a-box' ),
				'description' => __( 'Help text for the Calendar Settings panel.', 'commons-in-a-box' ),
			),
			'settings_help_text_calendar_members'    => array(
				'slug'        => 'settings_help_text_calendar_members',
				'label'       => __( 'Settings Help Text - Calendar, Members Only', 'commons-in-a-box' ),
				'description' => __( 'Help text for the "Members Only" option on the Calendar Settings panel.', 'commons-in-a-box' ),
			),
			'settings_help_text_calendar_admins'     => array(
				'slug'        => 'settings_help_text_calendar_admins',
				'label'       => __( 'Settings Help Text - Calendar, Admins Only', 'commons-in-a-box' ),
				'description' => __( 'Help text for the "Admins and Mods Only" option on the Calendar Settings panel.', 'commons-in-a-box' ),
			),
			'settings_help_text_relatedlinks'        => array(
				'slug'        => 'settings_help_text_relatedlinks',
				'label'       => __( 'Settings Help Text - Related Links', 'commons-in-a-box' ),
				'description' => __( 'Help text for the Related Links List Settings panel.', 'commons-in-a-box' ),
			),
			'settings_help_text_portfoliolist'       => array(
				'slug'        => 'settings_help_text_portfoliolist',
				'label'       => __( 'Settings Help Text - Portfolio List', 'commons-in-a-box' ),
				'description' => __( 'Help text for the Portfolio List Settings panel.', 'commons-in-a-box' ),
			),
			'settings_help_text_sharing'             => array(
				'slug'        => 'settings_help_text_sharing',
				'label'       => __( 'Settings Help Text - Sharing', 'commons-in-a-box' ),
				'description' => __( 'Help text for the Sharing Settings panel.', 'commons-in-a-box' ),
			),
			'settings_help_text_add_to_portfolio'    => array(
				'slug'        => 'settings_help_text_add_to_portfolio',
				'label'       => __( 'Settings Help Text - Add to Portfolio', 'commons-in-a-box' ),
				'description' => __( 'Help text for the Add to Portfolio Settings panel.', 'commons-in-a-box' ),
			),
			'invite_members_to_group'                => array(
				'slug'        => 'invite_members_to_group',
				'label'       => __( 'Invite Members To Group', 'commons-in-a-box' ),
				'description' => __( 'Used in group invitation navigation.', 'commons-in-a-box' ),
			),
			'invite_community_members_to_group'      => array(
				'slug'        => 'invite_community_members_to_group',
				'label'       => __( 'Invite Community Members To Group', 'commons-in-a-box' ),
				'description' => __( 'Used as a header on group creation/settings panel.', 'commons-in-a-box' ),
			),
			'search_for_members_to_invite_to_group'  => array(
				'slug'        => 'search_for_members_to_invite_to_group',
				'label'       => __( 'Search for Community Members to Invite to Group', 'commons-in-a-box' ),
				'description' => __( 'Used as help text when inviting community members to a group.', 'commons-in-a-box' ),
			),
			'group_contact'                          => array(
				'slug'        => 'group_contact',
				'label'       => __( 'Group Contact', 'commons-in-a-box' ),
				'description' => __( 'The label for the Group Contact feature.', 'commons-in-a-box' ),
			),
			'group_contact_help_text'                => array(
				'slug'        => 'group_contact_help_text',
				'label'       => __( 'Group Contact Help Text', 'commons-in-a-box' ),
				'description' => __( 'Help text for the Group Contact feature.', 'commons-in-a-box' ),
			),
			'group_discussion'                       => array(
				'slug'        => 'group_discussion',
				'label'       => __( 'Group Discussion', 'commons-in-a-box' ),
				'description' => __( 'Used for forum navigation.', 'commons-in-a-box' ),
			),
			'clone_credits_widget_description'       => array(
				'slug'        => 'clone_credits_widget_description',
				'label'       => __( 'Clone Credits Widget Description', 'commons-in-a-box' ),
				'description' => __( 'Describes the Clone Credits widget.', 'commons-in-a-box' ),
			),
			'shareable_content_widget_description'   => array(
				'slug'        => 'shareable_content_widget_description',
				'label'       => __( 'Shareable Content Widget Description', 'commons-in-a-box' ),
				'description' => __( 'Describes the Shareable Content widget.', 'commons-in-a-box' ),
			),
			'clone_this_group'                       => array(
				'slug'        => 'clone_this_group',
				'label'       => __( 'Clone This Group', 'commons-in-a-box' ),
				'description' => __( 'Used in the Shareable Content widget.', 'commons-in-a-box' ),
			),
			'add_to_portfolio'                       => array(
				'slug'        => 'add_to_portfolio',
				'label'       => __( 'Add to Portfolio', 'commons-in-a-box' ),
				'description' => __( 'Used in the "Add to Portfolio" dialog on sites.', 'commons-in-a-box' ),
			),
			'added_to_my_portfolio'                  => array(
				'slug'        => 'added_to_my_portfolio',
				'label'       => __( 'Added to my Portfolio', 'commons-in-a-box' ),
				'description' => __( 'Used in the "Add to Portfolio" dialog on sites.', 'commons-in-a-box' ),
			),
		);
	}

	public function set_directory_filters( $directory_filters ) {
		$this->data['directory_filters'] = $directory_filters;
	}

	/**
	 * Sets the available privacy options.
	 *
	 * @param array $available_privacy_options
	 * @return void
	 */
	public function set_available_privacy_options( $available_privacy_options ) {
		$this->data['available_privacy_options'] = $available_privacy_options;
	}

	/**
	 * Sets the available site privacy options.
	 *
	 * @param array $available_site_privacy_options
	 * @return void
	 */
	public function set_available_site_privacy_options( $available_site_privacy_options ) {
		$this->data['available_site_privacy_options'] = $available_site_privacy_options;
	}

	/**
	 * Sets the default privacy option.
	 *
	 * @param string $default_privacy_option
	 * @return void
	 */
	public function set_default_privacy_option( $default_privacy_option ) {
		$this->data['default_privacy_option'] = $default_privacy_option;
	}

	/**
	 * Sets the default site privacy option.
	 *
	 * @param string $default_site_privacy_option
	 * @return void
	 */
	public function set_default_site_privacy_option( $default_site_privacy_option ) {
		$this->data['default_site_privacy_option'] = $default_site_privacy_option;
	}

	/**
	 * Deprecated.
	 *
	 * @deprecated 1.4.0
	 */
	public function set_template_site_id( $template_site_id ) {
		_deprecated_function( __METHOD__, '1.4.0', 'GroupType::get_site_template_id()' );
		return null;
	}

	/**
	 * Sets the site template ID for this group type.
	 *
	 * @param int $site_template_id
	 */
	public function set_site_template_id( $site_template_id ) {
		$this->data['site_template_id'] = (int) $site_template_id;
	}

	public function create_template_site( $settings ) {
		$template_id = wp_insert_post(
			[
				'post_type'   => 'cboxol_site_template',
				// translators: Group type label.
				'post_title'  => sprintf( __( 'Site Template - %s', 'commons-in-a-box' ), $this->get_name() ),
				'post_status' => 'publish',
			]
		);

		if ( ! $template_id || is_wp_error( $template_id ) ) {
			return false;
		}

		$template_post = get_post( $template_id );

		$slug = sanitize_title_with_dashes( _x( 'site-template', 'Prefix for template site URL slug', 'commons-in-a-box' ) ) . '-' . $this->get_slug() . '-' . time();

		// translators: Group type label
		$name = sprintf( __( 'Site Template - %s', 'commons-in-a-box' ), $this->get_name() );

		$site_id = cboxol_create_site_for_template( $template_id, $slug, $name );

		// Try to use an existing category.
		$template_categories = get_terms(
			[
				'taxonomy'   => 'cboxol_template_category',
				'hide_empty' => false,
			]
		);

		$the_category_id = null;
		if ( $template_categories ) {
			foreach ( $template_categories as $template_category ) {
				$category_group_types = cboxol_get_term_group_types( $template_category->term_id );
				if ( in_array( $this->get_slug(), $category_group_types, true ) ) {
					$the_category_id = $template_category->term_id;
					break;
				}
			}
		}

		// No category exists, so we create one.
		if ( ! $the_category_id ) {
			// translators: Group type label
			$term_name = sprintf( __( 'General: %s', 'commons-in-a-box' ), $this->get_label( 'plural' ) );

			$inserted = wp_insert_term( $term_name, 'cboxol_template_category' );

			if ( ! is_wp_error( $inserted ) && ! empty( $inserted['term_id'] ) ) {
				$the_category_id = $inserted['term_id'];
			}
		}

		if ( $the_category_id ) {
			add_term_meta( $the_category_id, 'cboxol_group_type', $this->get_slug() );
			wp_set_post_terms( $template_id, [ $the_category_id ], 'cboxol_template_category' );
		}

		if ( ! $site_id ) {
			return;
		}

		switch_to_blog( $site_id );

		if ( ! empty( $settings['theme'] ) ) {
			switch_theme( $settings['theme'] );
		}

		// Update text of default post.
		wp_update_post(
			array(
				'ID'           => 1,
				'post_content' => __( 'Welcome! This is your first post. Edit or delete it, then start blogging!', 'commons-in-a-box' ),
			)
		);

		// Create pages. Ensure that children come after parents.
		$created_page_ids = array();
		if ( ! empty( $settings['pages'] ) ) {
			$created_page_ids = array_fill_keys( array_keys( $settings['pages'] ), 0 );

			foreach ( $settings['pages'] as $page_slug => $page ) {
				$post_parent = 0;
				if ( ! empty( $page['parent'] ) ) {
					$parent_slug = $page['parent'];
					$post_parent = $created_page_ids[ $parent_slug ];
				}

				$page_id = wp_insert_post(
					array(
						'post_type'    => 'page',
						'post_content' => $page['content'],
						'post_title'   => $page['title'],
						'menu_order'   => $page['order'],
						'post_parent'  => $post_parent,
						'post_status'  => 'publish',
					)
				);

				if ( $page_id && ! is_wp_error( $page_id ) ) {
					$created_page_ids[ $page_slug ] = $page_id;
				}
			}
		}

		// Try to place the newly create pages in the main menu.
		if ( $created_page_ids ) {
			$nav_menu_ids = [];

			$locations       = get_nav_menu_locations();
			$primary_nav_key = cboxol_get_theme_primary_nav_menu_location();

			if ( ! empty( $locations[ $primary_nav_key ] ) ) {
				$menu_id = $locations[ $primary_nav_key ];

				foreach ( $created_page_ids as $page_slug => $created_page_id ) {
					$page = get_post( $created_page_id );

					$parent_nav_item_id = 0;
					if ( ! empty( $page->post_parent ) && isset( $nav_menu_ids[ $page->post_parent ] ) ) {
						$parent_nav_item_id = $nav_menu_ids[ $page->post_parent ];
					}

					$nav_menu_item_id = wp_update_nav_menu_item(
						$menu_id,
						0,
						array(
							'menu-item-object-id' => $created_page_id,
							'menu-item-object'    => 'page',
							'menu-item-parent-id' => $parent_nav_item_id,
							'menu-item-type'      => 'post_type',
							'menu-item-classes'   => $page->post_name,
							'menu-item-url'       => get_permalink( $page ),
							'menu-item-status'    => 'publish',
						)
					);

					$nav_menu_ids[ $created_page_id ] = $nav_menu_item_id;
				}
			}
		}

		restore_current_blog();

		update_post_meta( $this->get_wp_post_id(), 'cboxol_group_type_site_template_id', $template_id );

		$this->set_site_template_id( $template_id );
	}

	/**
	 * List of all default labels for all default group types.
	 *
	 * @return array
	 */
	public static function get_all_group_type_default_labels() {
		return [
			'course'    => array(
				'singular'                               => __( 'Course', 'commons-in-a-box' ),
				'plural'                                 => __( 'Courses', 'commons-in-a-box' ),
				'allow_joining_private_label'            => __( 'Allow any community member to request membership to this private course', 'commons-in-a-box' ),
				'allow_joining_public_label'             => __( 'Allow any community member to join this public course', 'commons-in-a-box' ),
				'create_clone_item'                      => __( 'Create/Clone Course', 'commons-in-a-box' ),
				'item_creation'                          => __( 'Course Creation', 'commons-in-a-box' ),
				'create_item_help_text'                  => __( 'Set up the name, URL, avatar, and other settings and permissions for your course. These settings affect the course home, discussion, docs, and files.', 'commons-in-a-box' ),
				'name_help_text'                         => __( 'Please choose your course name carefully. A clear name will make it easier for others to find your course. We recommend keeping the name under 50 characters.', 'commons-in-a-box' ),
				'avatar_help_text'                       => __( 'Upload an image to use as an avatar for this course. The image will be shown on the course home page, and in search results.', 'commons-in-a-box' ),
				'avatar_help_text_cant_decide'           => __( 'Can\'t decide? You can upload a photo once the course is created.', 'commons-in-a-box' ),
				'url_help_text'                          => __( 'Choose a unique URL that will be the home for your course.', 'commons-in-a-box' ),
				'privacy_help_text'                      => __( 'These settings affect how others view your course.', 'commons-in-a-box' ),
				'privacy_help_text_new'                  => __( 'You may change these settings later in the course settings.', 'commons-in-a-box' ),
				'privacy_help_text_public_content'       => __( 'Course and related content and activity, including the membership list, will be visible to the public.', 'commons-in-a-box' ),
				'privacy_help_text_public_directory'     => __( 'Course will be listed in the "Courses" directory, in search results, and may be displayed on the community home page.', 'commons-in-a-box' ),
				'privacy_help_text_public_membership'    => __( 'Any community member may join this course.', 'commons-in-a-box' ),
				'privacy_help_text_private_content'      => __( 'Course content and activity, including the membership list, will only be visible to members of the course.', 'commons-in-a-box' ),
				'privacy_help_text_byrequest_membership' => __( 'Only community members who request membership and are accepted may join this course.', 'commons-in-a-box' ),
				'privacy_help_text_private_directory'    => __( 'Course will NOT be listed in the "Courses" directory, in search results, or on the community home page.', 'commons-in-a-box' ),
				'privacy_help_text_invited_membership'   => __( 'Only community members who are invited may join this course.', 'commons-in-a-box' ),
				'privacy_membership_settings_private'    => __( 'By default, any community member can request membership in a private course. Uncheck the box below to remove the "Request Membership" button from the Course Profile. When the box is unchecked, course membership will be by invitation only.', 'commons-in-a-box' ),
				'privacy_membership_settings_public'     => __( 'By default, a public course may be joined by any community member. Uncheck the box below to remove the "Join Course" button from the Course Profile. When the box is unchecked, membership will be by invitation only.', 'commons-in-a-box' ),
				'group_details'                          => __( 'Course Details', 'commons-in-a-box' ),
				'my_groups'                              => __( 'My Courses', 'commons-in-a-box' ),
				'course_code'                            => __( 'Course Code', 'commons-in-a-box' ),
				'course_information'                     => __( 'Course Information', 'commons-in-a-box' ),
				'course_information_description'         => __( 'The following fields are not required, but including this information will make it easier for others to find your Course.', 'commons-in-a-box' ),
				'section_code'                           => __( 'Section Code', 'commons-in-a-box' ),
				'group_site'                             => __( 'Course Site', 'commons-in-a-box' ),
				'status_open'                            => __( 'This Course is OPEN.', 'commons-in-a-box' ),
				'status_open_community_site'             => __( 'This Course is OPEN, but only logged-in community members may view the corresponding Site.', 'commons-in-a-box' ),
				'status_open_private_site'               => __( 'This Course is OPEN, but the corresponding Site is PRIVATE.', 'commons-in-a-box' ),
				'status_private'                         => __( 'This Course is PRIVATE.', 'commons-in-a-box' ),
				'status_private_community_site'          => __( 'This Course is PRIVATE, but all logged-in community members may view the corresponding Site.', 'commons-in-a-box' ),
				'status_private_open_site'               => __( 'This Course is PRIVATE, but the corresponding Site is OPEN to all visitors.', 'commons-in-a-box' ),
				'status_private_private_site'            => __( 'This Course is PRIVATE, and you must be a member to view the corresponding Site.', 'commons-in-a-box' ),
				'site_help_text'                         => __( 'Each course can also have an optional associated site. This is a WordPress site that all members of your course can access and contribute to.', 'commons-in-a-box' ),
				'site_address_help_text'                 => __( 'Take a moment to consider an address for the site associated with your course. You will not be able to change it once you\'ve created it.', 'commons-in-a-box' ),
				'site_feed_check_help_text'              => __( 'Note: Please click the Check button to search for Post and Comment feeds for your external site. Doing so will push new activity to the course page. If no feeds are detected, you may type in the Post and Comment feed URLs directly or just leave blank.', 'commons-in-a-box' ),
				'visit_group_site'                       => __( 'Visit Course Site', 'commons-in-a-box' ),
				'group_home'                             => __( 'Course Home', 'commons-in-a-box' ),
				'settings_help_text_discussion'          => __( 'These settings enable or disable the discussion forum on your course home page.', 'commons-in-a-box' ),
				'settings_help_text_calendar'            => __( 'These settings determine who can create an event for your course calendar and for the community-wide calendar.', 'commons-in-a-box' ),
				'settings_help_text_calendar_members'    => __( 'Any course member may connect events to this course.', 'commons-in-a-box' ),
				'settings_help_text_calendar_admins'     => __( 'Only administrators and moderators may connect events to this course.', 'commons-in-a-box' ),
				'settings_help_text_relatedlinks'        => __( 'These settings enable or disable the related links list display on your course home page.', 'commons-in-a-box' ),
				'settings_help_text_portfoliolist'       => __( 'These settings enable or disable the member portfolio list display on your course home page.', 'commons-in-a-box' ),
				'settings_help_text_sharing'             => __( 'This setting enables other faculty to clone your Course. If enabled, other faculty can reuse, remix, transform, and build upon the material in this course. Attribution to original Course authors will be included.', 'commons-in-a-box' ),
				'invite_members_to_group'                => __( 'Invite Members to Course', 'commons-in-a-box' ),
				'invite_community_members_to_group'      => __( 'Invite Community Members to Course', 'commons-in-a-box' ),
				'search_for_members_to_invite_to_group'  => __( 'Search for Community Members to invite to your course', 'commons-in-a-box' ),
				'group_contact'                          => __( 'Faculty', 'commons-in-a-box' ),
				'group_contact_help_text'                => __( 'By default, you are the sole faculty member associated with this Course. You may add or remove faculty once your Course has more members.', 'commons-in-a-box' ),
				'group_discussion'                       => __( 'Course Discussion', 'commons-in-a-box' ),
				'clone_credits_widget_description'       => __( 'A list of Courses that have contributed to your Course.', 'commons-in-a-box' ),
				'shareable_content_widget_description'   => __( 'Provides a link for others to clone your Course.', 'commons-in-a-box' ),
				'clone_this_group'                       => __( 'Clone this Course', 'commons-in-a-box' ),
			),
			'project'   => array(
				'singular'                               => __( 'Project', 'commons-in-a-box' ),
				'plural'                                 => __( 'Projects', 'commons-in-a-box' ),
				'allow_joining_private_label'            => __( 'Allow any community member to request membership to this private project', 'commons-in-a-box' ),
				'allow_joining_public_label'             => __( 'Allow any community member to join this public project', 'commons-in-a-box' ),
				'create_clone_item'                      => __( 'Create Project', 'commons-in-a-box' ),
				'item_creation'                          => __( 'Project Creation', 'commons-in-a-box' ),
				'create_item_help_text'                  => __( 'Set up the name, URL, avatar, and other settings and permissions for your project. These settings affect the project home, discussion, docs, and files.', 'commons-in-a-box' ),
				'name_help_text'                         => __( 'Please choose your project name carefully. A clear name will make it easier for others to find your project. We recommend keeping the name under 50 characters.', 'commons-in-a-box' ),
				'avatar_help_text'                       => __( 'Upload an image to use as an avatar for this project. The image will be shown on the project home page, and in search results.', 'commons-in-a-box' ),
				'avatar_help_text_cant_decide'           => __( 'Can\'t decide? You can upload a photo once the project is created.', 'commons-in-a-box' ),
				'url_help_text'                          => __( 'Choose a unique URL that will be the home for your project.', 'commons-in-a-box' ),
				'privacy_help_text'                      => __( 'These settings affect how others view your project.', 'commons-in-a-box' ),
				'privacy_help_text_new'                  => __( 'You may change these settings later in the project settings.', 'commons-in-a-box' ),
				'privacy_help_text_public_content'       => __( 'Project and related content and activity, including the membership list, will be visible to the public.', 'commons-in-a-box' ),
				'privacy_help_text_public_directory'     => __( 'Project will be listed in the "Projects" directory, in search results, and may be displayed on the community home page.', 'commons-in-a-box' ),
				'privacy_help_text_public_membership'    => __( 'Any community member may join this project.', 'commons-in-a-box' ),
				'privacy_help_text_private_content'      => __( 'Project content and activity, including the membership list, will only be visible to members of the project.', 'commons-in-a-box' ),
				'privacy_help_text_byrequest_membership' => __( 'Only community members who request membership and are accepted may join this project.', 'commons-in-a-box' ),
				'privacy_help_text_private_directory'    => __( 'Project will NOT be listed in the "Projects" directory, in search results, or on the community home page.', 'commons-in-a-box' ),
				'privacy_help_text_invited_membership'   => __( 'Only community members who are invited may join this project.', 'commons-in-a-box' ),
				'privacy_membership_settings_private'    => __( 'By default, any community member can request membership in a private project. Uncheck the box below to remove the "Request Membership" button from the Project Profile. When the box is unchecked, project membership will be by invitation only.', 'commons-in-a-box' ),
				'privacy_membership_settings_public'     => __( 'By default, a public project may be joined by any community member. Uncheck the box below to remove the "Join Project" button from the Project Profile. When the box is unchecked, membership will be by invitation only.', 'commons-in-a-box' ),
				'group_details'                          => __( 'Project Details', 'commons-in-a-box' ),
				'my_groups'                              => __( 'My Projects', 'commons-in-a-box' ),
				'group_site'                             => __( 'Project Site', 'commons-in-a-box' ),
				'status_open'                            => __( 'This Project is OPEN.', 'commons-in-a-box' ),
				'status_open_community_site'             => __( 'This Project is OPEN, but only logged-in community members may view the corresponding Site.', 'commons-in-a-box' ),
				'status_open_private_site'               => __( 'This Project is OPEN, but the corresponding Site is PRIVATE.', 'commons-in-a-box' ),
				'status_private'                         => __( 'This Project is PRIVATE.', 'commons-in-a-box' ),
				'status_private_community_site'          => __( 'This Project is PRIVATE, but all logged-in community members may view the corresponding Site.', 'commons-in-a-box' ),
				'status_private_open_site'               => __( 'This Project is PRIVATE, but the corresponding Site is OPEN to all visitors.', 'commons-in-a-box' ),
				'status_private_private_site'            => __( 'This Project is PRIVATE, and you must be a member to view the corresponding Site.', 'commons-in-a-box' ),
				'site_help_text'                         => __( 'Each project can also have an optional associated site. This is a WordPress site that all members of your project can access and contribute to.', 'commons-in-a-box' ),
				'site_address_help_text'                 => __( 'Take a moment to consider an address for the site associated with your project. You will not be able to change it once you\'ve created it.', 'commons-in-a-box' ),
				'site_feed_check_help_text'              => __( 'Note: Please click the Check button to search for Post and Comment feeds for your external site. Doing so will push new activity to the project page. If no feeds are detected, you may type in the Post and Comment feed URLs directly or just leave blank.', 'commons-in-a-box' ),
				'visit_group_site'                       => __( 'Visit Project Site', 'commons-in-a-box' ),
				'group_home'                             => __( 'Project Home', 'commons-in-a-box' ),
				'settings_help_text_discussion'          => __( 'These settings enable or disable the discussion forum on your project home page.', 'commons-in-a-box' ),
				'settings_help_text_calendar'            => __( 'These settings determine who can create an event for your project calendar and for the community-wide calendar.', 'commons-in-a-box' ),
				'settings_help_text_calendar_members'    => __( 'Any project member may connect events to this project.', 'commons-in-a-box' ),
				'settings_help_text_calendar_admins'     => __( 'Only administrators and moderators may connect events to this project.', 'commons-in-a-box' ),
				'settings_help_text_relatedlinks'        => __( 'These settings enable or disable the related links list display on your project home page.', 'commons-in-a-box' ),
				'settings_help_text_portfoliolist'       => __( 'These settings enable or disable the member portfolio list display on your project home page.', 'commons-in-a-box' ),
				'settings_help_text_sharing'             => __( 'This setting enables other members to clone your Project. If enabled, other members can reuse, remix, transform, and build upon the material in this project. Attribution to original Project authors will be included.', 'commons-in-a-box' ),
				'invite_members_to_group'                => __( 'Invite Members to Project', 'commons-in-a-box' ),
				'invite_community_members_to_group'      => __( 'Invite Community Members to Project', 'commons-in-a-box' ),
				'search_for_members_to_invite_to_group'  => __( 'Search for Community Members to invite to your project', 'commons-in-a-box' ),
				'group_contact'                          => __( 'Project Contact', 'commons-in-a-box' ),
				'group_contact_help_text'                => __( 'By default, you are the Project Contact. You may add or remove Project Contacts once your Project has more members.', 'commons-in-a-box' ),
				'group_discussion'                       => __( 'Project Discussion', 'commons-in-a-box' ),
				'clone_credits_widget_description'       => __( 'A list of Projects that have contributed to your Project.', 'commons-in-a-box' ),
				'shareable_content_widget_description'   => __( 'Provides a link for others to clone your Project.', 'commons-in-a-box' ),
				'clone_this_group'                       => __( 'Clone this Project', 'commons-in-a-box' ),
			),
			'club'      => array(
				'singular'                               => __( 'Club', 'commons-in-a-box' ),
				'plural'                                 => __( 'Clubs', 'commons-in-a-box' ),
				'allow_joining_private_label'            => __( 'Allow any community member to request membership to this private club', 'commons-in-a-box' ),
				'allow_joining_public_label'             => __( 'Allow any community member to join this public club', 'commons-in-a-box' ),
				'create_clone_item'                      => __( 'Create Club', 'commons-in-a-box' ),
				'item_creation'                          => __( 'Club Creation', 'commons-in-a-box' ),
				'create_item_help_text'                  => __( 'Set up the name, URL, avatar, and other settings and permissions for your club. These settings affect the club home, discussion, docs, and files.', 'commons-in-a-box' ),
				'name_help_text'                         => __( 'Please choose your club name carefully. A clear name will make it easier for others to find your club. We recommend keeping the name under 50 characters.', 'commons-in-a-box' ),
				'avatar_help_text'                       => __( 'Upload an image to use as an avatar for this club. The image will be shown on the club home page, and in search results.', 'commons-in-a-box' ),
				'avatar_help_text_cant_decide'           => __( 'Can\'t decide? You can upload a photo once the club is created.', 'commons-in-a-box' ),
				'url_help_text'                          => __( 'Choose a unique URL that will be the home for your club.', 'commons-in-a-box' ),
				'privacy_help_text'                      => __( 'These settings affect how others view your club.', 'commons-in-a-box' ),
				'privacy_help_text_new'                  => __( 'You may change these settings later in the club settings.', 'commons-in-a-box' ),
				'privacy_help_text_public_content'       => __( 'Club and related content and activity, including the membership list, will be visible to the public.', 'commons-in-a-box' ),
				'privacy_help_text_public_directory'     => __( 'Club will be listed in the "Clubs" directory, in search results, and may be displayed on the community home page.', 'commons-in-a-box' ),
				'privacy_help_text_public_membership'    => __( 'Any community member may join this club.', 'commons-in-a-box' ),
				'privacy_help_text_private_content'      => __( 'Club content and activity, including the membership list, will only be visible to members of the club.', 'commons-in-a-box' ),
				'privacy_help_text_byrequest_membership' => __( 'Only community members who request membership and are accepted may join this club.', 'commons-in-a-box' ),
				'privacy_help_text_private_directory'    => __( 'Club will NOT be listed in the "Clubs" directory, in search results, or on the community home page.', 'commons-in-a-box' ),
				'privacy_help_text_invited_membership'   => __( 'Only community members who are invited may join this club.', 'commons-in-a-box' ),
				'privacy_membership_settings_private'    => __( 'By default, any community member can request membership in a private club. Uncheck the box below to remove the "Request Membership" button from the Club Profile. When the box is unchecked, club membership will be by invitation only.', 'commons-in-a-box' ),
				'privacy_membership_settings_public'     => __( 'By default, a public club may be joined by any community member. Uncheck the box below to remove the "Join Club" button from the Club Profile. When the box is unchecked, membership will be by invitation only.', 'commons-in-a-box' ),
				'group_details'                          => __( 'Club Details', 'commons-in-a-box' ),
				'my_groups'                              => __( 'My Clubs', 'commons-in-a-box' ),
				'group_site'                             => __( 'Club Site', 'commons-in-a-box' ),
				'status_open'                            => __( 'This Club is OPEN.', 'commons-in-a-box' ),
				'status_open_community_site'             => __( 'This Club is OPEN, but only logged-in community members may view the corresponding Site.', 'commons-in-a-box' ),
				'status_open_private_site'               => __( 'This Club is OPEN, but the corresponding Site is PRIVATE.', 'commons-in-a-box' ),
				'status_private'                         => __( 'This Club is PRIVATE.', 'commons-in-a-box' ),
				'status_private_community_site'          => __( 'This Club is PRIVATE, but all logged-in community members may view the corresponding Site.', 'commons-in-a-box' ),
				'status_private_open_site'               => __( 'This Club is PRIVATE, but the corresponding Site is OPEN to all visitors.', 'commons-in-a-box' ),
				'status_private_private_site'            => __( 'This Club is PRIVATE, and you must be a member to view the corresponding Site.', 'commons-in-a-box' ),
				'site_help_text'                         => __( 'Each club can also have an optional associated site. This is a WordPress site that all members of your club can access and contribute to.', 'commons-in-a-box' ),
				'site_address_help_text'                 => __( 'Take a moment to consider an address for the site associated with your club. You will not be able to change it once you\'ve created it.', 'commons-in-a-box' ),
				'site_feed_check_help_text'              => __( 'Note: Please click the Check button to search for Post and Comment feeds for your external site. Doing so will push new activity to the club page. If no feeds are detected, you may type in the Post and Comment feed URLs directly or just leave blank.', 'commons-in-a-box' ),
				'visit_group_site'                       => __( 'Visit Club Site', 'commons-in-a-box' ),
				'group_home'                             => __( 'Club Home', 'commons-in-a-box' ),
				'settings_help_text_discussion'          => __( 'These settings enable or disable the discussion forum on your club home page.', 'commons-in-a-box' ),
				'settings_help_text_calendar'            => __( 'These settings determine who can create an event for your club calendar and for the community-wide calendar.', 'commons-in-a-box' ),
				'settings_help_text_calendar_members'    => __( 'Any club member may connect events to this club.', 'commons-in-a-box' ),
				'settings_help_text_calendar_admins'     => __( 'Only administrators and moderators may connect events to this club.', 'commons-in-a-box' ),
				'settings_help_text_relatedlinks'        => __( 'These settings enable or disable the related links list display on your club home page.', 'commons-in-a-box' ),
				'settings_help_text_portfoliolist'       => __( 'These settings enable or disable the member portfolio list display on your club home page.', 'commons-in-a-box' ),
				'settings_help_text_sharing'             => __( 'This setting enables other members to clone your Club. If enabled, other members can reuse, remix, transform, and build upon the material in this club. Attribution to original Club authors will be included.', 'commons-in-a-box' ),
				'invite_members_to_group'                => __( 'Invite Members to Club', 'commons-in-a-box' ),
				'invite_community_members_to_group'      => __( 'Invite Community Members to Club', 'commons-in-a-box' ),
				'search_for_members_to_invite_to_group'  => __( 'Search for Community Members to invite to your club', 'commons-in-a-box' ),
				'group_contact'                          => __( 'Club Contact', 'commons-in-a-box' ),
				'group_contact_help_text'                => __( 'By default, you are the Club Contact. You may add or remove Club Contacts once your Club has more members.', 'commons-in-a-box' ),
				'group_discussion'                       => __( 'Club Discussion', 'commons-in-a-box' ),
				'clone_credits_widget_description'       => __( 'A list of Clubs that have contributed to your Club.', 'commons-in-a-box' ),
				'shareable_content_widget_description'   => __( 'Provides a link for others to clone your Club.', 'commons-in-a-box' ),
				'clone_this_group'                       => __( 'Clone this Club', 'commons-in-a-box' ),
			),
			'portfolio' => array(
				'singular'                               => __( 'Portfolio', 'commons-in-a-box' ),
				'plural'                                 => __( 'Portfolios', 'commons-in-a-box' ),
				'allow_joining_private_label'            => __( 'Allow any community member to request membership to this private portfolio', 'commons-in-a-box' ),
				'allow_joining_public_label'             => __( 'Allow any community member to join this public portfolio', 'commons-in-a-box' ),
				'create_clone_item'                      => __( 'Create Portfolio', 'commons-in-a-box' ),
				'item_creation'                          => __( 'Portfolio Creation', 'commons-in-a-box' ),
				'create_item_help_text'                  => __( 'Set up the name, URL, avatar, and other settings and permissions for your portfolio. These settings affect the portfolio home, discussion, docs, and files.', 'commons-in-a-box' ),
				'name_help_text'                         => __( 'Choose a name for your Portfolio. You may use your name ("Jane Smith\'s Portfolio"), or any descriptive title you\'d like.', 'commons-in-a-box' ),
				'avatar_help_text'                       => __( 'Upload an image to use as an avatar for this portfolio. The image will be shown on the portfolio home page, and in search results.', 'commons-in-a-box' ),
				'avatar_help_text_cant_decide'           => __( 'Can\'t decide? You can upload a photo once the portfolio is created.', 'commons-in-a-box' ),
				'url_help_text'                          => __( 'Choose a unique URL that will be the home for your portfolio.', 'commons-in-a-box' ),
				'privacy_help_text'                      => __( 'These settings affect how others view your portfolio.', 'commons-in-a-box' ),
				'privacy_help_text_new'                  => __( 'You may change these settings later in the portfolio settings.', 'commons-in-a-box' ),
				'privacy_help_text_public_content'       => __( 'Portfolio and related content and activity, including the membership list, will be visible to the public.', 'commons-in-a-box' ),
				'privacy_help_text_public_directory'     => __( 'Portfolio will be listed in the "Portfolios" directory, in search results, and may be displayed on the community home page.', 'commons-in-a-box' ),
				'privacy_help_text_public_membership'    => __( 'Any community member may join this portfolio.', 'commons-in-a-box' ),
				'privacy_help_text_private_content'      => __( 'Portfolio content and activity, including the membership list, will only be visible to members of the portfolio.', 'commons-in-a-box' ),
				'privacy_help_text_byrequest_membership' => __( 'Only community members who request membership and are accepted may join this portfolio.', 'commons-in-a-box' ),
				'privacy_help_text_private_directory'    => __( 'Portfolio will NOT be listed in the "Portfolios" directory, in search results, or on the community home page.', 'commons-in-a-box' ),
				'privacy_help_text_invited_membership'   => __( 'Only community members who are invited may join this portfolio.', 'commons-in-a-box' ),
				'privacy_membership_settings_private'    => __( 'By default, any community member can request membership in a private portfolio. Uncheck the box below to remove the "Request Membership" button from the Portfolio Profile. When the box is unchecked, portfolio membership will be by invitation only.', 'commons-in-a-box' ),
				'privacy_membership_settings_public'     => __( 'By default, a public portfolio may be joined by any community member. Uncheck the box below to remove the "Join Portfolio" button from the Portfolio Profile. When the box is unchecked, membership will be by invitation only.', 'commons-in-a-box' ),
				'create_item'                            => __( 'Create Portfolio', 'commons-in-a-box' ),
				'group_details'                          => __( 'Portfolio Details', 'commons-in-a-box' ),
				'my_portfolio'                           => __( 'My Portfolio', 'commons-in-a-box' ),
				'my_portfolio_site'                      => __( 'My Portfolio Site', 'commons-in-a-box' ),
				'status_open'                            => __( 'This Portfolio is OPEN.', 'commons-in-a-box' ),
				'status_open_community_site'             => __( 'This Portfolio is OPEN, but only logged-in community members may view the corresponding Site.', 'commons-in-a-box' ),
				'status_open_private_site'               => __( 'This Portfolio is OPEN, but the corresponding Site is PRIVATE.', 'commons-in-a-box' ),
				'status_private'                         => __( 'This Portfolio is PRIVATE.', 'commons-in-a-box' ),
				'status_private_community_site'          => __( 'This Portfolio is PRIVATE, but all logged-in community members may view the corresponding Site.', 'commons-in-a-box' ),
				'status_private_open_site'               => __( 'This Portfolio is PRIVATE, but the corresponding Site is OPEN to all visitors.', 'commons-in-a-box' ),
				'status_private_private_site'            => __( 'This Portfolio is PRIVATE, and you must be a member to view the corresponding Site.', 'commons-in-a-box' ),
				'visit_portfolio_site'                   => __( 'Visit Portfolio Site', 'commons-in-a-box' ),
				'visit_group_site'                       => __( 'Visit Portfolio Site', 'commons-in-a-box' ),
				'site_help_text'                         => __( 'Each portfolio is associated with a WordPress site. The site is where portfolio owners display their work and accomplishments.', 'commons-in-a-box' ),
				'site_address_help_text'                 => __( 'Take a moment to consider an address for the site associated with your portfolio. You will not be able to change it once you\'ve created it.', 'commons-in-a-box' ),
				'site_feed_check_help_text'              => __( 'Note: Please click the Check button to search for Post and Comment feeds for your external site. Doing so will push new activity to the portfolio page. If no feeds are detected, you may type in the Post and Comment feed URLs directly or just leave blank.', 'commons-in-a-box' ),
				'group_site'                             => __( 'Portfolio Site', 'commons-in-a-box' ),
				'group_home'                             => __( 'Portfolio Home', 'commons-in-a-box' ),
				'settings_help_text_relatedlinks'        => __( 'These settings enable or disable the related links list display on your portfolio home page.', 'commons-in-a-box' ),
				'settings_help_text_add_to_portfolio'    => __( 'The Add to Portfolio feature saves selected posts, pages, and comments that you have authored on Course, Project, and Club sites directly to your Portfolio site.', 'commons-in-a-box' ),
				'invite_members_to_group'                => __( 'Invite Members to Portfolio', 'commons-in-a-box' ),
				'invite_community_members_to_group'      => __( 'Invite Community Members to Portfolio', 'commons-in-a-box' ),
				'search_for_members_to_invite_to_group'  => __( 'Search for Community Members to invite to your portfolio', 'commons-in-a-box' ),
				'group_contact'                          => __( 'Porfolio Contact', 'commons-in-a-box' ),
				'group_contact_help_text'                => __( 'By default, you are the Portfolio Contact. You may add or remove Portfolio Contacts once your Portfolio has more members.', 'commons-in-a-box' ),
				'group_discussion'                       => __( 'Portfolio Discussion', 'commons-in-a-box' ),
				'clone_credits_widget_description'       => __( 'A list of Portfolios that have contributed to your Portfolio.', 'commons-in-a-box' ),
				'shareable_content_widget_description'   => __( 'Provides a link for others to clone your Portfolio.', 'commons-in-a-box' ),
				'clone_this_group'                       => __( 'Clone this Portfolio', 'commons-in-a-box' ),
				'add_to_portfolio'                       => __( 'Add to Portfolio', 'commons-in-a-box' ),
				'added_to_my_portfolio'                  => __( 'Added to my Portfolio', 'commons-in-a-box' ),
			),
		];
	}

	/**
	 * Gets the default labels for a given group type.
	 *
	 * @param string $group_type_slug
	 * @return array
	 */
	public static function get_group_type_default_labels( $group_type_slug ) {
		$all_labels = self::get_all_group_type_default_labels();

		if ( array_key_exists( $group_type_slug, $all_labels ) ) {
			return $all_labels[ $group_type_slug ];
		}

		return [];
	}

	/**
	 * Gets the default value of a specific label for a given group type.
	 *
	 * @param string $group_type_slug
	 * @param string $label_key
	 * @return string
	 */
	public static function get_group_type_default_label( $group_type_slug, $label_key ) {
		$labels = self::get_group_type_default_labels( $group_type_slug );

		if ( array_key_exists( $label_key, $labels ) ) {
			return $labels[ $label_key ];
		}

		return '';
	}
}
