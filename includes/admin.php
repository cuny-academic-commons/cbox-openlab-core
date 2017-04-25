<?php

/**
 * General admin functionality.
 */

add_action( 'admin_menu', 'cboxol_register_admin_menu' );
add_action( 'admin_enqueue_scripts', 'cboxol_register_assets' );
add_action( 'admin_init', 'cboxol_catch_form_submit' );

function cboxol_register_admin_menu() {
	// @todo only add on "main" site
	// @todo icon
	// @todo How do I make it "About" as first option
	add_menu_page(
		__( 'OpenLab Setup', 'cbox-openlab-core' ),
		__( 'OpenLab Setup', 'cbox-openlab-core' ),
		'manage_network_options',
		cboxol_admin_slug(),
		'cboxol_admin_about_page',
		'',
		2
	);

	add_submenu_page(
		cboxol_admin_slug(),
		__( 'Group Settings', 'cbox-openlab-core' ),
		__( 'Group Settings', 'cbox-openlab-core' ),
		'manage_network_options',
		cboxol_admin_slug( 'group-settings', 'types' ),
		'cboxol_grouptypes_admin_page',
		'',
		2
	);

	add_submenu_page(
		cboxol_admin_slug(),
		__( 'Member Settings', 'cbox-openlab-core' ),
		__( 'Member Settings', 'cbox-openlab-core' ),
		'manage_network_options',
		cboxol_admin_slug( 'member-settings', 'types' ),
		'cboxol_membertypes_admin_page',
		'',
		2
	);
}

function cboxol_register_assets() {
	wp_register_script(
		'cbox-ol-app',
		CBOXOL_PLUGIN_URL . 'assets/js/build.js',
		array(),
		123, // @todo
		true
	);

	wp_localize_script( 'cbox-ol-app', 'CBOXOLStrings', array(
		'nonce' => wp_create_nonce( 'wp_rest' ),
		'endpoint' => home_url( '/wp-json/cboxol/v1/item-type/' ),
		'strings' => array(
			'addNewType' => _x( 'Add New Type', 'placeholder for new item type form', 'cbox-openlab-core' ),
			'delete' => __( 'Delete', 'cbox-openlab-core' ),
			'deleteConfirm' => __( 'Are you sure you want to delete this content?', 'cbox-openlab-core' ),
			'edit' => __( 'Edit', 'cbox-openlab-core' ),
			'editing' => __( 'Editing', 'cbox-openlab-core' ),
			'itemTypeNameLabel' => _x( 'Name', 'item type Name label', 'cbox-openlab-core' ),
			'labels' => _x( 'Labels', 'subheader for item type labels', 'cbox-openlab-core' ),
			'mayCreateCoursesLegend' => __( 'Members may create courses', 'cbox-openlab-core' ),

			// @todo This probably will not translate.
			'mayChangeMemberTypeToLegend' => __( 'Members may change Type to', 'cbox-openlab-core' ),

			'no' => _x( 'No', 'radio button option', 'cbox-openlab-core' ),
			'off' => _x( '(Off)', 'disabled label for item type', 'cbox-openlab-core' ),
			'orderDescription' => __( 'Used when displaying lists of types throughout the site.', 'cbox-openlab-core' ),
			'orderLegend' => __( 'Order', 'cbox-openlab-core' ),
			'saveChanges' => __( 'Save Changes', 'cbox-openlab-core' ),
			'saved' => __( 'Saved!', 'cbox-openlab-core' ),
			'saving' => __( 'Saving', 'cbox-openlab-core' ),
			'settings' => _x( 'Settings', 'subheader for item type settings', 'cbox-openlab-core' ),
			'template' => _x( 'Template', 'subheader for template site settings section', 'cbox-openlab-core' ),
			'templateDashboardLink' => _x( 'Dashboard', 'template site dashboard link', 'cbox-openlab-core' ),
			'templateSiteDescription' => __( 'When a group of this type creates a site, default settings and data will be copied from the group type\'s template site. Use the links below to view and configure the template site.', 'cbox-openlab-core' ),
			'templateViewLink' => _x( 'View Template', 'template site view link', 'cbox-openlab-core' ),
			'yes' => _x( 'Yes', 'radio button option', 'cbox-openlab-core' ),
		),
	) );

	wp_register_style( 'cbox-ol-admin', CBOXOL_PLUGIN_URL . 'assets/css/admin.css' );
	// @todo More specific.
	wp_enqueue_style( 'cbox-ol-admin' );
}

function cboxol_catch_form_submit() {
	global $pagenow;

	if ( 'admin.php' !== $pagenow ) {
		return;
	}

	if ( empty( $_GET['page'] ) ) {
		return;
	}

	if ( empty( $_POST ) ) {
		return;
	}

	switch ( $_GET['page'] ) {
		// @todo this is no longer correct
		case cboxol_admin_slug( 'member-settings', 'types' ) :
			cboxol_membertypes_process_form_submit();
			break;
	}
}

function cboxol_admin_about_page() {
	echo 'This is the about page.';
}

function cboxol_admin_slug( $parent_page = '', $sub_page = '' ) {
	switch ( $parent_page ) {
		case 'member-settings' :
			switch ( $sub_page ) {
				case 'types' :
					return 'cbox-ol-member-types';

				case 'signup-codes' :
					return 'cbox-ol-signup-codes';

				case 'categories' :
					return 'cbox-ol-member-categories';

				// @todo this will probably go to the BP screen?
				case 'profile-fields' :
					return 'cbox-ol-profile-fields';

				default :
					return 'cbox-ol-member-settings';
			}

		case 'group-settings' :
			switch ( $sub_page ) {
				case 'types' :
					return 'cbox-ol-group-types';

				case 'group-categories' :
					return 'cbox-ol-group-categories';

				case 'sort-group-categories' :
					return 'cbox-ol-sort-group-categories';
			}

		default :
			return 'cbox-ol';
	}
}

function cboxol_admin_page_label( $page ) {
	switch ( $page ) {
		case 'member-settings' :
			return __( 'Member Settings', 'cbox-openlab-core' );

		case 'group-settings' :
			return __( 'Group Settings', 'cbox-openlab-core' );
	}
}

function cboxol_admin_subpage_label( $parent_page, $page ) {
	switch ( $parent_page ) {
		case 'member-settings' :
			switch ( $page ) {
				case 'types' :
					return _x( 'Types', 'Member Types admin label', 'cbox-openlab-core' );

				case 'signup-codes' :
					return _x( 'Signup Codes', 'Signup Codes admin label', 'cbox-openlab-core' );

				case 'categories' :
					return _x( 'Categories', 'Member categories admin label', 'cbox-openlab-core' );

				case 'profile-fields' :
					return _x( 'Profile Fields', 'Member profile fields admin label', 'cbox-openlab-core' );
			}

		case 'group-settings' :
			switch ( $page ) {
				case 'types' :
					return _x( 'Types', 'Group Types admin label', 'cbox-openlab-core' );

				case 'group-categories' :
					return _x( 'Group Categories', 'Group categories admin label', 'cbox-openlab-core' );

				case 'sort-group-categories' :
					return _x( 'Sort Group Categories', 'Sort group categories admin label', 'cbox-openlab-core' );
			}
	}
}

function cboxol_admin_header( $parent_page, $sub_page ) {
	$parent_title = $sub_title = '';

	$title = sprintf(
		'<h1>%s: %s</h1>',
		cboxol_admin_page_label( $parent_page ),
		cboxol_admin_subpage_label( $parent_page, $sub_page )
	);

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
	switch ( $parent_page ) {
		case 'member-settings' :
			$tabs = array(
				'0' => array(
					'href' => admin_url( add_query_arg( array( 'page' => cboxol_admin_slug( 'member-settings', 'types' ) ), 'admin.php' ) ),
					'name' => 'types',
					'label' => cboxol_admin_subpage_label( 'member-settings', 'types' ),
				),
				'1' => array(
					'href' => admin_url( add_query_arg( array( 'page' => cboxol_admin_slug( 'member-settings', 'signup-codes' ) ), 'admin.php' ) ),
					'name' => 'signup-codes',
					'label' => cboxol_admin_subpage_label( 'member-settings', 'signup-codes' ),
				),
				'2' => array(
					'href' => admin_url( add_query_arg( array( 'page' => cboxol_admin_slug( 'member-settings', 'categories' ) ), 'admin.php' ) ),
					'name' => 'categories',
					'label' => cboxol_admin_subpage_label( 'member-settings', 'categories' ),
				),
				'3' => array(
					'href' => admin_url( add_query_arg( array( 'page' => cboxol_admin_slug( 'member-settings', 'profile-fields' ) ), 'admin.php' ) ),
					'name' => 'profile-fields',
					'label' => cboxol_admin_subpage_label( 'member-settings', 'profile-fields' ),
				),
			);

			break;

		case 'group-settings' :
			$tabs = array(
				'0' => array(
					'href' => admin_url( add_query_arg( array( 'page' => cboxol_admin_slug( 'group-settings', 'types' ) ), 'admin.php' ) ),
					'name' => 'types',
					'label' => cboxol_admin_subpage_label( 'group-settings', 'types' ),
				),
				'1' => array(
					'href' => admin_url( add_query_arg( array( 'page' => cboxol_admin_slug( 'group-settings', 'group-categories' ) ), 'admin.php' ) ),
					'name' => 'group-categories',
					'label' => cboxol_admin_subpage_label( 'group-settings', 'group-categories' ),
				),
				'2' => array(
					'href' => admin_url( add_query_arg( array( 'page' => cboxol_admin_slug( 'group-settings', 'sort-group-categories' ) ), 'admin.php' ) ),
					'name' => 'sort-group-categories',
					'label' => cboxol_admin_subpage_label( 'group-settings', 'sort-group-categories' ),
				),
			);

			break;
	}

	return $tabs;
}
