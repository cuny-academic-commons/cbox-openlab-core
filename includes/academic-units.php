<?php

/**
 * Academic Units functionality.
 */

add_action( 'init', 'cboxol_academic_units_register_post_types' );
add_action( 'bp_signup_usermeta', 'cboxol_save_signup_academic_units' );

// Run at 20 to ensure that member type is set.
add_action( 'bp_core_activated_user', 'cboxol_save_activated_user_academic_units', 20, 3 );
add_action( 'xprofile_updated_profile', 'cboxol_academic_units_process_change_for_user' );

add_action( 'groups_group_after_save', 'cboxol_academic_units_process_change_for_group', 20 );

/**
 * Register post types for Academic Units.
 *
 * @since 1.0.0
 */
function cboxol_academic_units_register_post_types() {
	register_post_type( 'cboxol_acadunit_type', array(
		'labels' => array(
			'name' => _x( 'Academic Unit Types', 'Post type general name', 'cbox-openlab-core' ),
		),
		'public' => false,
		'publicly_queryable' => false,
		'show_ui' => false,
		'show_in_menu' => false,
	) );

	register_post_type( 'cboxol_acadunit', array(
		'labels' => array(
			'name' => _x( 'Academic Units', 'Post type general name', 'cbox-openlab-core' ),
		),
		'public' => false,
		'publicly_queryable' => false,
		'show_ui' => false,
		'show_in_menu' => false,
	) );

	register_taxonomy( 'cboxol_member_in_acadunit', 'user', array(
		'public' => false,
	) );

	register_taxonomy( 'cboxol_group_in_acadunit', 'bp_group', array(
		'public' => false,
	) );
}

function cboxol_academic_units_main_admin_page() {
	wp_enqueue_script( 'cbox-ol-app' );

	$type_data = array();
	$academic_unit_types = cboxol_get_academic_unit_types();
	foreach ( $academic_unit_types as $academic_unit_type ) {
		$type_data[ $academic_unit_type->get_slug() ] = $academic_unit_type->get_for_endpoint();
	}

	$unit_data = array();

	// 'new' for each unit type.
	foreach ( $academic_unit_types as $academic_unit_type ) {
		$unit_slug = '_new-' . $academic_unit_type->get_slug();
		$unit_data[ $unit_slug ] = array(
			'count' => 0,
			'id' => 0,
			'name' => '',
			'order' => 0,
			'parent' => '',
			'type' => $academic_unit_type->get_slug(),
			'slug' => $unit_slug,
		);
	}

	$academic_units = cboxol_get_academic_units();
	foreach ( $academic_units as $academic_unit ) {
		$unit_data[ $academic_unit->get_slug() ] = $academic_unit->get_for_endpoint();
	}

	$mtypes = cboxol_get_member_types();
	$member_types = array();
	foreach ( $mtypes as $mtype ) {
		$slug = $mtype->get_slug();
		$member_types[ $slug ] = array(
			'value' => $slug,
			'label' => $mtype->get_label( 'singular' ),
		);
	}

	$gtypes = cboxol_get_group_types();
	$group_types = array();
	foreach ( $gtypes as $gtype ) {
		$slug = $gtype->get_slug();
		$group_types[ $slug ] = array(
			'value' => $slug,
			'label' => $gtype->get_label( 'singular' ),
		);
	}

	$dummy = \CBOX\OL\AcademicUnitType::get_dummy();
	$dummy_data = $dummy->get_for_endpoint();

	$app_config = array(
		'subapp' => 'AcademicUnitsUI',
		'objectType' => 'member',
		'academicUnits' => $unit_data,
		'academicUnitTypes' => $type_data,
		'dummy' => $dummy_data,
		'groupTypes' => $group_types,
		'memberTypes' => $member_types,
	);

	?>
	<div class="cboxol-admin-content">
		<?php /* @todo */ ?>
		<p>Academic Units officia pariatur tenetur autem. Libero illum quaerat cum iusto non. Voluptatem dignissimos et suscipit nesciunt eum nobis deleniti maiores. Dolor voluptatem qui aut maiores ut. Veritatis rerum velit aut laborum et ut ut. Aut quo nostrum assumenda dolorem quibusdam deleniti consequatur doloremque.</p>

		<script type="text/javascript">
			var CBOXOL_AppConfig = <?php echo wp_json_encode( $app_config ); ?>;
		</script>

		<div id="cboxol-admin"></div>
	</div>
	<?php
}

/**
 * Get registered Academic Unit Types.
 *
 * @params array $args
 */
function cboxol_get_academic_unit_types( $args = array() ) {
	$r = array_merge( array(
		'member_type' => null,
		'group_type' => null,
	), $args );

	$post_args = array(
		'post_type' => 'cboxol_acadunit_type',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'orderby' => array(
			'menu_order' => 'ASC',
			'title' => 'ASC',
		),
		'fields' => 'ids',
	);

	$last_changed = wp_cache_get_last_changed( 'posts' );
	$cache_key = 'cboxol_types_' . md5( wp_json_encode( $post_args ) ) . '_' . $last_changed;
	$ids = wp_cache_get( $cache_key, 'cboxol_academic_unit_types' );
	if ( false === $ids ) {
		$ids = get_posts( $post_args );
		_prime_post_caches( $ids );
		wp_cache_set( $cache_key, $ids, 'cboxol_academic_unit_types' );
	}

	$type_posts = array_map( 'get_post', $ids );

	$types = array();
	foreach ( $type_posts as $type_post ) {
		$type_obj = \CBOX\OL\AcademicUnitType::get_instance_from_wp_post( $type_post );

		if ( null !== $r['member_type'] && ! $type_obj->is_selectable_by_member_type( $r['member_type'] ) ) {
			continue;
		}

		if ( null !== $r['group_type'] && ! $type_obj->is_selectable_by_group_type( $r['group_type'] ) ) {
			continue;
		}

		$types[ $type_obj->get_slug() ] = $type_obj;
	}

	return $types;
}

/**
 * Get a specific academic unit type.
 *
 * @param string Type slug.
 * @return \WP_Error|\CBOX\OL\AcademicUnitType
 */
function cboxol_get_academic_unit_type( $slug ) {
	if ( $slug ) {
		$types = cboxol_get_academic_unit_types();
		foreach ( $types as $type ) {
			if ( $type->get_slug() === $slug ) {
				return $type;
			}
		}
	}

	return new WP_Error( 'no_academic_unit_type_found', __( 'No academic unit type found.', 'cbox-openlab-core' ) );
}

/**
 * Get registered Academic Units.
 *
 * @params array $args
 */
function cboxol_get_academic_units( $args = array() ) {
	$r = array_merge( array(
		'type' => null,
	), $args );

	$post_args = array(
		'post_type' => 'cboxol_acadunit',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'orderby' => array(
			'menu_order' => 'ASC',
			'title' => 'ASC',
		),
		'fields' => 'ids',
	);

	// For a simplified cache, filters are post-processed.
	$last_changed = wp_cache_get_last_changed( 'posts' );
	$cache_key = 'cboxol_types_' . md5( wp_json_encode( $post_args ) ) . '_' . $last_changed;
	$ids = wp_cache_get( $cache_key, 'cboxol_academic_units' );
	if ( false === $ids ) {
		$ids = get_posts( $post_args );
		_prime_post_caches( $ids );
		wp_cache_set( $cache_key, $ids, 'cboxol_academic_units' );
	}

	$type_posts = array_map( 'get_post', $ids );

	$types = array();
	foreach ( $type_posts as $type_post ) {
		$type_obj = \CBOX\OL\AcademicUnit::get_instance_from_wp_post( $type_post );

		if ( null !== $r['type'] && $r['type'] !== $type_obj->get_type() ) {
			continue;
		}

		$types[ $type_obj->get_slug() ] = $type_obj;
	}

	return $types;
}

/**
 * Get a specific academic unit.
 *
 * Assumes unique slugs.
 *
 * @param string Unit slug.
 * @return \WP_Error|\CBOX\OL\AcademicUnit
 */
function cboxol_get_academic_unit( $slug ) {
	if ( $slug ) {
		$units = cboxol_get_academic_units();
		foreach ( $units as $unit ) {
			if ( $unit->get_slug() === $slug ) {
				return $unit;
			}
		}
	}

	return new WP_Error( 'no_academic_unit_found', __( 'No academic unit found.', 'cbox-openlab-core' ) );
}

function cboxol_get_academic_unit_map() {
	$units = cboxol_get_academic_units();
	$retval = array();

	foreach ( $units as $unit ) {
		$retval[] = $unit->get_for_endpoint();
	}

	return $retval;
}

/**
 * Associate an object with academic units.
 */
function cboxol_associate_object_with_academic_units( $args = array() ) {
	$r = array_merge( array(
		'object_id' => null,
		'object_type' => null,
		'type_ids' => null,
	), $args );

	if ( ! $r['object_id'] || ! in_array( $r['object_type'], array( 'user', 'group' ), true ) ) {
		return false;
	}

	$object_id = (int) $r['object_id'];

	$taxonomy = '';
	switch ( $r['object_type'] ) {
		case 'user' :
			$taxonomy = 'cboxol_member_in_acadunit';
			break;

		case 'group' :
			$taxonomy = 'cboxol_group_in_acadunit';
			break;
	}

	if ( ! $taxonomy ) {
		return false;
	}

	$type_slugs = array_map( function( $id ) {
		return 'acad_unit_' . intval( $id );
	}, $r['type_ids'] );

	$set = wp_set_object_terms( $object_id, $type_slugs, $taxonomy, false );

	return ! is_wp_error( $set );
}

function cboxol_get_object_academic_units( $args ) {
	$r = array_merge( array(
		'object_id' => null,
		'object_type' => null,
	), $args );

	if ( ! $r['object_id'] || ! $r['object_type'] ) {
		return false;
	}

	$taxonomy = '';
	switch ( $r['object_type'] ) {
		case 'user' :
			$taxonomy = 'cboxol_member_in_acadunit';
			break;

		case 'group' :
			$taxonomy = 'cboxol_group_in_acadunit';
			break;
	}

	if ( ! $taxonomy ) {
		return false;
	}

	$terms = wp_get_object_terms( $r['object_id'], $taxonomy );

	$units = array();
	foreach ( $terms as $term ) {
		$unit_slug = substr( $term->name, 10 );

		// Pretty elegant.
		$unit_post = get_post( $unit_slug );

		if ( $unit_post ) {
			$unit = cboxol_get_academic_unit( $unit_post->post_name );
			if ( ! is_wp_error( $unit ) ) {
				$units[ $unit->get_slug() ] = $unit;
			}
		}
	}

	if ( $units ) {
		uasort( $units, function( $a, $b ) {
			$a_order = $a->get_order();
			$b_order = $b->get_order();

			if ( $a_order === $b_order ) {
				$a_name = $a->get_name();
				$b_name = $b->get_name();

				return strcasecmp( $a_name, $b_name );
			} else {
				return $a_order > $b_order ? 1 : -1;
			}
		} );
	}

	return $units;
}

function cboxol_get_object_academic_unit_data_for_display( $args = array() ) {
	$r = array_merge( array(
		'object_type' => null,
		'object_id' => null,
	), $args );

	$units = cboxol_get_object_academic_units( $r );

	$type_args = array();
	switch ( $args['object_type'] ) {
		case 'user' :
			$member_type = cboxol_get_user_member_type( $r['object_id'] );
			if ( ! is_wp_error( $member_type ) ) {
				$type_args['member_type'] = $member_type->get_slug();
			}
			break;

		case 'group' :
			$group_type = cboxol_get_group_group_type( $r['object_id'] );
			if ( ! is_wp_error( $group_type ) ) {
				$type_args['group_type'] = $group_type->get_slug();
			}
			break;
	}

	$types = cboxol_get_academic_unit_types( $type_args );

	$type_sorted = array();
	foreach ( $types as $type ) {
		$units_of_type = array();
		foreach ( $units as $unit ) {
			if ( $type->get_slug() === $unit->get_type() ) {
				$units_of_type[] = $unit;
			}
		}

		if ( $units_of_type ) {
			$names = array();
			foreach ( $units_of_type as $unit_of_type ) {
				$names[] = $unit_of_type->get_name();
			}

			// @todo This may not work in all languages.
			if ( count( $units_of_type ) > 1 ) {
				$label = $type->get_label( 'plural' );
			} else {
				$label = $type->get_label( 'singular' );
			}

			$type_sorted[ $type->get_slug() ] = array(
				'label' => $label,
				'value' => implode( ', ', $names ),
			);
		}
	}

	return $type_sorted;
}

/**
 * Get a tax query based on academic units.
 */
function cboxol_get_tax_query_for_academic_units( array $args ) {
	$r = array_merge( array(
		'units' => array(),
		'object_type' => '',
	), $args );

	$taxonomy = '';
	switch ( $r['object_type'] ) {
		case 'user' :
			$taxonomy = 'cboxol_member_in_acadunit';
			break;

		case 'group' :
			$taxonomy = 'cboxol_group_in_acadunit';
			break;
	}

	if ( ! $taxonomy ) {
		return false;
	}

	$term_slugs = array();
	foreach ( $r['units'] as $unit ) {
		$unit_obj = cboxol_get_academic_unit( $unit );
		if ( is_wp_error( $unit_obj ) ) {
			continue;
		}
		$term_slugs[] = 'acad_unit_' . $unit_obj->get_wp_post_id();
	}

	$tax_query = array(
		'taxonomy' => $taxonomy,
		'terms' => $term_slugs,
		'field' => 'slug',
	);

	return $tax_query;
}

/**
 * Get the markup for the Academic Unit selector.
 */
function cboxol_get_academic_unit_selector( $args = array() ) {
	$r = array_merge( array(
		'member_type' => null,
		'group_type' => null,
		'entity_type' => null,
		'selected' => array(),
	), $args );

	$unit_type_args = array();
	if ( null !== $r['member_type'] ) {
		$unit_type_args['member_type'] = $r['member_type'];
	}

	if ( null !== $r['group_type'] ) {
		$unit_type_args['group_type'] = $r['group_type'];
	}

	$academic_unit_types = cboxol_get_academic_unit_types( $unit_type_args );

	wp_enqueue_script( 'cboxol-academic-types', CBOXOL_PLUGIN_URL . '/assets/js/academic-units.js', array( 'jquery' ), false, true );

	$member_type_unit_types = $group_type_unit_types = array();
	foreach ( $academic_unit_types as $academic_unit_type ) {
		foreach ( $academic_unit_type->get_member_types() as $member_type => $setting ) {
			if ( $academic_unit_type->is_selectable_by_member_type( $member_type ) ) {
				$member_type_unit_types[ $member_type ][] = $academic_unit_type->get_slug();
			}
		}
		foreach ( $academic_unit_type->get_group_types() as $group_type => $setting ) {
			$group_type_unit_types[ $group_type ][] = $academic_unit_type->get_slug();
		}
	}

	$entity_type = $r['entity_type'];
	if ( ! in_array( $entity_type, array( 'user', 'group' ), true ) ) {
		$entity_type = '';
	}

	wp_localize_script( 'cboxol-academic-types', 'CBOXOLAcademicTypes', array(
		'entityType' => $entity_type,
		'groupType' => $r['group_type'],
		'typesByMemberType' => $member_type_unit_types,
		'typesByGroupType' => $group_type_unit_types,
	) );

	ob_start();

	?>
	<div class="cboxol-academic-unit-selector">
	<?php

	foreach ( $academic_unit_types as $academic_unit_type ) {
		$units_of_type = cboxol_get_academic_units( array(
			'type' => $academic_unit_type->get_slug(),
		) );

		if ( ! $units_of_type ) {
			continue;
		}

		?>
		<div class="cboxol-academic-unit-selector-for-type cboxol-academic-unit-selector-for-type-<?php echo esc_attr( $academic_unit_type->get_slug() ); ?>">
			<fieldset>
				<legend><?php echo esc_html( $academic_unit_type->get_name() ); ?></legend>

				<div class="cboxol-units-of-type">
					<ul>
						<?php foreach ( $units_of_type as $unit ) : ?>
							<li class="academic-unit academic-unit-visible">
								<?php
								$parent_attr = $unit->get_parent();
								$id_attr = 'academic-unit-' . $unit->get_slug();
								?>

								<input
									<?php checked( in_array( $unit->get_slug(), $r['selected'], true ) ); ?>
									class="academic-unit-checkbox"
									data-parent="<?php echo esc_attr( $parent_attr ); ?>"
									id="<?php echo esc_attr( $id_attr ); ?>"
									name="academic-units[]"
									type="checkbox"
									value="<?php echo esc_attr( $unit->get_slug() ); ?>"
								/> <label for="<?php echo esc_attr( $id_attr ); ?>"><?php echo esc_html( $unit->get_name() ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</fieldset>

			<?php wp_nonce_field( 'cboxol-academic-unit-selector', 'cboxol-academic-unit-selector-nonce', false ); ?>

		</div>
		<?php
	}

	?>
	</div>
	<?php

	$markup = ob_get_contents();
	ob_end_clean();

	return $markup;
}

/**
 * Save user's academic units at registration.
 *
 * @param array $usermeta
 * @return array
 */
function cboxol_save_signup_academic_units( $usermeta ) {
	$academic_units = array();
	if ( isset( $_POST['academic-units'] ) ) {
		$academic_units = wp_unslash( $_POST['academic-units'] );
	}

	$usermeta['academic-units'] = $academic_units;

	return $usermeta;
}

/**
 * Apply a user's chosen academic units at activation.
 *
 * @param int    $user_id
 * @param string $key
 * @param array  $user
 */
function cboxol_save_activated_user_academic_units( $user_id, $key, $user ) {
	$academic_units = array();

	if ( isset( $user['meta']['academic-units'] ) ) {
		$academic_units = $user['meta']['academic-units'];
	}

	$member_type = cboxol_get_user_member_type( $user_id );
	$units_to_save = array();
	if ( $academic_units && ! is_wp_error( $member_type ) ) {
		foreach ( $academic_units as $academic_unit_slug ) {
			$acad_unit_obj = cboxol_get_academic_unit( $academic_unit_slug );
			if ( is_wp_error( $acad_unit_obj ) ) {
				continue;
			}

			$unit_type_obj = cboxol_get_academic_unit_type( $acad_unit_obj->get_type() );
			if ( is_wp_error( $unit_type_obj ) ) {
				continue;
			}

			if ( ! $unit_type_obj->is_selectable_by_member_type( $member_type->get_slug() ) ) {
				continue;
			}

			$units_to_save[] = $acad_unit_obj->get_wp_post_id();

		}
	}

	if ( $units_to_save ) {
		$saved = cboxol_associate_object_with_academic_units( array(
			'object_id' => $user_id,
			'object_type' => 'user',
			'type_ids' => $units_to_save,
		) );
	}
}

/**
 * Process academic unit save from profile edit screen.
 *
 * @param int $user_id
 */
function cboxol_academic_units_process_change_for_user( $user_id ) {
	$academic_units = array();
	if ( isset( $_POST['academic-units'] ) ) {
		$academic_units = wp_unslash( $_POST['academic-units'] );
	}

	// Ensure that user has the ability to do this.
	$can_change = current_user_can( 'bp_moderate' ) || $user_id === bp_loggedin_user_id();
	if ( ! $can_change ) {
		return;
	}

	$member_type = cboxol_get_user_member_type( $user_id );

	$units_to_save = array();
	foreach ( $academic_units as $academic_unit_slug ) {
		$acad_unit_obj = cboxol_get_academic_unit( $academic_unit_slug );
		if ( is_wp_error( $acad_unit_obj ) ) {
			continue;
		}

		$unit_type_obj = cboxol_get_academic_unit_type( $acad_unit_obj->get_type() );
		if ( is_wp_error( $unit_type_obj ) ) {
			continue;
		}

		if ( ! $unit_type_obj->is_selectable_by_member_type( $member_type->get_slug() ) ) {
			continue;
		}

		$units_to_save[] = $acad_unit_obj->get_wp_post_id();
	}

	$saved = cboxol_associate_object_with_academic_units( array(
		'object_id' => $user_id,
		'object_type' => 'user',
		'type_ids' => $units_to_save,
	) );
}

/**
 * Process the saving of a group's academic units.
 */
function cboxol_academic_units_process_change_for_group( $group ) {
	$nonce = '';

	if ( isset( $_POST['cboxol-academic-unit-selector-nonce'] ) ) {
		$nonce = urldecode( $_POST['cboxol-academic-unit-selector-nonce'] );
	}

	if ( ! wp_verify_nonce( $nonce, 'cboxol-academic-unit-selector' ) ) {
		return;
	}

	// Admins only.
	if ( ! groups_is_user_admin( bp_loggedin_user_id(), $group->id ) ) {
		return;
	}

	$academic_units = array();
	if ( isset( $_POST['academic-units'] ) ) {
		$academic_units = wp_unslash( $_POST['academic-units'] );
	}

	if ( bp_is_group_create() ) {
		// This is a hack because group type association happens a bit later.
		$group_type = cboxol_get_group_type( $_POST['group-type'] );
	} else {
		$group_type = cboxol_get_group_group_type( $group->id );
	}

	$units_to_save = array();
	foreach ( $academic_units as $academic_unit_slug ) {
		$acad_unit_obj = cboxol_get_academic_unit( $academic_unit_slug );
		if ( is_wp_error( $acad_unit_obj ) ) {
			continue;
		}

		$unit_type_obj = cboxol_get_academic_unit_type( $acad_unit_obj->get_type() );
		if ( is_wp_error( $unit_type_obj ) ) {
			continue;
		}

		if ( ! $unit_type_obj->is_selectable_by_group_type( $group_type->get_slug() ) ) {
			continue;
		}

		$units_to_save[] = $acad_unit_obj->get_wp_post_id();
	}

	$saved = cboxol_associate_object_with_academic_units( array(
		'object_id' => $group->id,
		'object_type' => 'group',
		'type_ids' => $units_to_save,
	) );
}


/**
 * Shim for missing tax_query functionality in BP group queries.
 */
function cboxol_shim_tax_query_for_bp_groups( $sql, $sql_array, $params ) {
	global $wpdb;

	$academic_units = array();
	foreach ( $_GET as $get_key => $get_value ) {
		if ( 'academic-unit-' !== substr( $get_key, 0, 14 ) ) {
			continue;
		}

		if ( empty( $get_value ) ) {
			continue;
		}

		if ( 'all' === $get_value ) {
			$type_slug = substr( $get_key, 14 );
			$units_of_type = cboxol_get_academic_units( array(
				'type' => $type_slug,
			) );
			foreach ( $units_of_type as $unit_of_type ) {
				$academic_units[] = $unit_of_type->get_slug();
			}
		} else {
			$academic_units[] = urldecode( wp_unslash( $get_value ) );
		}
	}

	$academic_units = array_filter( $academic_units );

	if ( ! $academic_units ) {
		return $sql;
	}

	$term_slugs = array_map( function( $unit_slug ) {
		$unit = cboxol_get_academic_unit( $unit_slug );
		if ( ! is_wp_error( $unit ) ) {
			return 'acad_unit_' . $unit->get_wp_post_id();
		}
	}, $academic_units );

	// Convert to IN.
	$term_ids = get_terms( array(
		'taxonomy' => 'cboxol_group_in_acadunit',
		'orderby' => 'none',
		'hide_empty' => false,
		'slug' => $term_slugs,
		'fields' => 'ids',
	) );

	$object_ids = get_objects_in_term( $term_ids, 'cboxol_group_in_acadunit' );
	if ( ! $object_ids ) {
		$object_ids = array( 0 );
	}

	if ( 'bp_groups_get_paged_groups_sql' === current_filter() ) {
		$sql = str_replace( 'ORDER BY', ' AND g.id IN (' . implode( ',', array_map( 'intval', $object_ids ) ) . ') ORDER BY', $sql );
	} else {
		$sql .= ' AND g.id IN (' . implode( ',', array_map( 'intval', $object_ids ) ) . ')';
	}
	return $sql;
}
add_filter( 'bp_groups_get_paged_groups_sql', 'cboxol_shim_tax_query_for_bp_groups', 10, 3 );
add_filter( 'bp_groups_get_total_groups_sql', 'cboxol_shim_tax_query_for_bp_groups', 10, 3 );

/**
 * Shim for missing tax_query functionality in BP user queries.
 */
function cboxol_shim_tax_query_for_bp_members( BP_User_Query $query ) {
	global $wpdb;

	$academic_units = array();
	foreach ( $_GET as $get_key => $get_value ) {
		if ( 'academic-unit-' !== substr( $get_key, 0, 14 ) ) {
			continue;
		}

		if ( empty( $get_value ) ) {
			continue;
		}

		if ( 'all' === $get_value ) {
			$type_slug = substr( $get_key, 14 );
			$units_of_type = cboxol_get_academic_units( array(
				'type' => $type_slug,
			) );
			foreach ( $units_of_type as $unit_of_type ) {
				$academic_units[] = $unit_of_type->get_slug();
			}
		} else {
			$academic_units[] = urldecode( wp_unslash( $get_value ) );
		}
	}

	$academic_units = array_filter( $academic_units );

	if ( ! $academic_units ) {
		return;
	}

	$term_slugs = array_map( function( $unit_slug ) {
		$unit = cboxol_get_academic_unit( $unit_slug );
		if ( ! is_wp_error( $unit ) ) {
			return 'acad_unit_' . $unit->get_wp_post_id();
		}
	}, $academic_units );

	// Convert to IN.
	$term_ids = get_terms( array(
		'taxonomy' => 'cboxol_member_in_acadunit',
		'orderby' => 'none',
		'hide_empty' => false,
		'slug' => $term_slugs,
		'fields' => 'ids',
	) );

	$object_ids = get_objects_in_term( $term_ids, 'cboxol_member_in_acadunit' );
	if ( ! $object_ids ) {
		$object_ids = array( 0 );
	}

	if ( empty( $query->query_vars['include'] ) ) {
		$query->query_vars['include'] = $object_ids;
	} else {
		$include = (array) $query->query_vars['include'];
		$query->query_vars['include'] = array_intersect( $query->query_vars['include'], $object_ids );
	}
}
add_filter( 'bp_pre_user_query_construct', 'cboxol_shim_tax_query_for_bp_members' );
