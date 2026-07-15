<?php

/**
 * "Privacy" dashboard widget.
 *
 * @package cbox-openlab-core
 */

namespace CBOX\OL\DashboardWidget;

/**
 * "Privacy" dashboard widget.
 *
 * @since 1.8.0
 */
class Privacy {
	/**
	 * Registers the widget.
	 */
	public static function register() {
		add_action( 'wp_dashboard_setup', [ __CLASS__, 'add_widget' ], 3 );
	}

	/**
	 * Adds the widget to the dashboard.
	 */
	public static function add_widget() {
		wp_add_dashboard_widget(
			'cboxol_group_privacy_dashboard_widget',
			__( 'Privacy', 'commons-in-a-box' ),
			[ __CLASS__, 'render_widget' ],
			null,
			null,
			'normal',
			'high'
		);
	}

	/**
	 * Renders the content of the widget.
	 */
	public static function render_widget() {
		$blog_public = get_option( 'blog_public' );

		$strings = [
			'1'  => __( 'Public: Search Engines are allowed to index this site. The site will show up in web search results.', 'commons-in-a-box' ),
			'0'  => __( 'Public: Search Engines have been asked not to index this site. The site should not show up in web search results.<br /><em>Note: This option will NOT block access to the site. It is up to search engines to honor your request</em>.', 'commons-in-a-box' ),
			'-1' => __( 'Private: This site is visible only to members of the community', 'commons-in-a-box' ),
			'-2' => __( 'Private: This site is visible only to members of the community with a role on the site.', 'commons-in-a-box' ),
			'-3' => __( 'Hidden: This site is visible only to members of the community with an administrator role on the site.', 'commons-in-a-box' ),
		];

		$group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );

		$group_settings_link = bp_get_group_manage_url( $group_id, bp_groups_get_path_chunks( [ 'site-details' ], 'settings' ) );

		?>

		<p>
			<?php echo wp_kses_post( $strings[ $blog_public ] ); ?>
		</p>

		<?php if ( current_user_can( 'manage_options' ) ) : ?>
			<p>
				<a href="<?php echo esc_url( $group_settings_link ); ?>">
					<?php esc_html_e( 'Change Site Privacy Settings', 'commons-in-a-box' ); ?>
				</a>
			</p>
		<?php endif; ?>

		<?php
	}
}
