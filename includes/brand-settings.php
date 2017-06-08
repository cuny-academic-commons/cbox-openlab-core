<?php

function cboxol_brand_admin_page() {
	$customize_url = add_query_arg( 'return', urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 'customize.php' );

	?>
	<div class="cboxol-admin-content">
		<h3 class="cboxol-admin-content-header"><?php esc_html_e( 'Visual', 'cbox-openlab-core' ); ?></h3>
		<div class="cboxol-admin-content-copy">
			<p>Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut </p>

			<p>
				<a class="button" href="<?php echo esc_url( $customize_url ); ?>"><?php esc_html_e( 'Customize', 'cbox-openlab-core' ); ?></a>
			</p>
		</div>
	</div>

	<div class="cboxol-admin-content has-top-border">
		<h3 class="cboxol-admin-content-header"><?php esc_html_e( 'Copy', 'cbox-openlab-core' ); ?></h3>
		<div class="cboxol-admin-content-copy">
			<p>Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut </p>
		</div>
	<?php
}
