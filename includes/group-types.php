<?php

add_action( 'init', 'cboxol_grouptypes_register_post_type' );
add_action( 'bp_groups_register_group_types', 'cboxol_grouptypes_register_group_types' );

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

	if ( !$group_id ) {
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

function openlab_is_portfolio( $group_id = 0 ) { return openlab_is_group_type( $group_id, 'portfolio' ); }

function openlab_is_club( $group_id = 0 ) { return openlab_is_group_type( $group_id, 'club' ); }


// @todo abstract with Member Types?
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

	?>
	<div class="wrap cboxol-admin-wrap">
		<?php cboxol_admin_header( 'group-settings', 'types' ); ?>

		<div class="cboxol-admin-content">
			<?php /* @todo */ ?>
			<p>Group Types are et officia pariatur tenetur autem. Libero illum quaerat cum iusto non. Voluptatem dignissimos et suscipit nesciunt eum nobis deleniti maiores. Dolor voluptatem qui aut maiores ut. Veritatis rerum velit aut laborum et ut ut. Aut quo nostrum assumenda dolorem quibusdam deleniti consequatur doloremque.</p>

			<script type="text/javascript">
				var CBOXOL_ObjectType = 'group';
				var CBOXOL_Types = <?php echo json_encode( $type_data ); ?>;
				var CBOXOL_Dummy = <?php echo json_encode( $dummy_data ); ?>;
			</script>

			<div id="cboxol-types-admin">
				<cboxol-types-admin object="group"></cboxol-types-admin>
			</div>
		</div>
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

	return null;
}

/**
 * Get registered Group Types.
 *
 * @params array $args {
 *     Array of optional arguments.
 *     @type bool|null $enabled Filter by 'enabled' status. True returns only enabled Types, false returns
 *                              only disabled types. Null returns all.
 * }
 */
function cboxol_get_group_types( $args = array() ) {
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
		'post_type' => 'cboxol_group_type',
		'post_status' => 'any',
		'posts_per_page' => -1,
		'orderby' => array(
			'menu_order' => 'ASC',
			'title' => 'ASC',
		),
	) );

	$types = array();
	foreach ( $type_posts as $type_post ) {
		$types[ $type_post->post_name ] = \CBOX\OL\GroupType::get_instance_from_wp_post( $type_post );
	}

	return $types;
}
