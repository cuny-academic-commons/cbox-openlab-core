<?php

/**
 * "Profile Fields" admin tab.
 */
function cboxol_profile_fields_admin_page() {
	$profile_fields_url = network_admin_url( 'users.php?page=bp-profile-setup' );

	?>
	<div class="cboxol-admin-content">
		<p><?php esc_html_e( 'Profile fields can be customized to only be available to specific member types. Use the link below to access the Network Admin Dashboard to be able to specify which fields are available for each member type.', 'cbox-openlab-core' ); ?></p>

		<p>
			<a class="button" href="<?php echo esc_url( $profile_fields_url ); ?>"><?php esc_html_e( 'Network Admin > Users > Profile Fields', 'cbox-openlab-core' ); ?></a>
		</p>
	</div>
	<?php
}
