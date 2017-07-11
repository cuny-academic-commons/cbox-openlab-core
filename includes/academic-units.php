<?php

/**
 * Academic Units functionality.
 */

function cboxol_academic_units_main_admin_page() {
	wp_enqueue_script( 'cbox-ol-app' );

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
//		'types' => $type_data,
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
