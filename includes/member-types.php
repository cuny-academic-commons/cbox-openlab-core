<?php

/**
 * Member types.
 */

add_action( 'init', 'cboxol_membertypes_register_post_type' );

function cboxol_membertypes_register_post_type() {
	register_post_type( 'cboxol_member_type', array(
		'labels' => array(
			'name' => _x( 'Member Types', 'Post type general name', 'cbox-openlab-core' ),
			'singular_name' => _x( 'Member Type', 'Post type singular name', 'cbox-openlab-core' ),
			'add_new_item' => __( 'Add New Member Type', 'cbox-openlab-core' ),
			'new_item' => __( 'New Member Type', 'cbox-openlab-core' ),
			'edit_item' => __( 'Edit Member Type', 'cbox-openlab-core' ),
			'view_item' => __( 'View Member Type', 'cbox-openlab-core' ),
			'all_item' => __( 'All Member Types', 'cbox-openlab-core' ),
			'search_items' => __( 'Search Member Types', 'cbox-openlab-core' ),
			'not_found' => __( 'No member types found.', 'cbox-openlab-core' ),
			'not_found_in_trash' => __( 'No member types found in Trash.', 'cbox-openlab-core' ),
		),
		'public' => false,
		'publicly_queryable' => false,
		'show_ui' => true,
		'show_in_menu' => false,
	) );
}

function cboxol_membertypes_admin_page() {

	?>
	<div class="wrap">
		<?php cboxol_admin_header( 'member-settings', 'types' ); ?>
	</div>
	<?php
}
