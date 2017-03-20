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

		default :
			return 'cbox-ol';
	}
}

function cboxol_admin_page_label( $page ) {
	switch ( $page ) {
		case 'member-settings' :
			return __( 'Member Settings', 'cbox-openlab-core' );
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
	}

	return $tabs;
}
