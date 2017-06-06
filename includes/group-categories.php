<?php

/**
 * Group categories.
 */

function cboxol_groupcategories_admin_page() {
	wp_enqueue_script( 'cbox-ol-app' );

	$cats = cboxol_get_group_categories();

	$cats_data = array();
	foreach ( $cats as $cat ) {
		$cats_data[ $cat->get_slug() ] = $cat->get_for_endpoint();
	}

	$group_types = cboxol_get_group_types();
	$group_types_data = array();
	foreach ( $group_types as $group_type ) {
		$group_types_data[ $group_type->get_slug() ] = $group_type->get_for_endpoint();
	}

	$dummy = \CBOX\OL\GroupCategory::get_dummy();
	$dummy_data = $dummy->get_for_endpoint();

	$app_config = array(
		'subapp' => 'GroupCategoriesUI',
		'groupCategories' => $cats_data,
		'groupTypes' => $group_types_data,
		'dummy' => $dummy_data,
	);

	?>

	<?php /* @todo */ ?>
	<p>Group Categories are et officia pariatur tenetur autem. Libero illum quaerat cum iusto non. Voluptatem dignissimos et suscipit nesciunt eum nobis deleniti maiores. Dolor voluptatem qui aut maiores ut. Veritatis rerum velit aut laborum et ut ut. Aut quo nostrum assumenda dolorem quibusdam deleniti consequatur doloremque.</p>

	<script type="text/javascript">
		var CBOXOL_AppConfig = <?php echo json_encode( $app_config ); ?>;
	</script>

	<div id="cboxol-admin"></div>

	<?php
}

function cboxol_get_group_categories() {
	$args = array(
		'taxonomy' => 'bp_group_categories',
		'hide_empty' => false,
	);

	$terms = get_terms( $args );

	$cats = array();
	foreach ( $terms as $term ) {
		$cat = \CBOX\OL\GroupCategory::get_instance_from_wp_term( $term );
		$cats[ $term->name ] = $cat;
	}

	// Not ideal, but accounts better for cases where no 'order' termmeta exists.
	uasort( $cats, function( $a, $b ) {
		$a_order = $a->get_order();
		$b_order = $b->get_order();

		if ( $a_order === $b_order ) {
			return 0;
		}

		return $a_order > $b_order ? 1 : -1;
	} );

	return $cats;
}
