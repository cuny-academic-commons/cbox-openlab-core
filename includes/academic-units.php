<?php

/**
 * Academic Units functionality.
 */

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
			var CBOXOL_AppConfig = <?php echo json_encode( $app_config ); ?>;
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
	$cache_key = 'cboxol_types_' . md5( json_encode( $post_args ) ) . '_' . $last_changed;
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

		$types[ $type_obj->get_slug() ] = $type_obj;
	}

	return $types;
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
	$cache_key = 'cboxol_types_' . md5( json_encode( $post_args ) ) . '_' . $last_changed;
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
 * Get a map of academic units.
 */
function cboxol_get_academic_unit_map() {
	$units = cboxol_get_academic_units();
	$map = array();
	foreach ( $units as $unit ) {

		//$ids[ $unit->get_wp_post_id() ] =
	}
}

/**
 * Get the markup for the Academic Unit selector.
 */
function cboxol_get_academic_unit_selector( $args = array() ) {
	// @todo selected
	$r = array_merge( array(
		'member_type' => null,
	), $args );

	$academic_unit_types = cboxol_get_academic_unit_types( array(
		'member_type' => $r['member_type'],
	) );

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
	wp_localize_script( 'cboxol-academic-types', 'CBOXOLAcademicTypes', array(
		'typesByMemberType' => $member_type_unit_types,
		'typesByGroupType' => $group_type_unit_types,
	) );

	ob_start();

	?>
	<div class="cboxol-academic-unit-selector">
	<?php

	$map = cboxol_get_academic_unit_map();

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
