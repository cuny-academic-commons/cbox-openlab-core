<?php

function cboxol_registration_admin_page() {
	wp_enqueue_script( 'cbox-ol-app' );

	$app_config = array(
		'subapp' => 'Registration',
	);

	?>

	<p>
		Subheader text
	</p>

	<script type="text/javascript">
		var CBOXOL_AppConfig = <?php echo json_encode( $app_config ); ?>;
	</script>

	<div id="cboxol-admin"></div>

	<?php
}
