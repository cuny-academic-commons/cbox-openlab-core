<?php

/**
 * Member types.
 */

add_action( 'init', 'cboxol_membertypes_register_post_type' );

add_action( 'add_meta_boxes', 'cboxol_membertypes_register_meta_boxes' );
add_action( 'save_post', 'cboxol_membertypes_save_labels' );

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

function cboxol_get_member_types() {
	$type_posts = get_posts( array(
		'post_type' => 'cboxol_member_type',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
	) );

	$types = array();
	foreach ( $type_posts as $type_post ) {
		$types[ $type_post->post_name ] = \CBOX\OL\MemberType::get_instance_from_wp_post( $type_post );
	}
}

function cboxol_membertypes_admin_page() {

	$types = cboxol_get_member_types();

	?>
	<div class="wrap">
		<?php cboxol_admin_header( 'member-settings', 'types' ); ?>

		<?php /* @todo */ ?>
		<p>Et officia pariatur tenetur autem. Libero illum quaerat cum iusto non. Voluptatem dignissimos et suscipit nesciunt eum nobis deleniti maiores. Dolor voluptatem qui aut maiores ut. Veritatis rerum velit aut laborum et ut ut. Aut quo nostrum assumenda dolorem quibusdam deleniti consequatur doloremque.</p>
	</div>
	<?php
}

function cboxol_membertypes_register_meta_boxes() {
	// Labels.
	add_meta_box(
		'cbox-ol-member-type-labels',
		_x( 'Labels', 'Member type labels metabox title', 'cbox-openlab-core' ),
		'cboxol_membertypes_labels_metabox',
		'cboxol_member_type',
		'advanced',
		'high'
	);
}

// @todo should be abstracted for group types
function cboxol_membertypes_labels_metabox( WP_Post $post ) {
	$type = \CBOX\OL\MemberType::get_instance_from_wp_post( $post );

	?>
	<p><?php esc_html_e( 'Provide the labels that will be used in the interface when describing members of this type.', 'cbox-openlab-core' ); ?></p>

	<table class="widefat">
	<?php foreach ( $type->get_labels() as $label_type => $label_labels ) : ?>
		<tr>
			<th>
				<label for="type-label-<?php echo esc_attr( $label_type ); ?>"><?php echo esc_html( $label_labels['label'] ); ?></label>
			</th>

			<td>
				<input type="text" id="type-label-<?php echo esc_attr( $label_type ); ?>" name="type-labels[<?php echo esc_attr( $label_type ); ?>]" value="<?php echo esc_attr( $label_labels['value'] ); ?>" />

				<?php if ( ! empty( $label_labels['description'] ) ) : ?>
					<p class="description">
						<?php echo esc_html( $label_labels['description'] ); ?>
					</p>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
	<?php

	wp_nonce_field( 'type_labels-' . $post->ID, 'type-labels-nonce', false );
}

function cboxol_membertypes_save_labels( $post_id ) {
	if ( ! isset( $_POST['type-labels-nonce'] ) || ! wp_verify_nonce( $_POST['type-labels-nonce'], 'type_labels-' . $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$labels = isset( $_POST['type-labels'] ) ? wp_unslash( $_POST['type-labels'] ) : array();
	update_post_meta( $post_id, 'cboxol_member_type_labels', $labels );
}
