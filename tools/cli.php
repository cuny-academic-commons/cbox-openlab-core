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

		delete_site_option( 'cboxol_ver' );

		// Perform theme resets as well.
		$this->reset_theme();
	}

	public function reset_theme() {
		if ( 'openlab-theme' !== get_stylesheet() ) {
			return;
		}

		// This doesn't work but I don't have the patience to fix it.
		wp_set_sidebars_widgets( array() );
		remove_theme_mod( 'sidebars_widgets' );

		wp_delete_nav_menu( 'Main Menu' );
		update_option( 'nav_menu_locations', array() );

		remove_action( 'after_switch_theme', '_wp_sidebars_changed' );

		update_option( 'theme_switched', 'openlab-theme' );
		delete_option( 'openlab_theme_installed' );
	}
}

WP_CLI::add_command( 'cboxol', 'CBOXOL_Command' );
