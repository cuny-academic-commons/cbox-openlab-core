<?php

/**
 * Member types.
 */

add_action( 'init', 'cboxol_membertypes_register_post_type' );
add_action( 'bp_register_member_types', 'cboxol_membertypes_register_member_types' );

add_action( 'add_meta_boxes', 'cboxol_membertypes_register_meta_boxes' );
add_action( 'save_post', 'cboxol_membertypes_save_labels' );
add_action( 'save_post', 'cboxol_membertypes_save_settings' );

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
 * @return \CBOX\OL\MemberType|null
 */
function cboxol_get_member_type( $slug ) {
	$types = cboxol_get_member_types( array(
		'enabled' => null,
	) );

	if ( isset( $types[ $slug ] ) ) {
		return $types[ $slug ];
	}

	return null;
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

	$type_posts = get_posts( array(
		'post_type' => 'cboxol_member_type',
		'post_status' => 'any',
		'posts_per_page' => -1,
		'orderby' => array(
			'menu_order' => 'ASC',
			'title' => 'ASC',
		),
	) );

	$types = array();
	foreach ( $type_posts as $type_post ) {
		$types[ $type_post->post_name ] = \CBOX\OL\MemberType::get_instance_from_wp_post( $type_post );
	}

	return $types;
}

function cboxol_membertypes_admin_page() {

	$types = cboxol_get_member_types( array(
		'enabled' => null,
	) );

	$type_data = array();
	foreach ( $types as $type ) {
		$type_data[ $type->get_slug() ] = $type->get_for_endpoint();
	}

	$dummy = \CBOX\OL\MemberType::get_dummy();
	$dummy_data = $dummy->get_for_endpoint();

	?>
	<div class="wrap cboxol-admin-wrap">
		<?php cboxol_admin_header( 'member-settings', 'types' ); ?>

		<div class="cboxol-admin-content">
			<?php /* @todo */ ?>
			<p>Member Types are et officia pariatur tenetur autem. Libero illum quaerat cum iusto non. Voluptatem dignissimos et suscipit nesciunt eum nobis deleniti maiores. Dolor voluptatem qui aut maiores ut. Veritatis rerum velit aut laborum et ut ut. Aut quo nostrum assumenda dolorem quibusdam deleniti consequatur doloremque.</p>

			<script type="text/javascript">
				var CBOXOL_ObjectType = 'member';
				var CBOXOL_Types = <?php echo json_encode( $type_data ); ?>;
				var CBOXOL_Dummy = <?php echo json_encode( $dummy_data ); ?>;
			</script>

			<div id="cboxol-types-admin">
				<cboxol-types-admin object="member"></cboxol-types-admin>
			</div>
		</div>
	</div>
	<?php
}

function cboxol_membertypes_register_meta_boxes() {
	// Labels.
	add_meta_box(
		'cbox-ol-member-type-labels',
		_x( 'Labels', 'Member type labels metabox title', 'cbox-openlab-core' ),
		'cboxol_membertypes_labels_metabox',
		'cboxol_member_type',
		'advanced',
		'high'
	);

	// Settings.
	add_meta_box(
		'cbox-ol-member-type-settings',
		_x( 'Settings', 'Member type settings metabox title', 'cbox-openlab-core' ),
		'cboxol_membertypes_settings_metabox',
		'cboxol_member_type',
		'advanced'
	);
}

function cboxol_membertypes_process_form_submit() {
	if ( ! current_user_can( 'manage_network_settings' ) ) {
		return;
	}

	if ( ! isset( $_POST['types-enable-nonce'] ) || ! wp_verify_nonce( $_POST['types-enable-nonce'], 'types_enable' ) ) {
		return;
	}

	$enabled_types = array();
	if ( isset( $_POST['enabled-types'] ) ) {
		$enabled_types = wp_unslash( $_POST['enabled-types'] );
	}

	$all_types = cboxol_get_member_types( array(
		'enabled' => null,
	) );

	foreach ( $all_types as $type ) {
		if ( $type->get_is_enabled() && ! in_array( $type->get_slug(), $enabled_types, true ) ) {
			wp_update_post( array(
				'ID' => $type->get_wp_post_id(),
				'post_status' => 'draft',
			) );
		} elseif ( ! $type->get_is_enabled() && in_array( $type->get_slug(), $enabled_types, true ) ) {
			wp_update_post( array(
				'ID' => $type->get_wp_post_id(),
				'post_status' => 'publish',
			) );
		}
	}
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

	$type = bp_get_member_type( $user_id );
	if ( $type ) {
		$type_obj = cboxol_get_member_type( $type );
		if ( $type_obj ) {
			$selectable_types = $type_obj->get_selectable_types();
		}
	}

	return $selectable_types;
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
