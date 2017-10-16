<?php

add_action( 'init', 'cboxol_grouptypes_register_post_type' );
add_action( 'bp_groups_register_group_types', 'cboxol_grouptypes_register_group_types' );

// Group creation. We roll our own because we skip BP's validation.
add_action( 'bp_after_group_details_creation_step', 'cboxol_grouptypes_hidden_field' );
add_action( 'groups_group_after_save', 'cboxol_grouptypes_save_group_type', 15 );

// Group creation must always have a group_type.
add_action( 'bp_actions', 'cboxol_enforce_group_type_on_creation' );

/**
 * Get a group type by group id
 *
 * @param int $group_id
 * @return string
 */
function openlab_get_group_type( $group_id = 0 ) {
	if ( ! bp_is_active( 'groups' ) ) {
		return '';
	}

	if ( ! $group_id ) {
		$group_id = openlab_fallback_group();
	}

	$group_type = groups_get_groupmeta( $group_id, 'wds_group_type' );

	/*
	if ( !in_array( $group_type, openlab_group_types() ) ) {
		$group_type = 'group';
	}
	*/

	return $group_type;
}

function openlab_is_group_type( $group_id = 0, $type = 'group' ) {
	return $type == openlab_get_group_type( $group_id );
}

function openlab_is_course( $group_id = 0 ) { return openlab_is_group_type( $group_id, 'course' ); }

function openlab_is_project( $group_id = 0 ) { return openlab_is_group_type( $group_id, 'project' ); }

function cboxol_is_portfolio( $group_id = 0 ) {
	if ( ! $group_id ) {
		$group_id = openlab_fallback_group();
	}

	$group_type = cboxol_get_group_group_type( $group_id );

	return ! is_wp_error( $group_type ) && $group_type->get_is_portfolio();
}

function openlab_is_club( $group_id = 0 ) { return openlab_is_group_type( $group_id, 'club' ); }

function cboxol_grouptypes_admin_page() {
	wp_enqueue_script( 'cbox-ol-app' );

	$types = cboxol_get_group_types( array(
		'enabled' => null,
	) );

	$type_data = array();
	foreach ( $types as $type ) {
		$type_data[ $type->get_slug() ] = $type->get_for_endpoint();
	}

	$dummy = \CBOX\OL\GroupType::get_dummy();
	$dummy_data = $dummy->get_for_endpoint();

	$app_config = array(
		'subapp' => 'TypesUI',
		'objectType' => 'group',
		'types' => $type_data,
		'dummy' => $dummy_data,
	);

	?>

	<div class="cboxol-admin-content">
		<?php /* @todo */ ?>
		<p>Group Types are et officia pariatur tenetur autem. Libero illum quaerat cum iusto non. Voluptatem dignissimos et suscipit nesciunt eum nobis deleniti maiores. Dolor voluptatem qui aut maiores ut. Veritatis rerum velit aut laborum et ut ut. Aut quo nostrum assumenda dolorem quibusdam deleniti consequatur doloremque.</p>

		<script type="text/javascript">
			var CBOXOL_AppConfig = <?php echo json_encode( $app_config ); ?>;
		</script>

		<div id="cboxol-admin"></div>
	</div>

	<?php
}

/**
 * Register the Group Type post type.
 */
function cboxol_grouptypes_register_post_type() {
	register_post_type( 'cboxol_group_type', array(
		'labels' => array(
			'name' => _x( 'Group Types', 'Post type general name', 'cbox-openlab-core' ),
			'singular_name' => _x( 'Group Type', 'Post type singular name', 'cbox-openlab-core' ),
			'add_new_item' => __( 'Add New Group Type', 'cbox-openlab-core' ),
			'new_item' => __( 'New Group Type', 'cbox-openlab-core' ),
			'edit_item' => __( 'Edit Group Type', 'cbox-openlab-core' ),
			'view_item' => __( 'View Group Type', 'cbox-openlab-core' ),
			'all_item' => __( 'All Group Types', 'cbox-openlab-core' ),
			'search_items' => __( 'Search Group Types', 'cbox-openlab-core' ),
			'not_found' => __( 'No group types found.', 'cbox-openlab-core' ),
			'not_found_in_trash' => __( 'No group types found in Trash.', 'cbox-openlab-core' ),
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
function cboxol_grouptypes_register_group_types() {
	$saved_types = cboxol_get_group_types();

	// @todo Conflict checking? Prefixing?
	foreach ( $saved_types as $saved_type ) {
		bp_groups_register_group_type( $saved_type->get_slug(), array(
			'labels' => array(
				'name' => $saved_type->get_label( 'plural' ),
				'singular_name' => $saved_type->get_label( 'singular' ),
			),
			'has_directory' => true,
			'show_in_create_screen' => false,
		) );
	}
}

/**
 * Get a single registered Group Type.
 *
 * @param string $type Slug of the type.
 * @return \CBOX\OL\GroupType|null
 */
function cboxol_get_group_type( $slug ) {
	$types = cboxol_get_group_types( array(
		'enabled' => null,
	) );

	if ( isset( $types[ $slug ] ) ) {
		return $types[ $slug ];
	}

	return new WP_Error( 'no_group_type_found', __( 'No group type found by that slug.', 'cbox-openlab-core' ), $slug );;
}

/**
 * Get registered Group Types.
 *
 * @params array $args {
 *     Array of optional arguments.
 *     @type bool|null $enabled           Filter by 'enabled' status. True returns only enabled Types, false returns
 *                                        only disabled types. Null returns all.
 *     @type bool      $exclude_portfolio Whether to exclude group types where `is_portfolio` is true. Default false.
 * }
 */
function cboxol_get_group_types( $args = array() ) {
	$r = array_merge( array(
		'enabled' => true,
		'exclude_portfolio' => false,
	), $args );

	$post_status = 'publish';
	if ( false === $r['enabled'] ) {
		$post_status = 'draft';
	} elseif ( null === $r['enabled'] ) {
		$post_status = 'any';
	}

	$post_args = array(
		'post_type' => 'cboxol_group_type',
		'post_status' => $post_status,
		'posts_per_page' => -1,
		'orderby' => array(
			'menu_order' => 'ASC',
			'title' => 'ASC',
		),
		'fields' => 'ids',
	);

	$switched = false;
	$main_site_id = cbox_get_main_site_id();
	if ( get_current_blog_id() !== $main_site_id ) {
		switch_to_blog( $main_site_id );
		$switched = true;
	}

	$last_changed = wp_cache_get_last_changed( 'posts' );
	$cache_key = 'cboxol_types_' . md5( json_encode( $post_args ) ) . '_' . $last_changed;
	$ids = wp_cache_get( $cache_key, 'cboxol_group_types' );
	if ( false === $ids ) {
		$ids = get_posts( $post_args );
		_prime_post_caches( $ids );
		wp_cache_set( $cache_key, $ids, 'cboxol_group_types' );
	}

	$type_posts = array_map( 'get_post', $ids );

	$types = array();
	foreach ( $type_posts as $type_post ) {
		$group_type = \CBOX\OL\GroupType::get_instance_from_wp_post( $type_post );

		if ( $r['exclude_portfolio'] && $group_type->get_is_portfolio() ) {
			continue;
		}

		$types[ $type_post->post_name ] = $group_type;
	}

	if ( $switched ) {
		restore_current_blog();
	}

	return $types;
}

/**
 * Get the group type object for a given group.
 *
 * @param int $group_id ID of the group.
 * @return \CBOX\OL\GroupType|WP_Error
 */
function cboxol_get_group_group_type( $group_id ) {
	$type = bp_groups_get_group_type( $group_id );
	if ( ! $type ) {
		return new WP_Error( 'no_group_type', __( 'This group does not have a type.', 'cbox-openlab-core' ), $group_id );
	}

	return cboxol_get_group_type( $type );
}

/**
 * Utility for getting group type of group currently being created or edited.
 *
 * @return WP_Error|\CBOX\OL\GroupType
 */
function cboxol_get_edited_group_group_type() {
	$the_group_id = null;
	if ( bp_is_group() ) {
		$the_group_id = bp_get_current_group_id();
	} elseif ( bp_is_group_create() ) {
		$the_group_id = bp_get_new_group_id();
	}

	$group_type = null;
	if ( $the_group_id ) {
		$group_type = cboxol_get_group_group_type( $the_group_id );
	} elseif ( isset( $_GET['group_type'] ) ) {
		$group_type = cboxol_get_group_type( wp_unslash( urldecode( $_GET['group_type'] ) ) );
	}

	if ( ! $group_type ) {
		return new WP_Error( 'no_group_type', __( 'No group type found.', 'cbox-openlab-core' ) );
	}

	return $group_type;
}

/**
 * Hidden field for group type.
 */
function cboxol_grouptypes_hidden_field() {
	$group_type = null;

	if ( bp_is_group_create() && isset( $_GET['group_type'] ) ) {
		$group_type = wp_unslash( urldecode( $_GET['group_type'] ) );
	}

	$group_type_object = cboxol_get_group_type( $group_type );
	if ( is_wp_error( $group_type_object ) ) {
		return;
	}

	printf(
		'<input type="hidden" name="group-type" value="%s" /><input type="hidden" name="group-type-nonce" value="%s" />',
		esc_attr( $group_type_object->get_slug() ),
		wp_create_nonce( 'cboxol_set_group_type' )
	);
}

/**
 * Save group type.
 */
function cboxol_grouptypes_save_group_type( $group ) {
	if ( ! isset( $_POST['group-type'] ) ) {
		return;
	}

	if ( ! isset( $_POST['group-type-nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['group-type-nonce'], 'cboxol_set_group_type' ) ) {
		return;
	}

	$group_type = cboxol_get_group_type( wp_unslash( $_POST['group-type'] ) );

	// Errors from here down should send user back to previous screen.
	if ( is_wp_error( $group_type ) ) {
		return;
	}

	// Should make this less dumb.
	if ( $group_type->get_is_course() && ! cboxol_user_can_create_courses( bp_loggedin_user_id() ) ) {
		return;
	}

	bp_groups_set_group_type( $group->id, $group_type->get_slug() );
}

/**
 * Enforce that group creation always has an allowed type.
 *
 * If no group type is found, or if the specifed group type is non-existent or
 * off-limits, redirect to the first legal one found.
 */
function cboxol_enforce_group_type_on_creation() {
	if ( ! bp_is_group_create() ) {
		return;
	}

	if ( ! bp_is_action_variable( 'group-details', 1 ) ) {
		return;
	}

	if ( ! empty( $_POST ) ) {
		return;
	}

	$group_type = null;
	if ( isset( $_GET['group_type'] ) ) {
		$group_type = cboxol_get_group_type( wp_unslash( urldecode( $_GET['group_type'] ) ) );
	}

	$redirect = false;
	if ( ! $group_type || is_wp_error( $group_type ) ) {
		$redirect = true;
	} elseif ( $group_type->get_is_course() && ! cboxol_user_can_create_courses( bp_loggedin_user_id() ) ) {
		$redirect = true;
	}

	if ( ! $redirect ) {
		return;
	}

	// Grab the first non-course, non-portfolio group type.
	$types = cboxol_get_group_types();
	$redirect_type = null;
	foreach ( $types as $type ) {
		if ( ! $type->get_is_portfolio() && ! $type->get_is_course() ) {
			$redirect_type = $type;
			break;
		}
	}

	// Sanity check.
	if ( ! $redirect_type ) {
		return;
	}

	$redirect_url = add_query_arg( 'group_type', $redirect_type->get_slug(), bp_get_groups_directory_permalink() . 'create/step/group-details/' );
	wp_redirect( $redirect_url );
	die();
}

/**
 * Get the URL for the group type directory for a user.
 *
 * @param \CBOX\OL\GroupType $group_type Group type object.
 * @param int                $user_id    Optional. Defaults to displayed user.
 * @return string
 */
function cboxol_get_user_group_type_directory_url( \CBOX\OL\GroupType $group_type, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = bp_displayed_user_id();
	}

	$url = bp_core_get_user_domain( $user_id ) . bp_get_groups_slug() . '/';
	$url = add_query_arg( 'group_type', $group_type->get_slug(), $url );

	return $url;
}
