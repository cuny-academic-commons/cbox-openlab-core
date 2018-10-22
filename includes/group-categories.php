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

	<div class="cboxol-admin-content">
		<p><?php esc_html_e( 'Group categories make it easier to organize and discover groups. Group administrators can add the categories you create here to their groups, which will then be filterable by category on group directory pages.', 'cbox-openlab-core' ); ?></p>

		<script type="text/javascript">
			var CBOXOL_AppConfig = <?php echo json_encode( $app_config ); ?>;
		</script>

		<div id="cboxol-admin"></div>
	</div>

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

/**
 * Remove the plugin's native Dashboard panel.
 */
function cboxol_remove_group_categories_dashboard_panel() {
	remove_menu_page( 'bp-group-categories' );
}
add_action( 'admin_menu', 'cboxol_remove_group_categories_dashboard_panel', 50 );
