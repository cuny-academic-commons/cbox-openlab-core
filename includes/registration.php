<?php

function cboxol_registration_admin_page() {
	wp_enqueue_script( 'cbox-ol-app' );
	?>

	<div id="cboxol-registration-admin">
		<cboxol-registration-admin></cboxol-registration-admin>
	</div>

	<?php
}
