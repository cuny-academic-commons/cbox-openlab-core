<?php

add_action( 'init', 'cboxol_registration_register_post_type' );
add_filter( 'sanitize_option_limited_email_domains', 'cboxol_registration_sanitize_limited_email_domains', 10, 3 );

add_action( 'wp_ajax_nopriv_openlab_validate_email', 'cboxol_registration_validate_email' );
add_action( 'wp_ajax_nopriv_cboxol_validate_signup_code', 'cboxol_registration_validate_signup_code' );
add_action( 'bp_core_validate_user_signup', 'cboxol_validate_signup_member_type' );
add_action( 'bp_signup_usermeta', 'cboxol_save_signup_member_type' );
add_action( 'bp_core_activated_user', 'cboxol_save_activated_user_member_type', 10, 3 );

/**
 * Register post types related to registration.
 */
function cboxol_registration_register_post_type() {
	register_post_type(
		'cboxol_signup_code',
		array(
			'labels'             => array(
				'name' => _x( 'Signup Codes', 'Post type general name', 'commons-in-a-box' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
		)
	);
}

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

	$mtypes       = cboxol_get_member_types();
	$member_types = array();
	foreach ( $mtypes as $mtype ) {
		$slug                  = $mtype->get_slug();
		$member_types[ $slug ] = array(
			'value' => $slug,
			'label' => $mtype->get_label( 'singular' ),
		);
	}

	$signup_codes     = cboxol_get_signup_codes();
	$signup_code_data = array();
	foreach ( $signup_codes as $signup_code ) {
		$signup_code_data[ $signup_code->get_wp_post_id() ] = $signup_code->get_for_endpoint();
	}

	$dummy               = new \CBOX\OL\SignupCode();
	$signup_code_data[0] = $dummy->get_for_endpoint();

	$registration_form_settings = cboxol_get_registration_form_settings();

	$app_config = array(
		'subapp'                   => 'Registration',
		'emailDomains'             => $domains,
		'memberTypes'              => $member_types,
		'signupCodes'              => $signup_code_data,
		'registrationFormSettings' => $registration_form_settings,
	);

	?>

	<div class="cboxol-admin-content">
		<p><?php esc_html_e( 'Registration management allows you to control who can create accounts and what types of accounts different types of users can create.', 'commons-in-a-box' ); ?></p>

		<script type="text/javascript">
			var CBOXOL_AppConfig = <?php echo wp_json_encode( $app_config ); ?>;
		</script>

		<div id="cboxol-admin"></div>
	</div>

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
		$new_error   = new WP_Error();
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
				$limited_email_domain     = str_replace( '.', '\.', $limited_email_domain );        // Escape your .s
				$limited_email_domain     = str_replace( '*', '[-_\.a-zA-Z0-9]+', $limited_email_domain );     // replace * with REGEX for 1+ occurrence of anything
				$limited_email_domain     = '/^' . $limited_email_domain . '/';   // bracket the email with the necessary pattern markings
				$valid_email_domain_check = ( $valid_email_domain_check || preg_match( $limited_email_domain, $emaildomain ) );
			} else {
				$valid_email_domain_check = $limited_email_domain === $emaildomain;
			}
		}
	}

	return $valid_email_domain_check;
}

/**
 * Get Signup Code by the code.
 *
 * This assumes uniqueness.
 *
 * @param string $code
 * @return \WP_Error|\CBOX\OL\SignupCode
 */
function cboxol_get_signup_code( $code ) {
	if ( $code ) {
		$signup_codes = cboxol_get_signup_codes();

		foreach ( $signup_codes as $signup_code ) {
			if ( $signup_code->get_code() === $code ) {
				return $signup_code;
			}
		}
	}

	return new WP_Error( 'no_signup_code_found', __( 'No signup code found.', 'commons-in-a-box' ), $code );
}

/**
 * Get registered Signup Codes.
 */
function cboxol_get_signup_codes( $args = array() ) {
	$r = array_merge( array(), $args );

	$post_args = array(
		'post_type'      => 'cboxol_signup_code',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
		'fields'         => 'ids',
	);

	$last_changed = wp_cache_get_last_changed( 'posts' );
	$cache_key    = 'cboxol_signup_codes_' . md5( wp_json_encode( $post_args ) ) . '_' . $last_changed;
	$ids          = wp_cache_get( $cache_key, 'cboxol_signup_codes' );
	if ( false === $ids ) {
		$ids = get_posts( $post_args );
		_prime_post_caches( $ids );
		wp_cache_set( $cache_key, $ids, 'cboxol_signup_codes' );
	}

	$code_posts = array_map( 'get_post', $ids );

	$codes = array();
	foreach ( $code_posts as $code_post ) {
		$codes[ $code_post->ID ] = \CBOX\OL\SignupCode::get_instance_from_wp_post( $code_post );
	}

	return $codes;
}

/* Register page *************************************************************/

/**
 * Output registration errors into a JS variable.
 *
 * These error values can then be used to create dynamic error messages for objects inserted
 * into the DOM, as is the case with account-type-specific profile fields.
 */
function openlab_registration_errors_object() {
	if ( ! bp_is_register_page() ) {
		return;
	}

	/*
	 * Instead of doing a database query to pull up every registration field ID (and thus
	 * dynamically build hook names), do the quicker and more terrible loop through
	 * existing hooks.
	 */
	global $wp_filter;
	$errors = array();
	foreach ( $wp_filter as $filter_name => $callbacks ) {
		// Faster than regex.
		if ( 0 !== strpos( $filter_name, 'bp_' ) ) {
			continue;
		}

		if ( '_errors' !== substr( $filter_name, -7 ) ) {
			continue;
		}

		ob_start();
		do_action( $filter_name );
		$error = ob_get_clean();

		if ( ! empty( $error ) ) {
			preg_match( '/bp_(field_[0-9]+)_errors/', $filter_name, $matches );
			$field_name            = $matches[1];
			$errors[ $field_name ] = $error;
		}
	}

	$error_json = wp_json_encode( $errors );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<script type="text/javascript">var OpenLab_Registration_Errors = ' . $error_json . '</script>';
}
add_action( 'wp_head', 'openlab_registration_errors_object' );

/**
 * AJAX callback for email validation.
 */
function cboxol_registration_validate_email() {
	$retval = array(
		'message' => '',
	);

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! isset( $_POST['email'] ) ) {
		$retval['message'] = __( 'No email provided.', 'commons-in-a-box' );
		wp_send_json_error( $retval );
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$email = wp_unslash( $_POST['email'] );

	if ( ! is_email( $email ) ) {
		$retval['message'] = __( 'Please enter a valid email address.', 'commons-in-a-box' );
		wp_send_json_error( $retval );
	}

	if ( ! cboxol_wildcard_email_domain_check( $email ) ) {
		$retval['message'] = __( 'Sorry, that email address is not allowed!', 'commons-in-a-box' );
		wp_send_json_error( $retval );
	}

	if ( email_exists( $email ) ) {
		$retval['message'] = __( 'Sorry, that email address is already used!', 'commons-in-a-box' );
		wp_send_json_error( $retval );
	}

	wp_send_json_success();
}

/**
 * Save user's member type at registration.
 *
 * @param int $user_id
 */
function cboxol_validate_signup_member_type( $validate ) {
	$account_type = null;
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( isset( $_POST['account-type'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$account_type = wp_unslash( $_POST['account-type'] );
	}

	if ( ! $account_type ) {
		return $validate;
	}

	$error       = null;
	$member_type = cboxol_get_member_type( $account_type );
	if ( is_wp_error( $member_type ) ) {
		$error = $member_type;
	} else {
		if ( ! $member_type->get_requires_signup_code() ) {
			return $validate;
		}

		$signup_code = '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['account-type-signup-code'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$signup_code = wp_unslash( $_POST['account-type-signup-code'] );
		}

		$signup_code_validate = $member_type->validate_signup_code( $signup_code );
		if ( is_wp_error( $signup_code_validate ) ) {
			$error = $signup_code_validate;
		}
	}

	// Error must be added to the global for BP to stop signups. :(
	if ( $error ) {
		$validate['errors']->add( $error->get_error_code(), $error->get_error_message() );
		buddypress()->signup->errors['account_type'] = $error->get_error_message();
	}

	return $validate;
}

/**
 * Save user's member type at registration.
 *
 * @param array $usermeta
 * @return array
 */
function cboxol_save_signup_member_type( $usermeta ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	$account_type = null;
	if ( isset( $_POST['account-type'] ) ) {
		$account_type = wp_unslash( $_POST['account-type'] );
	}

	$account_type_signup_code = null;
	if ( isset( $_POST['account-type-signup-code'] ) ) {
		$account_type_signup_code = wp_unslash( $_POST['account-type-signup-code'] );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	$usermeta['account_type']             = $account_type;
	$usermeta['account_type_signup_code'] = $account_type_signup_code;

	return $usermeta;
}

/**
 * Apply a user's chosen member type at activation.
 *
 * Also does any group joining associated with the signup code.
 *
 * @param int    $user_id
 * @param string $key
 * @param array  $user
 */
function cboxol_save_activated_user_member_type( $user_id, $key, $user ) {
	$account_type             = null;
	$account_type_signup_code = null;

	if ( isset( $user['meta']['account_type'] ) ) {
		$account_type = $user['meta']['account_type'];
	}

	$member_type = cboxol_get_member_type( $account_type );
	if ( ! is_wp_error( $member_type ) ) {
		$validated = true;

		if ( $member_type->get_requires_signup_code() ) {
			if ( isset( $user['meta']['account_type_signup_code'] ) ) {
				$account_type_signup_code = $user['meta']['account_type_signup_code'];
			}
			$validated = $member_type->validate_signup_code( $account_type_signup_code );
		}

		if ( $validated ) {
			bp_set_member_type( $user_id, $member_type->get_slug() );

			$signup_code = cboxol_get_signup_code( $account_type_signup_code );
			if ( ! is_wp_error( $signup_code ) ) {
				$group_id = $signup_code->get_group_id();
				if ( $group_id ) {
					// Will create activity items, etc.
					groups_join_group( $group_id, $user_id );
				}
			}
		}
	}
}

/**
 * AJAX callback for validating signup code.
 */
function cboxol_registration_validate_signup_code() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( empty( $_POST['member_type'] ) || empty( $_POST['code'] ) ) {
		wp_send_json_error();
	}

	$validated = false;

	$member_type = cboxol_get_member_type( wp_unslash( $_POST['member_type'] ) );
	if ( ! is_wp_error( $member_type ) ) {
		$validated = $member_type->validate_signup_code( wp_unslash( $_POST['code'] ) );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	if ( is_wp_error( $validated ) ) {
		wp_send_json_error();
	} else {
		wp_send_json_success();
	}
}

/**
 * Get registration form settings.
 */
function cboxol_get_registration_form_settings() {
	$registration_form_settings = get_site_option( 'cboxol_registration_form_settings', array() );
	$registration_form_settings = array_merge(
		array(
			'confirmationText' => __( 'Click "Complete Sign Up" to continue.', 'commons-in-a-box' ),
		),
		$registration_form_settings
	);
	return $registration_form_settings;
}
