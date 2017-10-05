<?php

/**
 * "Profile Fields" admin tab.
 */
function cboxol_profile_fields_admin_page() {
	$profile_fields_url = network_admin_url( 'users.php?page=bp-profile-setup' );

	?>
	<div class="cboxol-admin-content">
		<p>Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut </p>

		<p>
			<a class="button" href="<?php echo esc_url( $profile_fields_url ); ?>"><?php esc_html_e( 'Network Admin > Users > Profile Fields', 'cbox-openlab-core' ); ?></a>
		</p>
	</div>
	<?php
}
