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

	$mtypes = cboxol_get_member_types();
	$member_types = array();
	foreach ( $mtypes as $mtype ) {
		$slug = $mtype->get_slug();
		$member_types[] = array(
			'value' => $slug,
			'label' => $mtype->get_label( 'singular' ),
		);
	}

	$app_config = array(
		'subapp' => 'Registration',
		'emailDomains' => $domains,
		'memberTypes' => $member_types,
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

/**
 * Filters the error object return by BP's signup validation process.
 *
 * Here we check for signups that have failed because of wildcard validation errors,
 * and we force them to pass.
 */
function cboxol_signup_email_filter( $result ) {
	$valid_email_domain_check = cboxol_wildcard_email_domain_check( $result['user_email'] );

	if ( $valid_email_domain_check ) {
		// Rebuild the error object.
		$error_codes = $result['errors']->get_error_codes();
		$new_error = new WP_Error();
		foreach ( $error_codes as $error_code ) {
			$error_messages = $result['errors']->get_error_messages( $error_code );
			foreach ( $error_messages as $error_message ) {
				if ( 'Sorry, that email address is not allowed!' === $error_message ) {
					continue;
				}

				$data = $result['errors']->get_error_data( $error_code );
				$new_error->add( $error_code, $error_message, $data );
			}
		}

		$result['errors'] = $new_error;
	}

	return $result;
}
add_filter( 'bp_core_validate_user_signup', 'cboxol_signup_email_filter', 8 );

/**
 * Checks an email address against the email domain whitelist. Wildcard-friendly.
 *
 * @param string $user_email
 * @return bool
 */
function cboxol_wildcard_email_domain_check( $user_email ) {
	$valid_email_domain_check = false;

	$limited_email_domains = get_site_option( 'limited_email_domains' );

	if ( is_array( $limited_email_domains ) && ! empty( $limited_email_domains ) ) {
		$emaildomain = strtolower( substr( $user_email, 1 + strpos( $user_email, '@' ) ) );
		foreach ( $limited_email_domains as $limited_email_domain ) {
			if ( $valid_email_domain_check ) {
				break;
			}

			if ( false !== strpos( $limited_email_domain, '*' ) ) {
				$limited_email_domain = str_replace( '.', '\.', $limited_email_domain );        // Escape your .s
				$limited_email_domain = str_replace( '*', '[-_\.a-zA-Z0-9]+', $limited_email_domain );     // replace * with REGEX for 1+ occurrence of anything
				$limited_email_domain = '/^' . $limited_email_domain . '/';   // bracket the email with the necessary pattern markings
				$valid_email_domain_check = ( $valid_email_domain_check or preg_match( $limited_email_domain, $emaildomain ) );
			} else {
				$valid_email_domain_check = $limited_email_domain == $emaildomain;
			}
		}
	}

	return $valid_email_domain_check;
}
