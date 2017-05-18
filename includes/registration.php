<?php

function cboxol_registration_admin_page() {
	wp_enqueue_script( 'cbox-ol-app' );

	$email_domains = get_site_option( 'limited_email_domains' );
	if ( ! is_array( $email_domains ) ) {
		$email_domains = array();
	}

	$domains = array();
	foreach ( $email_domains as $email_domain ) {
		$domains[ $email_domain ] = $email_domain;
	}

	$app_config = array(
		'subapp' => 'Registration',
		'emailDomains' => $domains,
	);

	?>

	<p>
		Subheader text
	</p>

	<script type="text/javascript">
		var CBOXOL_AppConfig = <?php echo json_encode( $app_config ); ?>;
	</script>

	<div id="cboxol-admin"></div>

	<?php
}
