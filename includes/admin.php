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
	/*
	add_menu_page(
		__( 'OpenLab Setup', 'cbox-openlab-core' ),
		__( 'OpenLab Setup', 'cbox-openlab-core' ),
		'manage_network_options',
		cboxol_admin_slug(),
		'cboxol_admin_about_page',
		'',
		2
	);
	*/

	add_submenu_page(
		cboxol_admin_slug(),
		__( 'Member Settings', 'cbox-openlab-core' ),
		__( 'Member Settings', 'cbox-openlab-core' ),
		'manage_network_options',
		cboxol_admin_slug( 'member-settings' ),
		'cboxol_member_settings_admin_page',
		2
	);

	add_submenu_page(
		cboxol_admin_slug(),
		__( 'Group Settings', 'cbox-openlab-core' ),
		__( 'Group Settings', 'cbox-openlab-core' ),
		'manage_network_options',
		cboxol_admin_slug( 'group-settings' ),
		'cboxol_group_settings_admin_page',
		3
	);

	add_submenu_page(
		cboxol_admin_slug(),
		__( 'Academic Units', 'cbox-openlab-core' ),
		__( 'Academic Units', 'cbox-openlab-core' ),
		'manage_network_options',
		cboxol_admin_slug( 'academic-units' ),
		'cboxol_academic_units_admin_page',
		4
	);

	add_submenu_page(
		cboxol_admin_slug(),
		__( 'Brand Settings', 'cbox-openlab-core' ),
		__( 'Brand Settings', 'cbox-openlab-core' ),
		'manage_network_options',
		cboxol_admin_slug( 'brand-settings' ),
		'cboxol_brand_settings_admin_page',
		5
	);

	add_submenu_page(
		cboxol_admin_slug(),
		__( 'Communication Settings', 'cbox-openlab-core' ),
		__( 'Communication Settings', 'cbox-openlab-core' ),
		'manage_network_options',
		cboxol_admin_slug( 'communication-settings' ),
		'cboxol_communication_settings_admin_page',
		6
	);
}

function cboxol_register_assets() {
	wp_register_script(
		'cbox-ol-app',
		CBOXOL_PLUGIN_URL . 'assets/js/build.js',
		array(),
		cboxol_get_asset_version(),
		true
	);

	wp_localize_script( 'cbox-ol-app', 'CBOXOLStrings', array(
		'nonce' => wp_create_nonce( 'wp_rest' ),
		'endpointBase' => home_url( '/wp-json/cboxol/v1/' ),
		'strings' => array(
			'academicUnitNameLabel' => _x( 'Name', '"Name" label for adding new academic units', 'cbox-openlab-core' ),
			'academicUnitParentLegend' => __( 'Define a parent/child relationship to indicate which category from the parent Academic Unit Type this new category should be associated with.', 'cbox-openlab-core' ),
			'action' => _x( 'Action', 'Header for Action column in admin tables', 'cbox-openlab-core' ),
			'add' => _x( 'Add', '"Add" button text', 'cbox-openlab-core' ),
			'addEmailDomain' => __( 'Add email domain', 'cbox-openlab-core' ),
			'addNewAcademicUnit' => __( 'Add New Academic Unit', 'cbox-openlab-core' ),
			'addNewAcademicUnitTitle' => __( 'Add New', 'cbox-openlab-core' ),
			'addNewCategory' => __( 'Add New Category', 'cbox-openlab-core' ),
			'addNewType' => _x( 'Add New Type', 'placeholder for new item type form', 'cbox-openlab-core' ),
			'associatedWithGroupTypes' => __( 'Associated with Group Types', 'cbox-openlab-core' ),
			'associatedWithMemberTypes' => __( 'Associated with Member Types', 'cbox-openlab-core' ),
			'cancel' => __( 'Cancel', 'cbox-openlab-core' ),
			'code' => _x( 'Code', 'Column header for signup code value', 'cbox-openlab-core' ),
			'confirmationText' => __( 'Confirmation Text', 'cbox-openlab-core' ),
			'confirmationTextLegend' => __( 'The text that appears just above the "Complete Sign Up" button on the registration form.', 'cbox-openlab-core' ),
			'count' => _x( 'Count', 'Column header', 'cbox-openlab-core' ),
			'delete' => __( 'Delete', 'cbox-openlab-core' ),
			'deleteConfirm' => __( 'Are you sure you want to delete this content?', 'cbox-openlab-core' ),
			'domain' => _x( 'Domain', 'Domain from email domain whitelist', 'cbox-openlab-core' ),
			'edit' => __( 'Edit', 'cbox-openlab-core' ),
			'editing' => __( 'Editing', 'cbox-openlab-core' ),
			'emailDomainWhitelist' => __( 'Email Domain Whitelist', 'cbox-openlab-core' ),
			'emailDomainWhitelistLegend' => __( 'To limit new user registrations to one or multiple email domains, include them here. Only users with emails matching the whitelisted domain(s) will be allowed to register for accounts. Wildcards are supported for multiple formats of the same base domain (e.g. *.schoolname.edu).', 'cbox-openlab-core' ),
			'enterSignupCode' => __( 'Enter Signup Code', 'cbox-openlab-core' ),
			'formCustomization' => __( 'Form Customization', 'cbox-openlab-core' ),
			'formCustomizationLegend' => __( 'Use these settings to customize the registration form.', 'cbox-openlab-core' ),
			'formCustomizationSave' => __( 'Save Form Customization Settings', 'cbox-openlab-core' ),
			'group' => _x( 'Group', 'Column header for signup code table', 'cbox-openlab-core' ),
			'itemTypeNameLabel' => _x( 'Name', 'item type Name label', 'cbox-openlab-core' ),
			'labels' => _x( 'Labels', 'subheader for item type labels', 'cbox-openlab-core' ),
			'mayCreateCoursesLegend' => __( 'Members may create courses', 'cbox-openlab-core' ),

			// @todo This probably will not translate.
			'mayChangeMemberTypeToLegend' => __( 'Members may change Type to', 'cbox-openlab-core' ),
			'memberType' => __( 'Member Type', 'cbox-openlab-core' ),

			'name' => _x( 'Name', 'table header', 'cbox-openlab-core' ),
			'no' => _x( 'No', 'radio button option', 'cbox-openlab-core' ),
			'noEmailDomains' => __( 'Registration is currently open for all email domains. Enter one or more domains to restrict registration by email address.', 'cbox-openlab-core' ),
			'none' => _x( 'None', 'null dropdown option', 'cbox-openlab-core' ),
			'noSignupCodes' => __( 'Currently, users may select any Member Type when creating or editing their accounts. To restrict access to a Member Type, create a corresponding Signup Code below.', 'cbox-openlab-core' ),
			'noUnitsOfType' => __( 'There are no units of this type.', 'cbox-openlab-core' ),
			'off' => _x( '(Off)', 'disabled label for item type', 'cbox-openlab-core' ),
			'onOffSwitchLabel' => __( 'On/off toggle', 'cbox-openlab-core' ),
			'optional' => __( 'Optional', 'cbox-openlab-core' ),
			'orderDescription' => __( 'Used when displaying lists of types throughout the site.', 'cbox-openlab-core' ),
			'orderLegend' => __( 'Order', 'cbox-openlab-core' ),
			'parent' => __( 'Parent', 'cbox-openlab-core' ),
			'required' => __( 'Required', 'cbox-openlab-core' ),
			'save' => __( 'Save', 'cbox-openlab-core' ),
			'saveChanges' => __( 'Save Changes', 'cbox-openlab-core' ),
			'saved' => __( 'Saved!', 'cbox-openlab-core' ),
			'saving' => __( 'Saving', 'cbox-openlab-core' ),
			'settings' => _x( 'Settings', 'subheader for item type settings', 'cbox-openlab-core' ),
			'selectAll' => __( 'Select All', 'cbox-openlab-core' ),
			'selectGroup' => __( 'Select Group', 'cbox-openlab-core' ),
			'selectMemberType' => __( 'Select Member Type', 'cbox-openlab-core' ),
			'selectUnit' => _x( 'Select Unit: %s', 'checkbox screen reader text', 'cbox-openlab-core' ),
			'signUpCodes' => __( 'Sign Up Codes', 'cbox-openlab-core' ),
			'signUpCodesLegend' => __( 'Registration codes let you restrict access to specific member account types (e.g faculty, staff, student). Each code can be associated with a group, so that users registering with the code will automatically be added to the group when their registration is complete. These account codes do not allow users to bypass the Email Domain Whitelist above.', 'cbox-openlab-core' ),
			'template' => _x( 'Template', 'subheader for template site settings section', 'cbox-openlab-core' ),
			'templateDashboardLink' => _x( 'Dashboard', 'template site dashboard link', 'cbox-openlab-core' ),
			'templateSiteDescription' => __( 'When a group of this type creates a site, default settings and data will be copied from the group type\'s template site. Use the links below to view and configure the template site.', 'cbox-openlab-core' ),
			'templateViewLink' => _x( 'View Template', 'template site view link', 'cbox-openlab-core' ),
			'thisGroupTypeIsDesignedForCourses' => __( 'Note: This Group Type is designed for Courses.', 'cbox-openlab-core' ),
			'thisGroupTypeIsDesignedForPortfolios' => __( 'Note: This Group Type is designed for Portfolios.', 'cbox-openlab-core' ),
			'update' => __( 'Update', 'cbox-openlab-core' ),
			'yes' => _x( 'Yes', 'radio button option', 'cbox-openlab-core' ),
		),
	) );

	wp_register_style( 'cbox-ol-admin', CBOXOL_PLUGIN_URL . 'assets/css/admin.css', array(), cboxol_get_asset_version() );
	// @todo More specific.
	wp_enqueue_style( 'cbox-ol-admin' );
}

function cboxol_admin_about_page() {
	echo 'This is the about page.';
}

function cboxol_admin_slug( $parent_page = '' ) {
	switch ( $parent_page ) {
		case 'member-settings' :
			return 'cbox-ol-member-settings';

		case 'group-settings' :
			return 'cbox-ol-group-settings';

		case 'brand-settings' :
			return 'cbox-ol-brand-settings';

		case 'academic-units' :
			return 'cbox-ol-academic-units';

		case 'communication-settings' :
			return 'cbox-ol-communication-settings';

		default :
			return 'cbox';
	}
}

function cboxol_admin_page_label( $page ) {
	switch ( $page ) {
		case 'member-settings' :
			return __( 'Member Settings', 'cbox-openlab-core' );

		case 'group-settings' :
			return __( 'Group Settings', 'cbox-openlab-core' );

		case 'brand-settings' :
			return __( 'Brand Settings', 'cbox-openlab-core' );

		case 'communication-settings' :
			return __( 'Communication Settings', 'cbox-openlab-core' );

		case 'academic-units' :
			return __( 'Academic Units', 'cbox-openlab-core' );
	}
}

function cboxol_admin_subpage_label( $parent_page, $page ) {
	switch ( $parent_page ) {
		case 'member-settings' :
			switch ( $page ) {
				case 'types' :
					return _x( 'Types', 'Member Types admin label', 'cbox-openlab-core' );

				case 'registration' :
					return _x( 'Registration', 'Registration admin label', 'cbox-openlab-core' );

				case 'profile-fields' :
					return _x( 'Profile Fields', 'Member profile fields admin label', 'cbox-openlab-core' );
			}

		case 'group-settings' :
			switch ( $page ) {
				case 'types' :
					return _x( 'Types', 'Group Types admin label', 'cbox-openlab-core' );

				case 'group-categories' :
					return _x( 'Group Categories', 'Group categories admin label', 'cbox-openlab-core' );
			}

		case 'communication-settings' :
			switch ( $page ) {
				case 'email' :
					return _x( 'Email', 'Communication Settings admin label', 'cbox-openlab-core' );

				case 'invitations' :
					return _x( 'Invitations', 'Communication Settings admin label', 'cbox-openlab-core' );
			}
	}
}

function cboxol_admin_header( $parent_page, $sub_page ) {
	$parent_title = $sub_title = '';

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
		$is_current = (bool) ( $tab_data['name'] == $active_tab );
		$tab_class  = $is_current ? $active_class : $idle_class;
		$tabs_html .= '<a href="' . esc_url( $tab_data['href'] ) . '" class="' . esc_attr( $tab_class ) . '">' . esc_html( $tab_data['label'] ) . '</a>';
	}

	echo '<h2 class="nav-tab-wrapper">' . $tabs_html . '</h2>';
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
		case 'member-settings' :
			$base = self_admin_url( add_query_arg( 'page', cboxol_admin_slug( 'member-settings' ), 'admin.php' ) );
			$tabs = array(
				'0' => array(
					'href' => add_query_arg( 'cboxol-section', 'types', $base ),
					'name' => 'types',
					'label' => cboxol_admin_subpage_label( 'member-settings', 'types' ),
				),
				'1' => array(
					'href' => add_query_arg( 'cboxol-section', 'registration', $base ),
					'name' => 'registration',
					'label' => cboxol_admin_subpage_label( 'member-settings', 'registration' ),
				),
				'2' => array(
					'href' => add_query_arg( 'cboxol-section', 'profile-fields', $base ),
					'name' => 'profile-fields',
					'label' => cboxol_admin_subpage_label( 'member-settings', 'profile-fields' ),
				),
			);

			break;

		case 'group-settings' :
			$base = self_admin_url( add_query_arg( 'page', cboxol_admin_slug( 'group-settings' ), 'admin.php' ) );
			$tabs = array(
				'0' => array(
					'href' => add_query_arg( 'cboxol-section', 'types', $base ),
					'name' => 'types',
					'label' => cboxol_admin_subpage_label( 'group-settings', 'types' ),
				),
				'1' => array(
					'href' => add_query_arg( 'cboxol-section', 'group-categories', $base ),
					'name' => 'group-categories',
					'label' => cboxol_admin_subpage_label( 'group-settings', 'group-categories' ),
				),
			);

			break;

		case 'communication-settings' :
			$base = self_admin_url( add_query_arg( 'page', cboxol_admin_slug( 'communication-settings' ), 'admin.php' ) );
			$tabs = array(
				'0' => array(
					'href' => add_query_arg( 'cboxol-section', 'email', $base ),
					'name' => 'email',
					'label' => cboxol_admin_subpage_label( 'communication-settings', 'email' ),
				),
				'1' => array(
					'href' => add_query_arg( 'cboxol-section', 'invitations', $base ),
					'name' => 'invitations',
					'label' => cboxol_admin_subpage_label( 'communication-settings', 'invitations' ),
				),
			);
	}

	return $tabs;
}

function cboxol_admin_section_content( $parent_page, $sub_page ) {
	switch ( $parent_page ) {
		case 'member-settings' :
			switch ( $sub_page ) {
				case 'types' :
					cboxol_membertypes_admin_page();
				break;

				case 'registration' :
					cboxol_registration_admin_page();
				break;

				case 'profile-fields' :
					cboxol_profile_fields_admin_page();
				break;
			}
		break;

		case 'group-settings' :
			switch ( $sub_page ) {
				case 'types' :
					cboxol_grouptypes_admin_page();
				break;

				case 'group-categories' :
					cboxol_groupcategories_admin_page();
				break;
			}

		break;

		case 'brand-settings' :
			cboxol_brand_admin_page();
		break;

		case 'communication-settings' :
			switch ( $sub_page ) {
				case 'email' :
					cboxol_communication_admin_page_email();
				break;

				case 'invitations' :
					cboxol_communication_admin_page_invitations();
				break;
			}
		break;

		case 'academic-units' :
			cboxol_academic_units_main_admin_page();
		break;
	}
}

function cboxol_group_settings_admin_page() {
	$current_section = isset( $_GET['cboxol-section'] ) ? wp_unslash( $_GET['cboxol-section'] ) : 'types';
	cboxol_admin_page( 'group-settings', $current_section );
}

function cboxol_member_settings_admin_page() {
	$current_section = isset( $_GET['cboxol-section'] ) ? wp_unslash( $_GET['cboxol-section'] ) : 'types';
	cboxol_admin_page( 'member-settings', $current_section );
}

function cboxol_brand_settings_admin_page() {
	$current_section = '';
	cboxol_admin_page( 'brand-settings', $current_section );
}

function cboxol_communication_settings_admin_page() {
	$current_section = isset( $_GET['cboxol-section'] ) ? wp_unslash( $_GET['cboxol-section'] ) : 'email';
	cboxol_admin_page( 'communication-settings', $current_section );
}

function cboxol_academic_units_admin_page() {
	$current_section = '';
	cboxol_admin_page( 'academic-units', $current_section );
}

function cboxol_admin_page( $parent_page, $current_section ) {
	?>

	<div class="wrap cboxol-admin-wrap cboxol-admin-<?php echo esc_attr( $parent_page ); ?> <?php if ( $current_section ) : ?>cboxol-admin-<?php echo esc_attr( $parent_page ); ?>-<?php echo esc_attr( $current_section ); ?><?php endif; ?>">
		<?php cboxol_admin_header( $parent_page, $current_section ); ?>

		<?php cboxol_admin_section_content( $parent_page, $current_section ); ?>
	</div>

	<?php
}
