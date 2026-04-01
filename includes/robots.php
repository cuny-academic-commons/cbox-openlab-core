<?php

/**
 * Tools related to robots.txt.
 *
 * @package cbox-openlab-core
 */

namespace CBOX\OL\Robots;

/**
 * Adds AI-specific directives to robots.txt.
 *
 * @since 1.8.0
 */
function add_ai_robots_directives( $data ) {
	if ( ! is_block_ai_robots_enabled() ) {
		return $data;
	}

	$agents_data = require CBOXOL_PLUGIN_DIR . 'build/knownagents-user-agents.php';

	foreach ( $agents_data['user_agents'] as $agent ) {
		$data .= "\n";
		$data .= "User-agent: {$agent}\n";
		$data .= "Disallow: /\n";
	}

	return $data;
}
add_filter( 'robots_txt', __NAMESPACE__ . '\\add_ai_robots_directives' );

/**
 * Is the option to block AI crawlers enabled for a given site?
 *
 * @since 1.8.0
 *
 * @param int|null $site_id Optional site ID to check. Defaults to current site.
 * @return bool True if the option is enabled, false otherwise.
 */
function is_block_ai_robots_enabled( $site_id = null ) {
	$site_id = $site_id ? (int) $site_id : get_current_blog_id();
	return (bool) get_blog_option( $site_id, 'cboxol_block_ai_robots', false );
}

/**
 * Registers setting for blocking AI crawlers.
 *
 * @since 1.8.0
 *
 * @return void
 */
function register_ai_robots_setting() {
	register_setting(
		'reading',
		'cboxol_block_ai_robots',
		[
			'type'              => 'boolean',
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize_ai_robots_setting',
			'default'           => false,
		]
	);
}
add_action( 'admin_init', __NAMESPACE__ . '\\register_ai_robots_setting' );

/**
 * Sanitizes AI crawler checkbox setting.
 *
 * @since 1.8.0
 *
 * @param mixed $value Raw submitted value.
 * @return int
 */
function sanitize_ai_robots_setting( $value ) {
	return empty( $value ) ? 0 : 1;
}

/**
 * Adds checkbox to Reading settings.
 *
 * @since 1.8.0
 *
 * @return void
 */
function add_ai_robots_checkbox() {
	$checked = is_block_ai_robots_enabled();
	ai_robots_checkbox_markup( $checked );

	?>
	<style>
		.block-ai-crawlers-wrapper {
			margin-top: 1em;
		}

		.block-ai-crawlers-wrapper label {
			font-weight: bold;
		}
	</style>
	<?php
}
add_action( 'blog_privacy_selector', __NAMESPACE__ . '\\add_ai_robots_checkbox', 50 );

/**
 * Markup for checkbox to block AI crawlers.
 *
 * @since 1.8.0
 *
 * @param bool $checked Whether the checkbox should be checked.
 * @return void
 */
function ai_robots_checkbox_markup( $checked ) {
	?>

	<div class="block-ai-crawlers-wrapper">
		<input type="hidden" name="cboxol_block_ai_robots" value="0" />
		<label for="block-ai-crawlers">
			<input type="checkbox" name="cboxol_block_ai_robots" id="block-ai-crawlers" value="1" <?php checked( $checked, true ); ?> />
			<?php esc_html_e( 'Ask AI crawlers not to access this site.', 'cbox-openlab-core' ); ?>
		</label>

		<p class="description group-settings-note italics note">
			<?php esc_html_e( 'Note: This option will NOT block access to the site. It is up to AI crawlers to honor your request.', 'cbox-openlab-core' ); ?>
		</p>
	</div>

	<?php
}
