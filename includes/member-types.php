<?php

/**
 * Member types.
 */

add_action( 'init', 'cboxol_membertypes_register_post_type' );
add_action( 'bp_register_member_types', 'cboxol_membertypes_register_member_types' );

add_action( 'xprofile_updated_profile', 'cboxol_membertypes_process_change' );

function cboxol_membertypes_register_post_type() {
	register_post_type( 'cboxol_member_type', array(
		'labels' => array(
			'name' => _x( 'Member Types', 'Post type general name', 'cbox-openlab-core' ),
			'singular_name' => _x( 'Member Type', 'Post type singular name', 'cbox-openlab-core' ),
			'add_new_item' => __( 'Add New Member Type', 'cbox-openlab-core' ),
			'new_item' => __( 'New Member Type', 'cbox-openlab-core' ),
			'edit_item' => __( 'Edit Member Type', 'cbox-openlab-core' ),
			'view_item' => __( 'View Member Type', 'cbox-openlab-core' ),
			'all_item' => __( 'All Member Types', 'cbox-openlab-core' ),
			'search_items' => __( 'Search Member Types', 'cbox-openlab-core' ),
			'not_found' => __( 'No member types found.', 'cbox-openlab-core' ),
			'not_found_in_trash' => __( 'No member types found in Trash.', 'cbox-openlab-core' ),
		),
		'public' => false,
		'publicly_queryable' => false,
		'show_ui' => true,
		'show_in_menu' => false,
	) );
}

/**
 * Register member types with BuddyPress.
 */
function cboxol_membertypes_register_member_types() {
	$saved_types = cboxol_get_member_types();

	// @todo Conflict checking? Prefixing?
	foreach ( $saved_types as $saved_type ) {
		bp_register_member_type( $saved_type->get_slug(), array(
			'labels' => array(
				'name' => $saved_type->get_label( 'plural' ),
				'singular_name' => $saved_type->get_label( 'singular' ),
			),
			'has_directory' => true,
		) );
	}
}

/**
 * Get a single registered Member Type.
 *
 * @param string $type Slug of the type.
 * @return \CBOX\OL\MemberType|WP_Error
 */
function cboxol_get_member_type( $slug ) {
	if ( $slug ) {
		$types = cboxol_get_member_types( array(
			'enabled' => null,
		) );

		if ( isset( $types[ $slug ] ) ) {
			return $types[ $slug ];
		}
	}

	return new WP_Error( 'no_member_type_found', __( 'No member type exists for this slug.', 'cbox-openlab-core' ), $slug );
}

/**
 * Get registered Member Types.
 *
 * @params array $args {
 *     Array of optional arguments.
 *     @type bool|null $enabled Filter by 'enabled' status. True returns only enabled Types, false returns
 *                              only disabled types. Null returns all.
 * }
 */
function cboxol_get_member_types( $args = array() ) {
	$r = array_merge( array(
		'enabled' => true,
	), $args );

	$post_status = 'publish';
	if ( false === $r['enabled'] ) {
		$post_status = 'draft';
	} elseif ( null === $r['enabled'] ) {
		$post_status = 'any';
	}

	$post_args = array(
		'post_type' => 'cboxol_member_type',
		'post_status' => $post_status,
		'posts_per_page' => -1,
		'orderby' => array(
			'menu_order' => 'ASC',
			'title' => 'ASC',
		),
		'fields' => 'ids',
	);

	$switched = false;
	$main_site_id = cboxol_get_main_site_id();
	if ( get_current_blog_id() !== $main_site_id ) {
		switch_to_blog( $main_site_id );
		$switched = true;
	}

	$last_changed = wp_cache_get_last_changed( 'posts' );
	$cache_key = 'cboxol_types_' . md5( json_encode( $post_args ) ) . '_' . $last_changed;
	$ids = wp_cache_get( $cache_key, 'cboxol_member_types' );
	if ( false === $ids ) {
		$ids = get_posts( $post_args );
		_prime_post_caches( $ids );
		wp_cache_set( $cache_key, $ids, 'cboxol_member_types' );
	}

	$type_posts = array_map( 'get_post', $ids );

	$types = array();
	foreach ( $type_posts as $type_post ) {
		$types[ $type_post->post_name ] = \CBOX\OL\MemberType::get_instance_from_wp_post( $type_post );
	}

	if ( $switched ) {
		restore_current_blog();
	}

	return $types;
}

function cboxol_membertypes_admin_page() {
	wp_enqueue_script( 'cbox-ol-app' );

	$types = cboxol_get_member_types( array(
		'enabled' => null,
	) );

	$type_data = array();
	foreach ( $types as $type ) {
		$type_data[ $type->get_slug() ] = $type->get_for_endpoint();
	}

	$dummy = \CBOX\OL\MemberType::get_dummy();
	$dummy_data = $dummy->get_for_endpoint();

	$app_config = array(
		'subapp' => 'TypesUI',
		'objectType' => 'member',
		'types' => $type_data,
		'dummy' => $dummy_data,
	);

	$registration_url = self_admin_url( add_query_arg( array(
		'page' => cboxol_admin_slug( 'member-settings' ),
		'cboxol-section' => 'registration',
	), 'admin.php' ) );

	?>

	<div class="cboxol-admin-content">
		<p><?php printf( __( 'Member Types are used to organize your site’s users. Members are able to choose their own Member Type according to the rules that you configure on this page, as well as in <a href="%s">Registration settings</a>.', 'cbox-openlab-core' ), esc_url( $registration_url ) ); ?></p>

		<script type="text/javascript">
			var CBOXOL_AppConfig = <?php echo json_encode( $app_config ); ?>;
		</script>

		<div id="cboxol-admin"></div>
	</div>

	<?php
}

/**
 * Get a user's MemberType object.
 *
 * @param int $user_id ID of the user.
 * @return \CBOX\OL\MemberType|WP_Error
 */
function cboxol_get_user_member_type( $user_id ) {
	$type = bp_get_member_type( $user_id );
	if ( ! $type ) {
		return new WP_Error( 'no_member_type', __( 'This user does not have a member type.', 'cbox-openlab-core' ), $user_id );
	}

	return cboxol_get_member_type( $type );
}

/**
 * Get the (singular) label corresponding to a user's member type.
 *
 * @param int $user_id
 * @return string
 */
function cboxol_get_user_member_type_label( $user_id ) {
	$label = '';
	$member_type = bp_get_member_type( $user_id );
	if ( $member_type ) {
		$member_type_obj = bp_get_member_type_object( $member_type );
		if ( $member_type_obj ) {
			$label = $member_type_obj->labels['singular_name'];
		}
	}

	return $label;
}

/**
 * Get a list of selectable member types for a given user.
 *
 * @param int $user_id
 * @return array
 */
function cboxol_get_selectable_member_types_for_user( $user_id ) {
	$selectable_types = array();

	$type_obj = cboxol_get_user_member_type( $user_id );
	if ( ! is_wp_error( $type_obj ) ) {
		$selectable_types = $type_obj->get_selectable_types();
	}

	return $selectable_types;
}

/**
 * Get whether a user can create courses.
 *
 * Should map to meta caps at some point so that we don't have to do manual
 * super admin checks, etc.
 *
 * @param int $user_id
 * @return array
 */
function cboxol_user_can_create_courses( $user_id ) {
	$can = false;

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	$type = cboxol_get_user_member_type( $user_id );
	if ( is_wp_error( $type ) ) {
		return $can;
	}

	return $type->get_can_create_courses();
}

/**
 * Process a change in member type initiated from the profile edit screen.
 *
 * @param int $user_id
 */
function cboxol_membertypes_process_change( $user_id ) {
	if ( ! isset( $_POST['member-type'] ) ) {
		return;
	}

	$new_type = wp_unslash( $_POST['member-type'] );

	// Ensure that user has the ability to do this.
	$can_change = current_user_can( 'bp_moderate' );
	if ( ! $can_change ) {
		$selectable_types = cboxol_get_selectable_member_types_for_user( $user_id );
		$can_change = in_array( $new_type, $selectable_types, true );
	}

	// Will return here if there's no change.
	if ( ! $can_change ) {
		return;
	}

	bp_set_member_type( $user_id, $new_type );
}
