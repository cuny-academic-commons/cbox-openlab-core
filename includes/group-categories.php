<?php

/**
 * Group categories.
 */

function cboxol_groupcategories_admin_page() {
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
		'subapp' => 'GroupCategoriesUI',
		'objectType' => 'group',
		'types' => $type_data,
		'dummy' => $dummy_data,
	);

	?>

	<?php /* @todo */ ?>
	<p>Group Types are et officia pariatur tenetur autem. Libero illum quaerat cum iusto non. Voluptatem dignissimos et suscipit nesciunt eum nobis deleniti maiores. Dolor voluptatem qui aut maiores ut. Veritatis rerum velit aut laborum et ut ut. Aut quo nostrum assumenda dolorem quibusdam deleniti consequatur doloremque.</p>

	<script type="text/javascript">
		var CBOXOL_AppConfig = <?php echo json_encode( $app_config ); ?>;
	</script>

	<div id="cboxol-admin"></div>

	<?php
}
