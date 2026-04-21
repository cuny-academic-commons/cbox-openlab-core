<label class="screen-reader-text" for="excerpt"><?php esc_html_e( 'Description', 'commons-in-a-box' ); ?></label>
<?php
wp_editor(
	html_entity_decode( $description, ENT_QUOTES, 'UTF-8' ),
	'excerpt',
	[
		'media_buttons' => false,
		'textarea_name' => 'excerpt',
		'textarea_rows' => 5,
		'teeny'         => true,
		'quicktags'     => [ 'buttons' => 'link' ],
		'tinymce'       => [
			'toolbar1' => 'bold,italic,link,unlink',
			'toolbar2' => '',
		],
	]
);
?>
