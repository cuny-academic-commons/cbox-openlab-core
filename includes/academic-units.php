<?php

/**
 * Academic Units functionality.
 */

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
		$unit_data[ '_new-' . $academic_unit_type->get_slug() ] = array(
			'name' => '',
			'parent' => '',
		);
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
		$types[ $type_post->post_name ] = \CBOX\OL\AcademicUnitType::get_instance_from_wp_post( $type_post );
	}

	return $types;
}
