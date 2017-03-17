<?php

/**
 * General admin functionality.
 */

add_action( 'admin_menu', 'cboxol_register_admin_menu' );

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
		cboxol_admin_slug( 'member-settings' ),
		'cboxol_membertypes_admin_page',
		'',
		2
	);
}

function cboxol_admin_about_page() {
	echo 'This is the about page.';
}

function cboxol_admin_slug( $page = '' ) {
	switch ( $page ) {
		case 'member-settings' :
			return 'cbox-ol-member-settings';

		default :
			return 'cbox-ol';
	}
}

function cboxol_admin_header( $parent_page, $sub_page ) {
	$parent_title = $sub_title = '';

	switch ( $parent_page ) {
		case 'member-settings' :
			$parent_title = __( 'Member Settings', 'cbox-openlab-core' );

			switch ( $sub_page ) {
				case 'types' :
					$sub_title = _x( 'Types', 'Member Types subnav title', 'cbox-openlab-core' );
					break;
			}
			break;
	}

	$title = sprintf(
		'<h2 class="page-title">%s: %s</h2>',
		$parent_title,
		$sub_title
	);

	$html = $title;

	echo $html;
}
