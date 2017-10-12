<?php

function cboxol_brand_admin_page() {
	$url_base = get_admin_url( bp_get_root_blog_id(), 'customize.php' );
	$customize_url = add_query_arg( 'return', urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), $url_base );

	$pages = cboxol_get_brand_pages();

	?>
	<div class="cboxol-admin-content">
		<h3 class="cboxol-admin-content-header"><?php esc_html_e( 'Visual', 'cbox-openlab-core' ); ?></h3>
		<div class="cboxol-admin-content-copy">
			<p>Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut </p>

			<p>
				<a class="button" href="<?php echo esc_url( $customize_url ); ?>"><?php esc_html_e( 'Customize', 'cbox-openlab-core' ); ?></a>
			</p>
		</div>
	</div>

	<div class="cboxol-admin-content has-top-border">
		<h3 class="cboxol-admin-content-header"><?php esc_html_e( 'Copy', 'cbox-openlab-core' ); ?></h3>
		<div class="cboxol-admin-content-copy">
			<p>Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut Qui fugiat alias rem dolor sint. Ullam minima corrupti voluptatem. Commodi vel quae aut </p>

			<?php foreach ( $pages as $page_name => $page_info ) : ?>
				<?php if ( ! empty( $page_info['id'] ) ) : ?>
					<div class="cboxol-admin-content-subsection">
						<h4 class="cboxol-admin-content-subsection-header"><?php echo esc_html( $page_info['settings_page_title'] ); ?></h4>
						<p><?php echo esc_html( $page_info['settings_page_description'] ); ?></p>

						<div class="cboxol-brand-settings-copy-links">
							<a href="<?php echo esc_url( $page_info['edit_url'] ); ?>"><?php esc_html_e( 'Edit', 'cbox-openlab-core' ); ?></a> | <a href="<?php echo esc_url( $page_info['preview_url'] ); ?>"><?php esc_html_e( 'Preview', 'cbox-openlab-core' ); ?></a>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php
}

function cboxol_get_brand_page_types() {
	return array(
		'about' => array(
			'settings_page_title' => __( 'About Page', 'cbox-openlab-core' ),
			'settings_page_description' => __( 'Blanditiis quia autem culpa voluptate. Consequuntur ipsum pariatur accusamus porro incidunt ut sint non. Eius alias rem expedita iste. Esse dignissimos fugiat veniam pariatur voluptatibus.', 'cbox-openlab-core' ),
		),
		'help' => array(
			'settings_page_title' => __( 'Help Page', 'cbox-openlab-core' ),
			'settings_page_description' => __( 'Blanditiis quia autem culpa voluptate. Consequuntur ipsum pariatur accusamus porro incidunt ut sint non. Eius alias rem expedita iste. Esse dignissimos fugiat veniam pariatur voluptatibus.', 'cbox-openlab-core' ),
		),
		'terms-of-use' => array(
			'settings_page_title' => __( 'Contact Page', 'cbox-openlab-core' ),
			'settings_page_description' => __( 'Blanditiis quia autem culpa voluptate. Consequuntur ipsum pariatur accusamus porro incidunt ut sint non. Eius alias rem expedita iste. Esse dignissimos fugiat veniam pariatur voluptatibus.', 'cbox-openlab-core' ),
			'parent' => 'about',
		),
		'contact-us' => array(
			'settings_page_title' => __( 'Contact Page', 'cbox-openlab-core' ),
			'settings_page_description' => __( 'Blanditiis quia autem culpa voluptate. Consequuntur ipsum pariatur accusamus porro incidunt ut sint non. Eius alias rem expedita iste. Esse dignissimos fugiat veniam pariatur voluptatibus.', 'cbox-openlab-core' ),
			'parent' => 'about',
		),
	);
}

function cboxol_get_brand_pages() {
	$brand_page_types = cboxol_get_brand_page_types();
	$pages = array();
	foreach ( $brand_page_types as $brand_page_type_name => $brand_page_type ) {
		$pages[ $brand_page_type_name ] = array_merge( $brand_page_type, array(
			'id' => 0,
			'title' => '',
			'edit_url' => '',
			'preview_url' => '',
		) );
	}

	$page_ids = get_site_option( 'cboxol_brand_page_ids' );

	$main_site_id = cbox_get_main_site_id();
	$switched = false;
	if ( get_current_blog_id() !== $main_site_id ) {
		switch_to_blog( $main_site_id );
		$switched = true;
	}

	foreach ( $page_ids as $page_type => $page_id ) {
		$page = get_page( $page_id );
		if ( ! $page || 'page' !== $page->post_type ) {
			continue;
		}

		$pages[ $page_type ]['id'] = $page_id;
		$pages[ $page_type ]['title'] = get_the_title( $page_id );
		$pages[ $page_type ]['edit_url'] = get_admin_url( $main_site_id, 'post.php?post=' . intval( $page_id ) . '&action=edit' );
		$pages[ $page_type ]['preview_url'] = get_permalink( $page_id );
	}

	if ( $switched ) {
		restore_current_blog();
	}

	return $pages;
}

/**
 * Determines whether a given post is a brand page of a specific type.
 *
 * @param string $page_type Type of page, eg 'about'.
 * @param int    $post_id   Optional. Defaults to get_queried_object_id().
 * @return bool
 */
function cboxol_is_brand_page( $page_type, $post_id = null ) {
	$is_brand_page = false;
	if ( null === $post_id ) {
		$post_id = get_queried_object_id();
	}

	$brand_pages = cboxol_get_brand_pages();
	if ( ! isset( $brand_pages[ $page_type ] ) ) {
		return false;
	}

	return $brand_pages[ $page_type ]['id'] === $post_id;
}
