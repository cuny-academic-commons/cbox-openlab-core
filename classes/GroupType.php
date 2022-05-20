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
			'id'             => $this->get_wp_post_id(),
			'isCollapsed'    => true,
			'isCourse'       => $this->get_is_course(),
			'isPortfolio'    => $this->get_is_portfolio(),
			'isEnabled'      => $this->get_is_enabled(),
			'isLoading'      => false,
			'isModified'     => false,
			'canBeDeleted'   => $this->get_can_be_deleted(),
			'settings'       => array(
				'Order' => array(
					'component' => 'Order',
					'data'      => $this->get_order(),
				),
			),
			'name'           => $this->get_name(),
			'slug'           => $this->get_slug(),
			'labels'         => $this->get_labels(),
			'siteTemplates'  => $this->get_site_templates(),
			'siteTemplateId' => $this->get_site_template_id(),
		);
	}

	public function get_directory_filters() {
		return $this->data['directory_filters'];
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

		return $site_templates;
	}

	/**
	 * Overridden here because we filter based on group type.
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

		// this is a real mess
		$retval     = array();
		$label_info = $this->get_label_types_info();
		foreach ( $this->data['labels'] as $label_slug => $label_value ) {
			if ( in_array( $label_slug, $type_labels, true ) ) {
				$label_data          = $label_info[ $label_slug ];
				$label_data['value'] = $label_value;

				$retval[ $label_slug ] = $label_data;
			}
		}

		// this continues to be a real mess
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
		// translators: Group type label
		$term_name = sprintf( __( 'General: %s', 'commons-in-a-box' ), $this->get_label( 'plural' ) );

		$inserted = wp_insert_term( $term_name, 'cboxol_template_category' );

		add_term_meta( $inserted['term_id'], 'cboxol_group_type', $this->get_slug() );

		wp_set_post_terms( $template_id, [ $inserted['term_id'] ], 'cboxol_template_category' );

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

			$locations = get_theme_mod( 'nav_menu_locations' );

			if ( ! empty( $locations['primary'] ) ) {
				$menu_id = $locations['primary'];

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
}
