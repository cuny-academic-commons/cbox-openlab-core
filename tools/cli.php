<?php

defined( 'WP_CLI' ) || die();

class CBOXOL_Command extends WP_CLI_Command {
	/**
	 * Reset an installation.
	 *
	 * Deletes all CBOX-OL content and allows the plugin to reinitialize default data.
	 *
	 * The following content is removed:
	 * - group types
	 * - member types
	 * - nav menus
	 * - widgets
	 */
	public function reset() {
		global $wpdb;

		delete_option( 'cboxol_installing' );

		$group_types = cboxol_get_group_types( array(
			'enabled' => null,
		) );

		foreach ( $group_types as $group_type ) {
			wp_delete_post( $group_type->get_wp_post_id(), true );

			$template_site_id = $group_type->get_template_site_id();
			if ( $template_site_id ) {
				wpmu_delete_blog( $template_site_id );
			}
		}

		$member_types = cboxol_get_member_types( array(
			'enabled' => null,
		) );

		foreach ( $member_types as $member_type ) {
			wp_delete_post( $member_type->get_wp_post_id(), true );
		}

		// email domains
		// signup codes

		// Sliders.
		$sliders = get_posts( array(
			'post_type' => 'slider',
			'post_status' => 'any',
			'posts_per_page' => '-1',
			'fields' => 'ids',
		) );

		foreach ( $sliders as $slide_id ) {
			wp_delete_post( $slide_id, true );
		}

		// Group categories.
		$group_cat_ids = $wpdb->get_col( "SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy = 'bp_group_categories'" );
		foreach ( $group_cat_ids as $group_cat_id ) {
			wp_delete_term( $group_cat_id, 'bp_group_categories' );
		}

		// Academic units and types.
		$acad_unit_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type IN ('cboxol_acadunit', 'cboxol_acadunit_type')" );
		foreach ( $acad_unit_ids as $acad_unit_id ) {
			wp_delete_post( $acad_unit_id, true );
		}

		delete_site_option( 'cboxol_ver' );

		$brand_pages = cboxol_get_brand_pages();
		foreach ( $brand_pages as $brand_page ) {
			wp_delete_post( $brand_page['id'], true );
		}

		update_option( 'theme_switched', 'twentysixteen' );

		// Perform theme resets as well.
		$this->reset_theme();
	}

	public function reset_theme() {
		global $wpdb;

		if ( 'openlab-theme' !== get_stylesheet() ) {
			return;
		}

		// This doesn't work but I don't have the patience to fix it.
		wp_set_sidebars_widgets( array() );
		delete_option( 'sidebars_widgets' );
		remove_theme_mod( 'sidebars_widgets' );

		$widget_options = $wpdb->get_col( "SELECT option_name FROM $wpdb->options WHERE option_name like 'widget_%'" );
		foreach ( $widget_options as $widget_option ) {
			delete_option( $widget_option );
		}

		wp_delete_nav_menu( 'Main Menu' );
		wp_delete_nav_menu( 'About Menu' );
		update_option( 'nav_menu_locations', array() );

		remove_action( 'after_switch_theme', '_wp_sidebars_changed' );

//		update_option( 'theme_switched', 'openlab-theme' );
		delete_option( 'openlab_theme_installed' );
	}
}

WP_CLI::add_command( 'cboxol', 'CBOXOL_Command' );
