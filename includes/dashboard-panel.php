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
		'panel_1_icon'    => 'check-circle',
		'panel_2_heading' => '',
		'panel_2_text'    => '',
		'panel_2_icon'    => 'check-circle',
		'panel_3_heading' => '',
		'panel_3_text'    => '',
		'panel_3_icon'    => 'check-circle',
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

	wp_add_inline_style(
		'cboxol-dashboard-panel',
		sprintf(
			'.openlab-news-panel-content a { color: %s; }
			.openlab-news-panel-content .panel-dismiss::before { color: %s; }',
			esc_attr( $text_color ),
			esc_attr( $text_color )
		)
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
					<div class="panel-icon">
						<?php
						$icon_name = $settings[ $panel . '_icon' ];

						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo get_dashboard_panel_icon( $icon_name );
						?>
					</div>

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

/**
 * Gets a dashboard panel SVG icon.
 *
 * @param string $icon_name The name of the icon.
 * @return string The SVG icon markup.
 */
function get_dashboard_panel_icon( $icon_name ) {
	$icons = get_dashboard_panel_icons();

	if ( ! isset( $icons[ $icon_name ] ) ) {
		return '';
	}

	return $icons[ $icon_name ]['svg'];
}

/**
 * Gets a list of all icons, for building the icon selector.
 *
 * @return array An associative array of icon names and their SVG markup.
 */
function get_dashboard_panel_icons() {
	return [
		'check-circle'     => [
			'name' => _x( 'Check', 'Dashboard Panel Icon', 'commons-in-a-box' ),
			'svg'  => sprintf(
				'<svg class="dashboard-panel-icon-check-circle" aria-label="%s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 48c110.5 0 200 89.5 200 200 0 110.5-89.5 200-200 200-110.5 0-200-89.5-200-200 0-110.5 89.5-200 200-200m140.2 130.3l-22.5-22.7c-4.7-4.7-12.3-4.7-17-.1L215.3 303.7l-59.8-60.3c-4.7-4.7-12.3-4.7-17-.1l-22.7 22.5c-4.7 4.7-4.7 12.3-.1 17l90.8 91.5c4.7 4.7 12.3 4.7 17 .1l172.6-171.2c4.7-4.7 4.7-12.3 .1-17z"/></svg>',
				esc_attr_x( 'Check', 'Dashboard Panel Icon', 'commons-in-a-box' )
			),
		],
		'comments'         => [
			'name' => _x( 'Comment Bubbles', 'Dashboard Panel Icon', 'commons-in-a-box' ),
			'svg'  => sprintf(
				'<svg class="dashboard-panel-icon-comments" aria-label="%s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M88.2 309.1c9.8-18.3 6.8-40.8-7.5-55.8C59.4 230.9 48 204 48 176c0-63.5 63.8-128 160-128s160 64.5 160 128s-63.8 128-160 128c-13.1 0-25.8-1.3-37.8-3.6c-10.4-2-21.2-.6-30.7 4.2c-4.1 2.1-8.3 4.1-12.6 6c-16 7.2-32.9 13.5-49.9 18c2.8-4.6 5.4-9.1 7.9-13.6c1.1-1.9 2.2-3.9 3.2-5.9zM208 352c114.9 0 208-78.8 208-176S322.9 0 208 0S0 78.8 0 176c0 41.8 17.2 80.1 45.9 110.3c-.9 1.7-1.9 3.5-2.8 5.1c-10.3 18.4-22.3 36.5-36.6 52.1c-6.6 7-8.3 17.2-4.6 25.9C5.8 378.3 14.4 384 24 384c43 0 86.5-13.3 122.7-29.7c4.8-2.2 9.6-4.5 14.2-6.8c15.1 3 30.9 4.5 47.1 4.5zM432 480c16.2 0 31.9-1.6 47.1-4.5c4.6 2.3 9.4 4.6 14.2 6.8C529.5 498.7 573 512 616 512c9.6 0 18.2-5.7 22-14.5c3.8-8.8 2-19-4.6-25.9c-14.2-15.6-26.2-33.7-36.6-52.1c-.9-1.7-1.9-3.4-2.8-5.1C622.8 384.1 640 345.8 640 304c0-94.4-87.9-171.5-198.2-175.8c4.1 15.2 6.2 31.2 6.2 47.8l0 .6c87.2 6.7 144 67.5 144 127.4c0 28-11.4 54.9-32.7 77.2c-14.3 15-17.3 37.6-7.5 55.8c1.1 2 2.2 4 3.2 5.9c2.5 4.5 5.2 9 7.9 13.6c-17-4.5-33.9-10.7-49.9-18c-4.3-1.9-8.5-3.9-12.6-6c-9.5-4.8-20.3-6.2-30.7-4.2c-12.1 2.4-24.8 3.6-37.8 3.6c-61.7 0-110-26.5-136.8-62.3c-16 5.4-32.8 9.4-50 11.8C279 439.8 350 480 432 480z"/></svg>',
				esc_attr_x( 'Comment Bubbles', 'Dashboard Panel Icon', 'commons-in-a-box' )
			),
		],
		'info'             => [
			'name' => _x( 'Info', 'Dashboard Panel Icon', 'commons-in-a-box' ),
			'svg'  => sprintf(
				'<svg class="dashboard-panel-icon-info" aria-label="%s" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>',
				esc_attr_x( 'Info', 'Dashboard Panel Icon', 'commons-in-a-box' )
			),
		],
		'plus'             => [
			'name' => _x( 'Plus', 'Dashboard Panel Icon', 'commons-in-a-box' ),
			'svg'  => sprintf(
				'<svg class="dashboard-panel-icon-plus" aria-label="%s" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><path d="M440-280h80v-160h160v-80H520v-160h-80v160H280v80h160v160Zm40 200q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/></svg>',
				esc_attr_x( 'Plus', 'Dashboard Panel Icon', 'commons-in-a-box' )
			),
		],
		'question-circle'  => [
			'name' => _x( 'Question Mark', 'Dashboard Panel Icon', 'commons-in-a-box' ),
			'svg'  => sprintf(
				'<svg class="dashboard-panel-icon-question-circle" aria-label="%s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 8C119 8 8 119.1 8 256c0 137 111 248 248 248s248-111 248-248C504 119.1 393 8 256 8zm0 448c-110.5 0-200-89.4-200-200 0-110.5 89.5-200 200-200 110.5 0 200 89.5 200 200 0 110.5-89.4 200-200 200zm107.2-255.2c0 67.1-72.4 68.1-72.4 92.9V300c0 6.6-5.4 12-12 12h-45.6c-6.6 0-12-5.4-12-12v-8.7c0-35.7 27.1-50 47.6-61.5 17.6-9.8 28.3-16.5 28.3-29.6 0-17.2-22-28.7-39.8-28.7-23.2 0-33.9 11-48.9 30-4.1 5.1-11.5 6.1-16.7 2.1l-27.8-21.1c-5.1-3.9-6.3-11.1-2.6-16.4C184.8 131.5 214.9 112 261.8 112c49.1 0 101.5 38.3 101.5 88.8zM298 368c0 23.2-18.8 42-42 42s-42-18.8-42-42 18.8-42 42-42 42 18.8 42 42z"/></svg>',
				esc_attr_x( 'Question Mark', 'Dashboard Panel Icon', 'commons-in-a-box' )
			),
		],
		'universal-access' => [
			'name' => _x( 'Universal Access', 'Dashboard Panel Icon', 'commons-in-a-box' ),
			'svg'  => sprintf(
				'<svg class="dashboard-panel-icon-universal-access" aria-label="%s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 48c115 0 208 93 208 208 0 115-93 208-208 208-115 0-208-93-208-208 0-115 93-208 208-208m0-40C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 56C150 64 64 150 64 256s86 192 192 192 192-86 192-192S362 64 256 64zm0 44c19.9 0 36 16.1 36 36s-16.1 36-36 36-36-16.1-36-36 16.1-36 36-36zm117.7 98c-28.7 6.8-55.5 12.7-82.1 15.8 .9 101 12.3 123.1 25 155.6 3.6 9.3-1 19.7-10.2 23.3-9.3 3.6-19.7-1-23.3-10.2-8.7-22.3-17.1-40.6-22.3-78.5h-9.7c-5.2 37.9-13.5 56.2-22.3 78.5-3.6 9.3-14.1 13.8-23.3 10.2-9.3-3.6-13.8-14.1-10.2-23.3 12.7-32.5 24.2-54.5 25-155.6-26.6-3.1-53.4-9-82.1-15.8-8.6-2-13.9-10.6-11.9-19.2s10.6-13.9 19.2-11.9c96.7 22.8 124.3 22.8 220.8 0 8.6-2 17.2 3.3 19.2 11.9 2 8.6-3.3 17.2-11.9 19.2z"/></svg>',
				esc_attr_x( 'Universal Access', 'Dashboard Panel Icon', 'commons-in-a-box' )
			),
		],
	];
}
