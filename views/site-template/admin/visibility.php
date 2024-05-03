<?php
$all_member_types = cboxol_get_member_types();
?>

<p><?php esc_html_e( 'Control who can select this template when creating a new site.', 'commons-in-a-box' ); ?></p>

<div class="cboxol-site-template-visibility-section">
	<fieldset class="template-visibility-radios">
		<legend><?php esc_html_e( 'By Member Type', 'commons-in-a-box' ); ?></legend>
		<label><input type="radio" name="template-visibility-limit-by-member-type" id="template-visibility-limit-by-member-type-yes" value="yes" aria-controls="template-visibility-suboptions-member-type" <?php checked( $limit_by_member_types ); ?> /> <?php esc_html_e( 'Restrict by member type', 'commons-in-a-box' ); ?></label><br />
		<fieldset class="template-visibility-suboptions" id="template-visibility-suboptions-member-type">
			<legend><?php esc_html_e( 'This template will be available only to users belonging to the member types selected below:', 'commons-in-a-box' ); ?></legend>
			<?php foreach ( $all_member_types as $member_type ) : ?>
				<label><input type="checkbox" name="template-visibility-limit-to-member-types[]" value="<?php echo esc_attr( $member_type->get_slug() ); ?>" <?php checked( isset( $selected_member_types[ $member_type->get_slug() ] ) ); ?> /> <?php echo esc_html( $member_type->get_name() ); ?></label><br />
			<?php endforeach; ?>
		</fieldset>

		<label><input type="radio" name="template-visibility-limit-by-member-type" id="template-visibility-limit-by-member-type-no" value="no" aria-controls="template-visibility-suboptions-member-type" <?php checked( ! $limit_by_member_types ); ?> /> <?php esc_html_e( 'Allow for all member types', 'commons-in-a-box' ); ?></label>
	</fieldset>
</div>

<?php wp_nonce_field( 'cboxol-template-visibility', 'cboxol-template-visibility-nonce', false ); ?>
