<?php

function cboxol_brand_admin_page() {
	$url_base      = get_admin_url( bp_get_root_blog_id(), 'customize.php' );
	$customize_url = add_query_arg( 'return', rawurlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), $url_base );

	$pages = cboxol_get_brand_pages();

	?>
	<div class="cboxol-admin-content">
		<h3 class="cboxol-admin-content-header"><?php esc_html_e( 'Visual', 'commons-in-a-box' ); ?></h3>
		<div class="cboxol-admin-content-copy">
			<p><?php esc_html_e( 'Customize your site’s look, including the color scheme, custom logo, homepage layout, widgets and more.', 'commons-in-a-box' ); ?></p>

			<p>
				<a class="button" href="<?php echo esc_url( $customize_url ); ?>"><?php esc_html_e( 'Customize', 'commons-in-a-box' ); ?></a>
			</p>
		</div>
	</div>

	<div class="cboxol-admin-content has-top-border">
		<h3 class="cboxol-admin-content-header"><?php esc_html_e( 'Copy', 'commons-in-a-box' ); ?></h3>
		<div class="cboxol-admin-content-copy">
			<p><?php esc_html_e( 'View and change the copy on your About, Help, Terms of Use, and other pages through the Edit and Preview links for each page below.', 'commons-in-a-box' ); ?></p>

			<?php foreach ( $pages as $page_name => $page_info ) : ?>
				<?php if ( ! empty( $page_info['id'] ) ) : ?>
					<div class="cboxol-admin-content-subsection">
						<h4 class="cboxol-admin-content-subsection-header"><?php echo esc_html( $page_info['settings_page_title'] ); ?></h4>
						<p><?php echo esc_html( $page_info['settings_page_description'] ); ?></p>

						<div class="cboxol-brand-settings-copy-links">
							<a href="<?php echo esc_url( $page_info['edit_url'] ); ?>"><?php esc_html_e( 'Edit', 'commons-in-a-box' ); ?></a> | <a href="<?php echo esc_url( $page_info['preview_url'] ); ?>"><?php esc_html_e( 'Preview', 'commons-in-a-box' ); ?></a>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php
}

function cboxol_get_brand_page_types() {
	return array(
		'about'          => array(
			'settings_page_title'       => __( 'About Page', 'commons-in-a-box' ),
			'settings_page_description' => __( 'This page can contain an introduction to your site, institution, and/or organization.', 'commons-in-a-box' ),
		),
		'help'           => array(
			'settings_page_title'       => __( 'Help Page', 'commons-in-a-box' ),
			'settings_page_description' => __( 'This section can contain help and support documentation and answers to frequently asked questions for your site’s members and visitors.', 'commons-in-a-box' ),
		),
		'terms-of-use'   => array(
			'settings_page_title'       => __( 'Terms of Service', 'commons-in-a-box' ),
			'settings_page_description' => __( 'This page can contain the Terms of Service for your site. Terms of Service are the rules that a user must abide by while using your site.', 'commons-in-a-box' ),
			'parent'                    => 'about',
		),
		'contact-us'     => array(
			'settings_page_title'       => __( 'Contact Page', 'commons-in-a-box' ),
			'settings_page_description' => __( 'This page can contain contact information for the administrators of your site, which visitors to the site can use when they have questions, comments, or need help.', 'commons-in-a-box' ),
			'parent'                    => 'about',
		),
		'search-results' => array(
			'settings_page_title'       => __( 'Search Results', 'commons-in-a-box' ),
			'settings_page_description' => __( 'This placeholder page is used to display search results. This page\'s contents are generated dynamically, but you can edit the page title and slug.', 'commons-in-a-box' ),
		),
	);
}

/**
 * Gets 'brand' pages.
 *
 * @since 1.1.0
 *
 * @return array
 */
function cboxol_get_brand_pages() {
	$brand_page_types = cboxol_get_brand_page_types();
	$pages            = array();
	foreach ( $brand_page_types as $brand_page_type_name => $brand_page_type ) {
		$pages[ $brand_page_type_name ] = array_merge(
			$brand_page_type,
			array(
				'id'          => 0,
				'title'       => '',
				'edit_url'    => '',
				'preview_url' => '',
			)
		);
	}

	$page_ids = get_site_option( 'cboxol_brand_page_ids' );

	$main_site_id = cboxol_get_main_site_id();
	$switched     = false;
	if ( get_current_blog_id() !== $main_site_id ) {
		switch_to_blog( $main_site_id );
		$switched = true;
	}

	foreach ( $page_ids as $page_type => $page_id ) {
		$page = get_page( $page_id );
		if ( ! $page || 'page' !== $page->post_type ) {
			continue;
		}

		$pages[ $page_type ]['id']          = $page_id;
		$pages[ $page_type ]['title']       = get_the_title( $page_id );
		$pages[ $page_type ]['edit_url']    = get_admin_url( $main_site_id, 'post.php?post=' . intval( $page_id ) . '&action=edit' );
		$pages[ $page_type ]['preview_url'] = get_permalink( $page_id );
	}

	if ( $switched ) {
		restore_current_blog();
	}

	return $pages;
}

/**
 * Gets info about a specific brand page.
 *
 * @since 1.2.0
 *
 * @param string $page Name of brand page.
 * @return array Info about brand page.
 */
function cboxol_get_brand_page( $page ) {
	$all_pages = cboxol_get_brand_pages();

	if ( ! isset( $all_pages[ $page ] ) ) {
		return null;
	}

	return $all_pages[ $page ];
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

	$brand_page = cboxol_get_brand_page( $page_type );
	if ( ! $brand_page || 0 === $brand_page['id'] ) {
		return false;
	}

	return $brand_page['id'] === $post_id;
}

/**
 * Default avatar.
 *
 * openlab-theme uses a Customizer control to allow admins to set this value.
 *
 * @return string
 */
function cboxol_default_avatar( $size = 'full' ) {
	$saved = get_site_option( 'cboxol_default_avatar_' . $size );
	if ( $saved ) {
		return $saved;
	}

	$size_suffix = 'full' === $size ? '-full' : '-thumb';
	return CBOXOL_PLUGIN_URL . 'assets/img/default-avatar' . $size_suffix . '.png';
}

add_filter(
	'bp_core_avatar_full',
	function( $full ) {
		return cboxol_default_avatar( 'full' );
	}
);

add_filter(
	'bp_core_avatar_thumb',
	function( $thumb ) {
		return cboxol_default_avatar( 'thumb' );
	}
);

/**
 * Force default avatar instead of wavatar.
 *
 * @param string $url
 * @param type   $params
 * @return string
 */
function cboxol_default_get_group_avatar( $url, $params ) {
	if ( strstr( $url, 'default-avatar' ) || strstr( $url, 'wavatar' ) || strstr( $url, 'mystery-group.png' ) ) {
		$url = cboxol_default_avatar( 'full' );
	}

	return $url;
}
add_filter( 'bp_core_fetch_avatar_url', 'cboxol_default_get_group_avatar', 10, 2 );

function cboxol_default_group_avatar_img( $html ) {
	$bp_default = buddypress()->plugin_url . 'bp-core/images/mystery-group.png';
	$ol_default = cboxol_default_avatar( 'full' );
	return str_replace( $bp_default, $ol_default, $html );
}
add_filter( 'bp_core_fetch_avatar', 'cboxol_default_group_avatar_img' );

