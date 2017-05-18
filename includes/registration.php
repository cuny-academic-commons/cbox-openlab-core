<?php

add_filter( 'sanitize_option_limited_email_domains', 'cboxol_registration_sanitize_limited_email_domains', 10, 3 );

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

	ksort( $domains );

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

/**
 * Allow wildcard email email domains in the whitelist.
 *
 * @param array  $value
 * @param string $option
 * @param array  $original_value
 * @return array
 */
function cboxol_registration_sanitize_limited_email_domains( $value, $option, $original_value ) {
	if ( $value === $original_value ) {
		return $value;
	}

	if ( ! is_array( $original_value ) ) {
		$original_value = explode( "\n", $original_value );
	}

	$rejected = array_diff( $original_value, $value );

	$has_wildcard = array();
	foreach ( $rejected as $domain ) {
		if ( ! preg_match( '/(--|\.\.)/', $domain ) && preg_match( '|^([a-zA-Z0-9-\.\*])+$|', $domain ) ) {
			$has_wildcard[] = $domain;
		}
	}

	if ( $has_wildcard ) {
		$value = array_merge( $value, $has_wildcard );
	}

	// Sort (this makes the order consistent in Network Admin).
	sort( $value );

	return $value;
}
