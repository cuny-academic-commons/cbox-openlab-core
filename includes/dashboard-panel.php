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
				'allowDismissal'  => (bool) $settings['allow_dismissal'],
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
				'<svg class="dashboard-panel-icon-check-circle" aria-label="%s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M504 256c0 137-111 248-248 248S8 393 8 256 119 8 256 8s248 111 248 248zM227.3 387.3l184-184c6.2-6.2 6.2-16.4 0-22.6l-22.6-22.6c-6.2-6.2-16.4-6.2-22.6 0L216 308.1l-70.1-70.1c-6.2-6.2-16.4-6.2-22.6 0l-22.6 22.6c-6.2 6.2-6.2 16.4 0 22.6l104 104c6.2 6.2 16.4 6.2 22.6 0z"/></svg>',
				esc_attr_x( 'Check', 'Dashboard Panel Icon', 'commons-in-a-box' )
			),
		],
		'comments'         => [
			'name' => _x( 'Comment Bubbles', 'Dashboard Panel Icon', 'commons-in-a-box' ),
			'svg'  => sprintf(
				'<svg class="dashboard-panel-icon-comments" aria-label="%s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M208 352c114.9 0 208-78.8 208-176S322.9 0 208 0S0 78.8 0 176c0 38.6 14.7 74.3 39.6 103.4c-3.5 9.4-8.7 17.7-14.2 24.7c-4.8 6.2-9.7 11-13.3 14.3c-1.8 1.6-3.3 2.9-4.3 3.7c-.5 .4-.9 .7-1.1 .8l-.2 .2s0 0 0 0s0 0 0 0C1 327.2-1.4 334.4 .8 340.9S9.1 352 16 352c21.8 0 43.8-5.6 62.1-12.5c9.2-3.5 17.8-7.4 25.2-11.4C134.1 343.3 169.8 352 208 352zM448 176c0 112.3-99.1 196.9-216.5 207C255.8 457.4 336.4 512 432 512c38.2 0 73.9-8.7 104.7-23.9c7.5 4 16 7.9 25.2 11.4c18.3 6.9 40.3 12.5 62.1 12.5c6.9 0 13.1-4.5 15.2-11.1c2.1-6.6-.2-13.8-5.8-17.9c0 0 0 0 0 0s0 0 0 0l-.2-.2c-.2-.2-.6-.4-1.1-.8c-1-.8-2.5-2-4.3-3.7c-3.6-3.3-8.5-8.1-13.3-14.3c-5.5-7-10.7-15.4-14.2-24.7c24.9-29 39.6-64.7 39.6-103.4c0-92.8-84.9-168.9-192.6-175.5c.4 5.1 .6 10.3 .6 15.5z"/></svg>',
				esc_attr_x( 'Comment Bubbles', 'Dashboard Panel Icon', 'commons-in-a-box' )
			),
		],
		'info'             => [
			'name' => _x( 'Info', 'Dashboard Panel Icon', 'commons-in-a-box' ),
			'svg'  => sprintf(
				'<svg class="dashboard-panel-icon-info" aria-label="%s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 8C119 8 8 119.1 8 256c0 137 111 248 248 248s248-111 248-248C504 119.1 393 8 256 8zm0 110c23.2 0 42 18.8 42 42s-18.8 42-42 42-42-18.8-42-42 18.8-42 42-42zm56 254c0 6.6-5.4 12-12 12h-88c-6.6 0-12-5.4-12-12v-24c0-6.6 5.4-12 12-12h12v-64h-12c-6.6 0-12-5.4-12-12v-24c0-6.6 5.4-12 12-12h64c6.6 0 12 5.4 12 12v100h12c6.6 0 12 5.4 12 12v24z"/></svg>',
				esc_attr_x( 'Info', 'Dashboard Panel Icon', 'commons-in-a-box' )
			),
		],
		'plus'             => [
			'name' => _x( 'Plus', 'Dashboard Panel Icon', 'commons-in-a-box' ),
			'svg'  => sprintf(
				'<svg class="dashboard-panel-icon-plus" aria-label="%s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM232 344l0-64-64 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l64 0 0-64c0-13.3 10.7-24 24-24s24 10.7 24 24l0 64 64 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-64 0 0 64c0 13.3-10.7 24-24 24s-24-10.7-24-24z"/></svg>',
				esc_attr_x( 'Plus', 'Dashboard Panel Icon', 'commons-in-a-box' )
			),
		],
		'question-circle'  => [
			'name' => _x( 'Question Mark', 'Dashboard Panel Icon', 'commons-in-a-box' ),
			'svg'  => sprintf(
				'<svg class="dashboard-panel-icon-question-circle" aria-label="%s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M504 256c0 137-111 248-248 248S8 393 8 256C8 119.1 119 8 256 8s248 111.1 248 248zM262.7 90c-54.5 0-89.3 23-116.5 63.8-3.5 5.3-2.4 12.4 2.7 16.3l34.7 26.3c5.2 3.9 12.6 3 16.7-2.1 17.9-22.7 30.1-35.8 57.3-35.8 20.4 0 45.7 13.1 45.7 33 0 15-12.4 22.7-32.5 34C247.1 238.5 216 254.9 216 296v4c0 6.6 5.4 12 12 12h56c6.6 0 12-5.4 12-12v-1.3c0-28.5 83.2-29.6 83.2-106.7 0-58-60.2-102-116.5-102zM256 338c-25.4 0-46 20.6-46 46 0 25.4 20.6 46 46 46s46-20.6 46-46c0-25.4-20.6-46-46-46z"/></svg>',
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
