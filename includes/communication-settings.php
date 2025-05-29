<?php

function cboxol_communication_admin_page_email() {
	$url_base = get_admin_url( cboxol_get_main_site_id() );

	$settings = array(
		array(
			'title'       => __( 'Email Appearance', 'commons-in-a-box' ),
			'url'         => add_query_arg(
				array(
					'post_type' => 'bp-email',
					'page'      => 'bp-emails-customizer-redirect',
				),
				$url_base . 'edit.php'
			),
			'description' => __( 'Customize the visual design of emails that will be sent to community members.', 'commons-in-a-box' ),
		),
		array(
			'title'       => __( 'Email Templates', 'commons-in-a-box' ),
			'url'         => add_query_arg(
				array(
					'post_type' => 'bp-email',
				),
				$url_base . 'edit.php'
			),
			'description' => __( 'Manage the content that’s included in emails sent automatically to members based on triggers (e.g. a welcome email sent to new members of a group).', 'commons-in-a-box' ),
		),
	);

	if ( function_exists( 'ass_admin_menu' ) ) {
		if ( is_plugin_active_for_network( 'buddypress-group-email-subscription/bp-activity-subscription.php' ) ) {
			$ges_base = network_admin_url( 'settings.php' );
		} else {
			$ges_base = $url_base . 'options-general.php';
		}

		$settings[] = array(
			'title'       => __( 'Group Email Options', 'commons-in-a-box' ),
			'url'         => add_query_arg(
				array(
					'page' => 'ass_admin_options',
				),
				$ges_base
			),
			'description' => __( 'Update settings related to Daily Digests & Weekly Summaries of group activities in your community; toggle global unsubscribe links; modify group admin abilities related to email subscription settings; and establish spam prevention guidelines.', 'commons-in-a-box' ),
		);
	}

	?>
	<div class="cboxol-admin-content">
		<p><?php esc_html_e( 'Members of your community can be sent various notifications via email. Manage all settings related to emails here.', 'commons-in-a-box' ); ?></p>

		<?php cboxol_communication_settings_markup( $settings ); ?>
	</div>
	<?php
}

function cboxol_communication_admin_page_invitations() {
	if ( bp_core_do_network_admin() ) {
		$base = network_admin_url( 'settings.php' );
	} else {
		$base = get_admin_url( cboxol_get_main_site_id(), 'options-general.php' );
	}

	$settings = array(
		array(
			'title'       => __( 'Invite Anyone', 'commons-in-a-box' ),
			'url'         => add_query_arg(
				array(
					'page' => 'invite-anyone',
				),
				$base
			),
			'description' => __( 'Manage the invite email content template, control which member types are able to send various kinds of invitations, control address book integration, view sent invitations and related statistics.', 'commons-in-a-box' ),
		),
	);

	?>
	<div class="cboxol-admin-content">
		<p><?php esc_html_e( 'Invite Anyone allows community members to invite non-members to your community and its groups via email.', 'commons-in-a-box' ); ?></p>

		<?php cboxol_communication_settings_markup( $settings ); ?>
	</div>
	<?php
}

/**
 * Member Communications settings panel under Communication Settings.
 *
 * @since 1.7.0
 */
function cboxol_communication_admin_page_member_communications() {
	wp_enqueue_script(
		'cboxol-dashboard-panel-settings',
		CBOXOL_PLUGIN_URL . 'assets/js/dashboard-panel-settings.js',
		[],
		CBOXOL_PLUGIN_VER,
		true
	);

	$dashboard_panel_settings = \CBOX\OL\DashboardPanel\get_dashboard_panel_settings();

	$panels = [
		'panel_1' => __( 'Left panel', 'commons-in-a-box' ),
		'panel_2' => __( 'Middle panel', 'commons-in-a-box' ),
		'panel_3' => __( 'Right panel', 'commons-in-a-box' ),
	];

	?>
	<div class="cboxol-admin-content">
		<div>
			<h3><?php esc_html_e( 'Main Site Banner', 'commons-in-a-box' ); ?></h3>

			<p>
				<?php
					printf(
						// translators: %s is a link to the Customizer section for the Main Site Banner.
						esc_html__( 'You can edit the Main Site Banner in the Customizer: %s', 'commons-in-a-box' ),
						sprintf(
							'<a href="%s" class="cboxol-customize-link">%s</a>',
							esc_url( admin_url( 'customize.php?autofocus[section]=openlab_section_sitewide_notice' ) ),
							esc_html__( 'Customize Main Site Banner', 'commons-in-a-box' )
						)
					);
				?>
			</p>
		</div>

		<br />

		<div class="cboxol-dashboard-panels-config">
			<h3><?php esc_html_e( 'Dashboard Panel', 'commons-in-a-box' ); ?></h3>

			<p><?php esc_html_e( 'Below you can customize a panel that will appear on the Dashboard of all group sites. For reference, below is an example of what the Dashboard Panel looks like.', 'commons-in-a-box' ); ?></p>

			<img src="<?php echo esc_url( CBOXOL_PLUGIN_URL . 'assets/img/dashboard-panel.png' ); ?>" alt="<?php esc_attr_e( 'Dashboard Panel', 'commons-in-a-box' ); ?>" class="cboxol-dashboard-panel-example" />

			<form class="dashboard-panel-settings-form" id="dashboard-panel-settings-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<p>
					<label for="cboxol-dashboard-panel-toggle">
						<input type="checkbox" id="cboxol-dashboard-panel-toggle" name="enabled" value="1" <?php checked( $dashboard_panel_settings['enabled'] ); ?> />
						<?php esc_html_e( 'Enable Dashboard panel', 'commons-in-a-box' ); ?>
					</label>
				</p>

				<p>
					<label for="cboxol-dashboard-panel-allow-dismissal">
						<input type="checkbox" class="disabled-when-disabled" id="cboxol-dashboard-panel-allow-dismissal" name="allow-dismissal" value="1" <?php checked( $dashboard_panel_settings['allow_dismissal'] ); ?> />
						<?php esc_html_e( 'Allow members to dismiss this notice ', 'commons-in-a-box' ); ?>
					</label>
				</p>

				<p>
					<?php esc_html_e( 'Please choose the heading text and tagline that will appear in the top banner portion.', 'commons-in-a-box' ); ?>

					<table class="form-table">
						<tr>
							<th>
								<label for="primary-heading">
									<?php esc_html_e( 'Heading', 'commons-in-a-box' ); ?>
								</label>
							</th>

							<td>
								<input type="text" id="primary-heading" name="primary-heading" value="<?php echo esc_attr( $dashboard_panel_settings['heading'] ); ?>" class="disabled-when-disabled regular-text form-control" />
							</td>
						</tr>

						<tr>
							<th>
								<label for="tagline">
									<?php esc_html_e( 'Tagline', 'commons-in-a-box' ); ?>
								</label>
							</th>

							<td>
								<div class="editor-wrap">
									<?php
									wp_editor(
										$dashboard_panel_settings['tagline'],
										'tagline',
										array(
											'editor_class'  => 'disabled-when-disabled',
											'media_buttons' => false,
											'textarea_rows' => 3,
											'teeny'         => true,
											'quicktags'     => array(
												'buttons' => 'strong,em,link',
											),
										)
									);
									?>
								</div>
							</td>

						</tr>
					</table>
				</p>

				<p>
					<?php esc_html_e( 'Please choose the text and icon for each of the three panels that appear below the banner.', 'commons-in-a-box' ); ?>
				</p>

				<?php foreach ( $panels as $panel_id => $panel_name ) : ?>
					<?php
					$panel_heading = $dashboard_panel_settings[ $panel_id . '_heading' ];
					$panel_text    = $dashboard_panel_settings[ $panel_id . '_text' ];
					?>

					<div class="cboxol-dashboard-panel-single-panel-settings">
						<table class="form-table">
							<tr>
								<th colspan="2">
									<h4><?php echo esc_html( $panel_name ); ?></h4>
								</th>
							</tr>

							<tr>
								<th>
									<label for="<?php echo esc_attr( $panel_id ); ?>-heading">
										<?php esc_html_e( 'Heading text', 'commons-in-a-box' ); ?>
									</label>
								</th>

								<td>
									<input type="text" id="<?php echo esc_attr( $panel_id ); ?>-heading" name="<?php echo esc_attr( $panel_id ); ?>-heading" value="<?php echo esc_attr( $panel_heading ); ?>" class="disabled-when-disabled regular-text form-control" />
								</td>
							</tr>

							<tr>
								<th>
									<label for="<?php echo esc_attr( $panel_id ); ?>-text">
										<?php esc_html_e( 'Body Text (the suggested limit is 300 characters)', 'commons-in-a-box' ); ?>
									</label>
								</th>

								<td>
									<div class="editor-wrap">
										<?php
										wp_editor(
											$panel_text,
											$panel_id . '-text',
											array(
												'editor_class'  => 'disabled-when-disabled',
												'media_buttons' => false,
												'textarea_rows' => 3,
												'teeny'         => true,
												'quicktags'     => array(
													'buttons' => 'strong,em,link',
												),
											)
										);
										?>
									</div>
								</td>

							</tr>
						</table>
					</div>
				<?php endforeach; ?>

				<p>
					<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'commons-in-a-box' ); ?>" />
				</p>

				<?php wp_nonce_field( 'cboxol_dashboard_panel' ); ?>
				<input type="hidden" name="action" value="cboxol_dashboard_panel" />
			</form>
		</div>
	</div>

	<?php
}

/**
 * Save callback for the Dashboard Panel settings.
 *
 * @return void
 */
function cboxol_save_dashboard_panel_settings() {
	if ( ! isset( $_POST['action'] ) || 'cboxol_dashboard_panel' !== $_POST['action'] ) {
		return;
	}

	if ( ! check_admin_referer( 'cboxol_dashboard_panel' ) ) {
		return;
	}

	$settings = array(
		'enabled'         => isset( $_POST['enabled'] ),
		'allow_dismissal' => isset( $_POST['allow-dismissal'] ),
		'heading'         => sanitize_text_field( wp_unslash( $_POST['primary-heading'] ) ),
		'tagline'         => wp_kses_post( wp_unslash( $_POST['tagline'] ) ),
	);

	foreach ( [ 'panel_1', 'panel_2', 'panel_3' ] as $panel_id ) {
		$settings[ $panel_id . '_heading' ] = sanitize_text_field( wp_unslash( $_POST[ $panel_id . '-heading' ] ) );
		$settings[ $panel_id . '_text' ]    = wp_kses_post( wp_unslash( $_POST[ $panel_id . '-text' ] ) );
	}

	update_site_option( 'cboxol_dashboard_panel_settings', $settings );

	wp_redirect(
		add_query_arg(
			array(
				'settings-updated' => 1,
			),
			admin_url( 'admin.php?page=cbox-ol-communication-settings&cboxol-section=member-communications' )
		)
	);
	exit;
}
add_action( 'admin_post_cboxol_dashboard_panel', 'cboxol_save_dashboard_panel_settings' );

/**
 * Badges settings panel under Communication Settings.
 *
 * @since 1.2.0
 */
function cboxol_badges_admin_page() {
	$url = add_query_arg(
		'page',
		'openlab-badges',
		network_admin_url( 'admin.php' )
	);

	$settings = array(
		array(
			'title'       => __( 'OpenLab Badges', 'commons-in-a-box' ),
			'url'         => $url,
			'description' => __( 'Create and manage group badges on the Badges admin page.', 'commons-in-a-box' ),
		),
	);

	?>
	<div class="cboxol-admin-content">
		<p><?php esc_html_e( 'OpenLab Badges allows the network administrator to create custom badges that can be awarded to groups. These badges are displayed in group directories and on group home pages, and can be used to find and filter groups in the directories.', 'commons-in-a-box' ); ?></p>

		<?php cboxol_communication_settings_markup( $settings ); ?>
	</div>
	<?php
}

/**
 * Helper function for building markup on Communication Settings panels.
 *
 * @since 1.1.0
 *
 * @param array $settings {
 *   Values for the various parts of the panel interface.
 *
 *   @type string $title       Panel title.
 *   @type string $url         URL for the link.
 *   @type string $description Description text.
 * }
 */
function cboxol_communication_settings_markup( $settings ) {
	?>
		<ul class="cboxol-communication-settings">
			<?php foreach ( $settings as $setting ) : ?>
			<li>
				<div class="setting-link"><a href="<?php echo esc_url( $setting['url'] ); ?>"><?php echo esc_html( $setting['title'] ); ?></a></div>
				<div class="setting-description">
					<p class="description"><?php echo esc_html( $setting['description'] ); ?></p>
				</div>
			</li>
			<?php endforeach; ?>
		</ul>
	<?php
}
