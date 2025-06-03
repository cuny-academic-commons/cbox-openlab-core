<?php
/**
 * Functionality related to the Dashboard Panel feature.
 *
 * @since 1.7.0
 */

namespace CBOX\OL\DashboardPanel;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Gets the settings for the Dashboard Panel.
 *
 * @since 1.7.0
 *
 * @return array The settings for the Dashboard Panel.
 */
function get_dashboard_panel_settings() {
	$defaults = [
		'allow_dismissal' => false,
		'enabled'         => false,
		'heading'         => '',
		'tagline'         => '',
		'panel_1_heading' => '',
		'panel_1_text'    => '',
		'panel_2_heading' => '',
		'panel_2_text'    => '',
		'panel_3_heading' => '',
		'panel_3_text'    => '',
	];

	$settings = get_site_option( 'cboxol_dashboard_panel_settings' );
	if ( ! is_array( $settings ) ) {
		$settings = [];
	}

	$settings = wp_parse_args( $settings, $defaults );

	return $settings;
}

/**
 * Displays Dashboard Panel.
 *
 * @return void
 */
function display() {
	global $pagenow;

	// Only enqueue on the main Dashboard page.
	if ( 'index.php' !== $pagenow ) {
		return;
	}

	$settings = get_dashboard_panel_settings();
	if ( ! $settings['enabled'] ) {
		return;
	}

	// todo dismissal
	$panel_is_visible = is_panel_visible_for_user();

	wp_enqueue_script(
		'cboxol-dashboard-panel',
		CBOXOL_PLUGIN_URL . 'assets/js/dashboard-panel.js',
		[],
		CBOXOL_PLUGIN_VER,
		true
	);

	wp_enqueue_style(
		'cboxol-dashboard-panel',
		CBOXOL_PLUGIN_URL . 'assets/css/dashboard-panel.css',
		[],
		CBOXOL_PLUGIN_VER
	);

	$mods = get_theme_mods();

	$text_color       = '';
	$background_color = '';
	switch ( openlab_get_color_scheme() ) {
		case 'blue':
			$background_color = '#8ccae4';
			$text_color       = '#1d2327';
			break;
		case 'red':
			$background_color = '#a9280e';
			$text_color       = '#ffffff';
			break;
		case 'green':
		default:
			$background_color = '#b6d498';
			$text_color       = '#1d2327';
			break;
	}

	wp_add_inline_script(
		'cboxol-dashboard-panel',
		'const CBOXOLDashboardPanel = ' . wp_json_encode(
			[
				'backgroundColor' => $background_color,
				'panelIsVisible'  => (bool) $panel_is_visible,
				'textColor'       => $text_color,
			]
		) . ';',
		'before'
	);

	$hidden_class = $panel_is_visible ? '' : ' hidden';

	$panels = [ 'panel_1', 'panel_2', 'panel_3' ];

	?>
	<div id="openlab-news-panel" class="openlab-news-panel-content <?php echo esc_attr( $hidden_class ); ?>">
		<div class="panel-header">
			<div class="panel-header-content">
				<h2><?php echo esc_html( $settings['heading'] ); ?></h2>
				<p>
					<?php echo wp_kses_post( $settings['tagline'] ); ?>
				</p>
			</div>
		</div>

		<div class="panel-column-container">
			<?php foreach ( $panels as $panel ) : ?>
				<div class="panel-column">
					<?php
					/*
					<div class="panel-icon">
						<i class="fa <?php echo esc_attr( $announcement->get( 'icon' ) ); ?>"></i>
					</div>
					*/
					?>

					<div class="welcome-panel-column-content">
						<h3><?php echo esc_html( $settings[ $panel . '_heading' ] ); ?></h3>
						<?php echo wp_kses_post( $settings[ $panel . '_text' ] ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php wp_nonce_field( 'openlab-news-panel-nonce', 'openlab-news-panel-nonce', false ); ?>
	</div>
	<?php
}
add_action( 'in_admin_footer', __NAMESPACE__ . '\\display', 20 );

/**
 * AJAX callback to hide the Dashboard Panel.
 *
 * @return void
 */
function dismiss_ajax_cb() {
	check_ajax_referer( 'openlab-news-panel-nonce', 'nonce' );

	$visible = isset( $_POST['visible'] ) && 'false' === $_POST['visible'] ? 0 : 1;

	set_panel_is_visible_for_user( get_current_user_id(), (bool) $visible );
}
add_action( 'wp_ajax_cboxol_hide_dashboard_panel', __NAMESPACE__ . '\\dismiss_ajax_cb' );

/**
 * Sets whether the news panel should be visible for the current user.
 *
 * @param int  $user_id Optional. User ID. Defaults to current user.
 * @param bool $visible Optional. Whether the panel should be visible. Defaults to true.
 * @return void
 */
function set_panel_is_visible_for_user( $user_id = 0, $visible = true ) {
	$site_hidden_users = get_hidden_users();

	if ( $visible ) {
		$site_hidden_users = array_diff( $site_hidden_users, [ $user_id ] );
	} else {
		$site_hidden_users[] = $user_id;
	}

	update_blog_option( get_current_blog_id(), 'cboxol_dashboard_panel_hidden_users', $site_hidden_users );
}

/**
 * Gets a list of site users for whom the news panel is hidden.
 *
 * @param int $site_id Optional. Site ID. Defaults to current site.
 * @return int[]
 */
function get_hidden_users( $site_id = 0 ) {
	$site_hidden_users = get_blog_option( $site_id, 'cboxol_dashboard_panel_hidden_users', [] );
	if ( ! is_array( $site_hidden_users ) ) {
		$site_hidden_users = [];
	}

	$site_hidden_users = array_map( 'intval', $site_hidden_users );

	return $site_hidden_users;
}

/**
 * Determines whether the news panel should be visible for the current user.
 *
 * @param int $user_id Optional. User ID. Defaults to current user.
 * @param int $site_id Optional. Site ID. Defaults to current site.
 * @return bool
 */
function is_panel_visible_for_user( $user_id = 0, $site_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $site_id ) {
		$site_id = get_current_blog_id();
	}

	$panel_is_visible = ! in_array( $user_id, get_hidden_users(), true );

	return (bool) $panel_is_visible;
}
