<?php
if ( $site_id ) {
	$option_name = sprintf(
		// translators: 1. Numeric ID of site, 2. Name of site, 3. URL of site
		__( '#%1$s %2$s (%3$s)', 'commons-in-a-box' ),
		$site_id,
		$site_name,
		$site_url
	);
} else {
	$option_name = '';
}
?>

<label class="screen-reader-text" for="template-site-id"><?php esc_html_e( 'Template Site', 'cboxol-site-template-picker' ); ?></label>

<p><?php esc_html_e( 'Select the site whose settings and content will be copied when using this template.', 'commons-in-a-box' ); ?></p>

<select class="widefat" name="template-site-id" id="template-site-id" required>
	<option value="<?php echo esc_attr( $site_id ); ?>" selected="selected"><?php echo esc_html( $option_name ); ?></option>
</select>

<?php if ( $is_create ) : ?>
	<p class="description"><?php esc_html_e( 'You may select an existing site. If no site is selected, a new site will be created automatically to serve as the template.', 'commons-in-a-box' ); ?></p>
<?php endif; ?>

<?php wp_nonce_field( 'cboxol-template-site', 'cboxol-template-site-nonce', false ); ?>
