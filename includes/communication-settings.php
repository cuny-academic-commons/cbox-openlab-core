<?php

function cboxol_communication_admin_page_email() {
	$url_base = get_admin_url( cbox_get_main_site_id() );

	$settings = array(
		array(
			'title' => __( 'Email Appearance', 'cbox-openlab-core' ),
			'url' => add_query_arg( array(
				'post_type' => 'bp-email',
				'page' => 'bp-emails-customizer-redirect',
			), $url_base . 'edit.php' ),
			'description' => 'Id omnis perspiciatis porro non. Qui quia facere ipsa a repellat qui occaecati. Recusandae quia mollitia sed quaerat repellat corporis. Quos quo autem delectus sed. Similique sit aut nostrum rerum aut. Exercitationem enim voluptatibus non possimus sint. Et quis quibusdam quaerat explicabo. At voluptas voluptatem autem accusantium ut laborum rerum veniam.',
		),
		array(
			'title' => __( 'Email Templates', 'cbox-openlab-core' ),
			'url' => add_query_arg( array(
				'post_type' => 'bp-email',
			), $url_base . 'edit.php' ),
			'description' => 'Id omnis perspiciatis porro non. Qui quia facere ipsa a repellat qui occaecati. Recusandae quia mollitia sed quaerat repellat corporis. Quos quo autem delectus sed. Similique sit aut nostrum rerum aut. Exercitationem enim voluptatibus non possimus sint. Et quis quibusdam quaerat explicabo. At voluptas voluptatem autem accusantium ut laborum rerum veniam.',
		),
	);

	if ( function_exists( 'ass_admin_menu' ) ) {
		if ( is_plugin_active_for_network( 'buddypress-group-email-subscription/bp-activity-subscription.php' ) ) {
			$ges_base = network_admin_url( 'settings.php' );
		} else {
			$ges_base = $url_base . 'options-general.php';
		}

		$settings[] = array(
			'title' => __( 'Group Email Options', 'cbox-openlab-core' ),
			'url' => add_query_arg( array(
				'page' => 'ass_admin_options',
			), $ges_base ),
			'description' => 'Id omnis perspiciatis porro non. Qui quia facere ipsa a repellat qui occaecati. Recusandae quia mollitia sed quaerat repellat corporis. Quos quo autem delectus sed. Similique sit aut nostrum rerum aut. Exercitationem enim voluptatibus non possimus sint. Et quis quibusdam quaerat explicabo. At voluptas voluptatem autem accusantium ut laborum rerum veniam.',
		);
	}

	?>
	<div class="cboxol-admin-content">
		<p>Id omnis perspiciatis porro non. Qui quia facere ipsa a repellat qui occaecati. Recusandae quia mollitia sed quaerat repellat corporis. Quos quo autem delectus sed. Similique sit aut nostrum rerum aut. Exercitationem enim voluptatibus non possimus sint. Et quis quibusdam quaerat explicabo. At voluptas voluptatem autem accusantium ut laborum rerum veniam.
		</p>

		<?php cboxol_communication_settings_markup( $settings ); ?>
	</div>
	<?php
}

function cboxol_communication_admin_page_invitations() {
	if ( bp_core_do_network_admin() ) {
		$base = network_admin_url( 'settings.php' );
	} else {
		$base = get_admin_url( cbox_get_main_site_id(), 'options-general.php' );
	}

	$settings = array(
		array(
			'title' => __( 'Invite Anyone', 'cbox-openlab-core' ),
			'url' => add_query_arg( array(
				'page' => 'invite-anyone',
			), $base ),
			'description' => 'Id omnis perspiciatis porro non. Qui quia facere ipsa a repellat qui occaecati. Recusandae quia mollitia sed quaerat repellat corporis. Quos quo autem delectus sed. Similique sit aut nostrum rerum aut. Exercitationem enim voluptatibus non possimus sint. Et quis quibusdam quaerat explicabo. At voluptas voluptatem autem accusantium ut laborum rerum veniam.',
		),
	);

	?>
	<div class="cboxol-admin-content">
		<p>Id omnis perspiciatis porro non. Qui quia facere ipsa a repellat qui occaecati. Recusandae quia mollitia sed quaerat repellat corporis. Quos quo autem delectus sed. Similique sit aut nostrum rerum aut. Exercitationem enim voluptatibus non possimus sint. Et quis quibusdam quaerat explicabo. At voluptas voluptatem autem accusantium ut laborum rerum veniam.
		</p>

		<?php cboxol_communication_settings_markup( $settings ); ?>
	</div>
	<?php
}

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
