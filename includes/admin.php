<?php

/**
 * General admin functionality.
 */

if ( ! defined( 'DOING_AJAX' ) ) {
	add_action( 'cbox_openlab_admin_menu', 'cboxol_register_admin_menu' );
}

add_action( 'admin_enqueue_scripts', 'cboxol_register_assets' );

function cboxol_register_admin_menu() {
	// @todo only add on "main" site
	// @todo icon
	// @todo How do I make it "About" as first option

	add_submenu_page(
		cboxol_admin_slug(),
		__( 'Member Settings', 'commons-in-a-box' ),
		__( 'Member Settings', 'commons-in-a-box' ),
		'manage_network_options',
		cboxol_admin_slug( 'member-settings' ),
		'cboxol_member_settings_admin_page',
		2
	);

	add_submenu_page(
		cboxol_admin_slug(),
		__( 'Group Settings', 'commons-in-a-box' ),
		__( 'Group Settings', 'commons-in-a-box' ),
		'manage_network_options',
		cboxol_admin_slug( 'group-settings' ),
		'cboxol_group_settings_admin_page',
		3
	);

	add_submenu_page(
		cboxol_admin_slug(),
		__( 'Academic Units', 'commons-in-a-box' ),
		__( 'Academic Units', 'commons-in-a-box' ),
		'manage_network_options',
		cboxol_admin_slug( 'academic-units' ),
		'cboxol_academic_units_admin_page',
		4
	);

	add_submenu_page(
		cboxol_admin_slug(),
		__( 'Academic Terms', 'commons-in-a-box' ),
		__( 'Academic Terms', 'commons-in-a-box' ),
		'manage_network_options',
		cboxol_admin_slug( 'academic-terms' ),
		'cboxol_academic_terms_admin_page',
		5
	);

	add_submenu_page(
		cboxol_admin_slug(),
		__( 'Brand Settings', 'commons-in-a-box' ),
		__( 'Brand Settings', 'commons-in-a-box' ),
		'manage_network_options',
		cboxol_admin_slug( 'brand-settings' ),
		'cboxol_brand_settings_admin_page',
		6
	);

	add_submenu_page(
		cboxol_admin_slug(),
		__( 'Communication Settings', 'commons-in-a-box' ),
		__( 'Communication Settings', 'commons-in-a-box' ),
		'manage_network_options',
		cboxol_admin_slug( 'communication-settings' ),
		'cboxol_communication_settings_admin_page',
		7
	);
}

function cboxol_register_assets() {
	// @todo Should these be loaded on all sites in the network?
	wp_register_script(
		'cbox-ol-app',
		CBOXOL_PLUGIN_URL . 'assets/js/build.js',
		array(),
		cboxol_get_asset_version(),
		true
	);

	wp_localize_script(
		'cbox-ol-app',
		'CBOXOLStrings',
		array(
			'nonce'                 => wp_create_nonce( 'wp_rest' ),
			'endpointBase'          => home_url( '/wp-json/cboxol/v1/' ),
			'siteTemplatesAdminUrl' => admin_url( 'edit.php?post_type=cboxol_site_template' ),
			'strings'               => array(
				'academicUnitNameLabel'                => _x( 'Name', '"Name" label for adding new academic units', 'commons-in-a-box' ),
				'academicUnitParentLegend'             => __( 'Define a parent/child relationship to indicate which category from the parent Academic Unit Type this new category should be associated with.', 'commons-in-a-box' ),
				'action'                               => _x( 'Action', 'Header for Action column in admin tables', 'commons-in-a-box' ),
				'add'                                  => _x( 'Add', '"Add" button text', 'commons-in-a-box' ),
				'addEmailDomain'                       => __( 'Add email domain', 'commons-in-a-box' ),
				'addNewAcademicTerm'                   => __( 'Add New Academic Term', 'commons-in-a-box' ),
				'addNewAcademicUnit'                   => __( 'Add New Academic Unit', 'commons-in-a-box' ),
				'addNewAcademicUnitTitle'              => __( 'Add New', 'commons-in-a-box' ),
				'addNewCategory'                       => __( 'Add New Category', 'commons-in-a-box' ),
				'addNewType'                           => _x( 'Add New Type', 'placeholder for new item type form', 'commons-in-a-box' ),
				'associatedWithGroupTypes'             => __( 'Associated with Group Types', 'commons-in-a-box' ),
				'associatedWithMemberTypes'            => __( 'Associated with Member Types', 'commons-in-a-box' ),
				'cancel'                               => __( 'Cancel', 'commons-in-a-box' ),
				'code'                                 => _x( 'Code', 'Column header for signup code value', 'commons-in-a-box' ),
				'confirmationText'                     => __( 'Confirmation Text', 'commons-in-a-box' ),
				'confirmationTextLegend'               => __( 'The text that appears just above the "Complete Sign Up" button on the registration form.', 'commons-in-a-box' ),
				'count'                                => _x( 'Count', 'Column header', 'commons-in-a-box' ),
				'delete'                               => __( 'Delete', 'commons-in-a-box' ),
				'deleteConfirm'                        => __( 'Are you sure you want to delete this content?', 'commons-in-a-box' ),
				'domain'                               => _x( 'Domain', 'Domain from email domain whitelist', 'commons-in-a-box' ),
				'dragToSort'                           => __( 'Drag to sort', 'commons-in-a-box' ),
				'edit'                                 => __( 'Edit', 'commons-in-a-box' ),
				'editing'                              => __( 'Editing', 'commons-in-a-box' ),
				'emailDomainWhitelist'                 => __( 'Email Domain Whitelist', 'commons-in-a-box' ),
				'emailDomainWhitelistLegend'           => __( 'To limit new user registrations to one or multiple email domains, include them here. Only users with emails matching the whitelisted domain(s) will be allowed to register for accounts. Wildcards are supported for multiple formats of the same base domain (e.g. *.schoolname.edu).', 'commons-in-a-box' ),
				'enterSignupCode'                      => __( 'Enter Signup Code', 'commons-in-a-box' ),
				'formCustomization'                    => __( 'Form Customization', 'commons-in-a-box' ),
				'formCustomizationLegend'              => __( 'Use these settings to customize the registration form.', 'commons-in-a-box' ),
				'formCustomizationSave'                => __( 'Save Form Customization Settings', 'commons-in-a-box' ),
				'group'                                => _x( 'Group', 'Column header for signup code table', 'commons-in-a-box' ),
				'itemTypeNameLabel'                    => _x( 'Name', 'item type Name label', 'commons-in-a-box' ),
				'labels'                               => _x( 'Labels', 'subheader for item type labels', 'commons-in-a-box' ),
				'links'                                => _x( 'Links', 'column header for template site settings section', 'commons-in-a-box' ),
				'mayCreateCoursesLegend'               => __( 'Members may create courses', 'commons-in-a-box' ),

				// @todo This probably will not translate.
				'mayChangeMemberTypeToLegend'          => __( 'Members may change Type to', 'commons-in-a-box' ),
				'memberType'                           => __( 'Member Type', 'commons-in-a-box' ),

				'name'                                 => _x( 'Name', 'table header', 'commons-in-a-box' ),
				'no'                                   => _x( 'No', 'radio button option', 'commons-in-a-box' ),
				'noEmailDomains'                       => __( 'Registration is currently open for all email domains. Enter one or more domains to restrict registration by email address.', 'commons-in-a-box' ),
				'none'                                 => _x( 'None', 'null dropdown option', 'commons-in-a-box' ),
				'noSignupCodes'                        => __( 'Currently, users may select any Member Type when creating or editing their accounts. To restrict access to a Member Type, create a corresponding Signup Code below.', 'commons-in-a-box' ),
				'noUnitsOfType'                        => __( 'There are no units of this type.', 'commons-in-a-box' ),
				'off'                                  => _x( '(Off)', 'disabled label for item type', 'commons-in-a-box' ),
				'onOffSwitchLabel'                     => __( 'On/off toggle', 'commons-in-a-box' ),
				'optional'                             => __( 'Optional', 'commons-in-a-box' ),
				'orderDescription'                     => __( 'Used when displaying lists of types throughout the site.', 'commons-in-a-box' ),
				'orderLegend'                          => __( 'Order', 'commons-in-a-box' ),
				'parent'                               => __( 'Parent', 'commons-in-a-box' ),
				'required'                             => __( 'Required', 'commons-in-a-box' ),
				'save'                                 => __( 'Save', 'commons-in-a-box' ),
				'saveChanges'                          => __( 'Save Changes', 'commons-in-a-box' ),
				'saved'                                => __( 'Saved!', 'commons-in-a-box' ),
				'saving'                               => __( 'Saving', 'commons-in-a-box' ),
				'settings'                             => _x( 'Settings', 'subheader for item type settings', 'commons-in-a-box' ),
				'selectAll'                            => __( 'Select All', 'commons-in-a-box' ),
				'selectGroup'                          => __( 'Select Group', 'commons-in-a-box' ),
				'selectMemberType'                     => __( 'Select Member Type', 'commons-in-a-box' ),
				// translators: Unit name
				'selectUnit'                           => _x( 'Select Unit: %s', 'checkbox screen reader text', 'commons-in-a-box' ),
				'signUpCode'                           => __( 'Signup Code', 'commons-in-a-box' ),
				'signUpCodes'                          => __( 'Sign Up Codes', 'commons-in-a-box' ),
				'signUpCodesLegend'                    => __( 'Registration codes let you restrict access to specific member account types (e.g faculty, staff, student). Each code can be associated with a group, so that users registering with the code will automatically be added to the group when their registration is complete. These account codes do not allow users to bypass the Email Domain Whitelist above.', 'commons-in-a-box' ),
				'template'                             => _x( 'Default Template', 'subheader for template site settings section', 'commons-in-a-box' ),
				'templates'                            => _x( 'Templates', 'column header for template site settings section', 'commons-in-a-box' ),
				'templateDashboardLink'                => _x( 'Dashboard', 'template site dashboard link', 'commons-in-a-box' ),
				'templateSiteDescription'              => __( 'When a group of this type creates a site, default settings and data will be copied from the group type\'s template site. Below, choose the default template associated with this group type and use the links to view and configure the template site.', 'commons-in-a-box' ),
				'templateSiteAdminDescription'         => __( 'You can create additional templates in Site Templates', 'commons-in-a-box' ),
				'templateViewLink'                     => _x( 'View Template', 'template site view link', 'commons-in-a-box' ),
				'thisGroupTypeIsDesignedForCourses'    => __( 'Note: This Group Type is designed for Courses.', 'commons-in-a-box' ),
				'thisGroupTypeIsDesignedForPortfolios' => __( 'Note: This Group Type is designed for Portfolios.', 'commons-in-a-box' ),
				'update'                               => __( 'Update', 'commons-in-a-box' ),
				'yes'                                  => _x( 'Yes', 'radio button option', 'commons-in-a-box' ),
			),
		)
	);

	wp_register_style( 'cbox-ol-admin', CBOXOL_PLUGIN_URL . 'assets/css/admin.css', array(), cboxol_get_asset_version() );
	// @todo More specific.
	wp_enqueue_style( 'cbox-ol-admin' );
}

/**
 * Registers assets needed for the block editor across all sites.
 *
 * @since 1.6.0
 *
 * @return void
 */
function cboxol_register_block_assets() {
	$blocks_dir        = CBOXOL_PLUGIN_URL . 'build/';
	$blocks_asset_file = include CBOXOL_PLUGIN_DIR . 'build/blocks.asset.php';

	// Replace "wp-blockEditor" with "wp-block-editor".
	$blocks_asset_file['dependencies'] = array_replace(
		$blocks_asset_file['dependencies'],
		array_fill_keys(
			array_keys( $blocks_asset_file['dependencies'], 'wp-blockEditor', true ),
			'wp-block-editor'
		)
	);

	wp_enqueue_script(
		'cboxol-block-editor',
		CBOXOL_PLUGIN_URL . 'build/blocks.js',
		$blocks_asset_file['dependencies'],
		$blocks_asset_file['version'],
		true
	);

	$blog_public = (int) get_option( 'blog_public' );

	wp_add_inline_script(
		'cboxol-block-editor',
		'const openlabBlocksPostVisibility = ' . wp_json_encode(
			[
				'siteIsPublic' => $blog_public >= 0,
			]
		) . ';'
	);
}
add_action( 'enqueue_block_editor_assets', 'cboxol_register_block_assets' );

function cboxol_admin_slug( $parent_page = '' ) {
	switch ( $parent_page ) {
		case 'member-settings':
			return 'cbox-ol-member-settings';

		case 'group-settings':
			return 'cbox-ol-group-settings';

		case 'brand-settings':
			return 'cbox-ol-brand-settings';

		case 'academic-units':
			return 'cbox-ol-academic-units';

		case 'academic-terms':
			return 'cbox-ol-academic-terms';

		case 'communication-settings':
			return 'cbox-ol-communication-settings';

		default:
			return 'cbox';
	}
}

function cboxol_admin_page_label( $page ) {
	switch ( $page ) {
		case 'member-settings':
			return __( 'Member Settings', 'commons-in-a-box' );

		case 'group-settings':
			return __( 'Group Settings', 'commons-in-a-box' );

		case 'brand-settings':
			return __( 'Brand Settings', 'commons-in-a-box' );

		case 'communication-settings':
			return __( 'Communication Settings', 'commons-in-a-box' );

		case 'academic-units':
			return __( 'Academic Units', 'commons-in-a-box' );

		case 'academic-terms':
			return __( 'Academic Terms', 'commons-in-a-box' );
	}
}

function cboxol_admin_subpage_label( $parent_page, $page ) {
	switch ( $parent_page ) {
		case 'member-settings':
			switch ( $page ) {
				case 'types':
					return _x( 'Types', 'Member Types admin label', 'commons-in-a-box' );

				case 'registration':
					return _x( 'Registration', 'Registration admin label', 'commons-in-a-box' );

				case 'profile-fields':
					return _x( 'Profile Fields', 'Member profile fields admin label', 'commons-in-a-box' );
			}

			break;

		case 'group-settings':
			switch ( $page ) {
				case 'types':
					return _x( 'Types', 'Group Types admin label', 'commons-in-a-box' );

				case 'group-categories':
					return _x( 'Group Categories', 'Group categories admin label', 'commons-in-a-box' );

				case 'badges':
					return _x( 'Badges', 'Group settings admin label', 'commons-in-a-box' );

				case 'site-templates':
					return _x( 'Site Templates', 'Group settings admin label', 'commons-in-a-box' );
			}

			break;

		case 'communication-settings':
			switch ( $page ) {
				case 'email':
					return _x( 'Email', 'Communication Settings admin label', 'commons-in-a-box' );

				case 'invitations':
					return _x( 'Invitations', 'Communication Settings admin label', 'commons-in-a-box' );
			}

			break;
	}
}

function cboxol_admin_header( $parent_page, $sub_page ) {
	$parent_title = '';
	$sub_title    = '';

	$subpage_label = cboxol_admin_subpage_label( $parent_page, $sub_page );

	if ( $subpage_label ) {
		$title = sprintf(
			'<h1>%s: %s</h1>',
			cboxol_admin_page_label( $parent_page ),
			cboxol_admin_subpage_label( $parent_page, $sub_page )
		);
	} else {
		$title = sprintf(
			'<h1>%s</h1>',
			cboxol_admin_page_label( $parent_page )
		);
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $title;

	cboxol_admin_tabs( $parent_page, $sub_page );
}

/**
 * Output the tabs in the admin area.
 *
 * @since 1.5.0
 *
 * @param string $parent_page Parent page.
 * @param string $active_tab  Name of the tab that is active. Optional.
 */
function cboxol_admin_tabs( $parent_page, $active_tab = '' ) {
	$tabs_html    = '';
	$idle_class   = 'nav-tab';
	$active_class = 'nav-tab nav-tab-active';

	$tabs = cboxol_get_admin_tabs( $parent_page );

	// Loop through tabs and build navigation.
	foreach ( array_values( $tabs ) as $tab_data ) {
		$is_current = (bool) ( $tab_data['name'] === $active_tab );
		$tab_class  = $is_current ? $active_class : $idle_class;
		$tabs_html .= '<a href="' . esc_url( $tab_data['href'] ) . '" class="' . esc_attr( $tab_class ) . '">' . esc_html( $tab_data['label'] ) . '</a>';
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<div class="nav-tab-wrapper">' . $tabs_html . '</div>';
}

/**
 * Get the data for the tabs in the admin area.
 *
 * @param string $parent_page Name of the tab that is active. Optional.
 * @return array
 */
function cboxol_get_admin_tabs( $parent_page ) {
	$tabs = array();

	switch ( $parent_page ) {
		case 'member-settings':
			$base = self_admin_url( add_query_arg( 'page', cboxol_admin_slug( 'member-settings' ), 'admin.php' ) );
			$tabs = array(
				'0' => array(
					'href'  => add_query_arg( 'cboxol-section', 'types', $base ),
					'name'  => 'types',
					'label' => cboxol_admin_subpage_label( 'member-settings', 'types' ),
				),
				'1' => array(
					'href'  => add_query_arg( 'cboxol-section', 'registration', $base ),
					'name'  => 'registration',
					'label' => cboxol_admin_subpage_label( 'member-settings', 'registration' ),
				),
				'2' => array(
					'href'  => add_query_arg( 'cboxol-section', 'profile-fields', $base ),
					'name'  => 'profile-fields',
					'label' => cboxol_admin_subpage_label( 'member-settings', 'profile-fields' ),
				),
			);

			break;

		case 'group-settings':
			$base = self_admin_url( add_query_arg( 'page', cboxol_admin_slug( 'group-settings' ), 'admin.php' ) );
			$tabs = array(
				'0' => array(
					'href'  => add_query_arg( 'cboxol-section', 'types', $base ),
					'name'  => 'types',
					'label' => cboxol_admin_subpage_label( 'group-settings', 'types' ),
				),
				'1' => array(
					'href'  => add_query_arg( 'cboxol-section', 'group-categories', $base ),
					'name'  => 'group-categories',
					'label' => cboxol_admin_subpage_label( 'group-settings', 'group-categories' ),
				),
				'2' => array(
					'href'  => add_query_arg( 'cboxol-section', 'badges', $base ),
					'name'  => 'badges',
					'label' => cboxol_admin_subpage_label( 'group-settings', 'badges' ),
				),
				'3' => array(
					'href'  => add_query_arg( 'cboxol-section', 'site-templates', $base ),
					'name'  => 'site-templates',
					'label' => cboxol_admin_subpage_label( 'group-settings', 'site-templates' ),
				),
			);

			break;

		case 'communication-settings':
			$base = self_admin_url( add_query_arg( 'page', cboxol_admin_slug( 'communication-settings' ), 'admin.php' ) );
			$tabs = array(
				'0' => array(
					'href'  => add_query_arg( 'cboxol-section', 'email', $base ),
					'name'  => 'email',
					'label' => cboxol_admin_subpage_label( 'communication-settings', 'email' ),
				),
				'1' => array(
					'href'  => add_query_arg( 'cboxol-section', 'invitations', $base ),
					'name'  => 'invitations',
					'label' => cboxol_admin_subpage_label( 'communication-settings', 'invitations' ),
				),
			);
	}

	return $tabs;
}

function cboxol_admin_section_content( $parent_page, $sub_page ) {
	switch ( $parent_page ) {
		case 'member-settings':
			switch ( $sub_page ) {
				case 'types':
					cboxol_membertypes_admin_page();
					break;

				case 'registration':
					cboxol_registration_admin_page();
					break;

				case 'profile-fields':
					cboxol_profile_fields_admin_page();
					break;
			}
			break;

		case 'group-settings':
			switch ( $sub_page ) {
				case 'types':
					cboxol_grouptypes_admin_page();
					break;

				case 'group-categories':
					cboxol_groupcategories_admin_page();
					break;

				case 'badges':
					cboxol_badges_admin_page();
					break;

				case 'site-templates':
					cboxol_site_templates_admin_page();
					break;
			}

			break;

		case 'brand-settings':
			cboxol_brand_admin_page();
			break;

		case 'communication-settings':
			switch ( $sub_page ) {
				case 'email':
					cboxol_communication_admin_page_email();
					break;

				case 'invitations':
					cboxol_communication_admin_page_invitations();
					break;
			}
			break;

		case 'academic-units':
			cboxol_academic_units_main_admin_page();
			break;

		case 'academic-terms':
			cboxol_academic_terms_main_admin_page();
			break;
	}
}

function cboxol_group_settings_admin_page() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_section = isset( $_GET['cboxol-section'] ) ? wp_unslash( $_GET['cboxol-section'] ) : 'types';
	cboxol_admin_page( 'group-settings', $current_section );
}

function cboxol_member_settings_admin_page() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_section = isset( $_GET['cboxol-section'] ) ? wp_unslash( $_GET['cboxol-section'] ) : 'types';
	cboxol_admin_page( 'member-settings', $current_section );
}

function cboxol_brand_settings_admin_page() {
	$current_section = '';
	cboxol_admin_page( 'brand-settings', $current_section );
}

function cboxol_communication_settings_admin_page() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_section = isset( $_GET['cboxol-section'] ) ? wp_unslash( $_GET['cboxol-section'] ) : 'email';
	cboxol_admin_page( 'communication-settings', $current_section );
}

function cboxol_academic_units_admin_page() {
	$current_section = '';
	cboxol_admin_page( 'academic-units', $current_section );
}

function cboxol_academic_terms_admin_page() {
	$current_section = '';
	cboxol_admin_page( 'academic-terms', $current_section );
}

function cboxol_admin_page( $parent_page, $current_section ) {
	?>

	<div class="wrap cboxol-admin-wrap cboxol-admin-<?php echo esc_attr( $parent_page ); ?> <?php
	if ( $current_section ) :
		?>
		cboxol-admin-<?php echo esc_attr( $parent_page ); ?>-<?php echo esc_attr( $current_section ); ?><?php endif; ?>">
		<?php cboxol_admin_header( $parent_page, $current_section ); ?>

		<?php cboxol_admin_section_content( $parent_page, $current_section ); ?>
	</div>

	<?php
}
