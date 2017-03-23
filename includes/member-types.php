<?php

/**
 * Member types.
 */

add_action( 'init', 'cboxol_membertypes_register_post_type' );
add_action( 'bp_register_member_types', 'cboxol_membertypes_register_member_types' );

add_action( 'add_meta_boxes', 'cboxol_membertypes_register_meta_boxes' );
add_action( 'save_post', 'cboxol_membertypes_save_labels' );
add_action( 'save_post', 'cboxol_membertypes_save_settings' );

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

/**
 * Register member types with BuddyPress.
 */
function cboxol_membertypes_register_member_types() {
	$saved_types = cboxol_get_member_types();

	// @todo Conflict checking? Prefixing?
	foreach ( $saved_types as $saved_type ) {
		bp_register_member_type( $saved_type->get_slug(), array(
			'labels' => array(
				'name' => $saved_type->get_label( 'plural' ),
				'singular_name' => $saved_type->get_label( 'singular' ),
			),
			'has_directory' => true,
		) );
	}
}

/**
 * Get a single registered Member Type.
 *
 * @param string $type Slug of the type.
 * @return \CBOX\OL\MemberType|null
 */
function cboxol_get_member_type( $slug ) {
	$types = cboxol_get_member_types( array(
		'enabled' => null,
	) );

	if ( isset( $types[ $slug ] ) ) {
		return $types[ $slug ];
	}

	return null;
}

/**
 * Get registered Member Types.
 *
 * @params array $args {
 *     Array of optional arguments.
 *     @type bool|null $enabled Filter by 'enabled' status. True returns only enabled Types, false returns
 *                              only disabled types. Null returns all.
 * }
 */
function cboxol_get_member_types( $args = array() ) {
	$r = array_merge( array(
		'enabled' => true,
	), $args );

	$post_status = 'publish';
	if ( false === $r['enabled'] ) {
		$post_status = 'draft';
	} elseif ( null === $r['enabled'] ) {
		$post_status = 'any';
	}

	$type_posts = get_posts( array(
		'post_type' => 'cboxol_member_type',
		'post_status' => 'any',
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
	) );

	$types = array();
	foreach ( $type_posts as $type_post ) {
		$types[ $type_post->post_name ] = \CBOX\OL\MemberType::get_instance_from_wp_post( $type_post );
	}

	return $types;
}

function cboxol_membertypes_admin_page() {

	$types = cboxol_get_member_types( array(
		'enabled' => null,
	) );

	?>
	<div class="wrap">
		<?php cboxol_admin_header( 'member-settings', 'types' ); ?>

		<?php /* @todo */ ?>
		<p>Member Types are et officia pariatur tenetur autem. Libero illum quaerat cum iusto non. Voluptatem dignissimos et suscipit nesciunt eum nobis deleniti maiores. Dolor voluptatem qui aut maiores ut. Veritatis rerum velit aut laborum et ut ut. Aut quo nostrum assumenda dolorem quibusdam deleniti consequatur doloremque.</p>

		<form method="post" action="">
			<ul class="cboxol-types-admin">
			<?php foreach ( $types as $type ) : ?>
				<li>
					<input type="checkbox" id="enabled-types-<?php echo esc_attr( $type->get_slug() ); ?>" name="enabled-types[]" value="<?php echo $type->get_slug(); ?>" class="enabled-type-checkbox" <?php checked( $type->get_is_enabled() ); ?> />
					<div class="type-content">
						<div class="type-header">
							<label for="enabled-types-<?php echo esc_attr( $type->get_slug() ); ?>"><?php echo esc_html( $type->get_name() ); ?></label> <a class="type-edit-link" href="<?php echo esc_url( get_edit_post_link( $type->get_wp_post_id() ) ); ?>"><?php echo esc_html( _x( 'Edit', 'Edit link for member/group type', 'cbox-openlab' ) ); ?></a>
						</div>

						<?php if ( $description = $type->get_description() ) : ?>
							<div class="type-description">
								<?php echo wpautop( $description ); ?>
							</div>
						<?php endif; ?>

						<table class="widefat cboxol-metabox-table">
							<?php /* @todo needs a Courses check */ ?>
							<tr>
								<th scope="row">
									<?php esc_html_e( 'Member may create Courses', 'cbox-openlab' ); ?>
								</th>

								<td>
									<strong><?php echo esc_attr( $type->get_can_create_courses() ? __( 'Yes', 'cbox-openlab' ) : __( 'No', 'cbox-openlab' ) ); ?></strong>
								</td>
							</tr>

							<tr>
								<th scope="row">
									<?php esc_html_e( 'Member may change Type to', 'cbox-openlab' ); ?>
								</th>

								<td>
									<strong><?php echo esc_html( $type->get_selectable_types_list() ); ?></strong>
								</td>
							</tr>
						</table>
					</div>
				</li>
			<?php endforeach; ?>
			</ul>

			<?php wp_nonce_field( 'types_enable', 'types-enable-nonce', false ); ?>
			<?php submit_button( 'Save Changes' ); ?>
		</form>
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

	// Settings.
	add_meta_box(
		'cbox-ol-member-type-settings',
		_x( 'Settings', 'Member type settings metabox title', 'cbox-openlab-core' ),
		'cboxol_membertypes_settings_metabox',
		'cboxol_member_type',
		'advanced'
	);
}

function cboxol_membertypes_process_form_submit() {
	if ( ! current_user_can( 'manage_network_settings' ) ) {
		return;
	}

	if ( ! isset( $_POST['types-enable-nonce'] ) || ! wp_verify_nonce( $_POST['types-enable-nonce'], 'types_enable' ) ) {
		return;
	}

	$enabled_types = array();
	if ( isset( $_POST['enabled-types'] ) ) {
		$enabled_types = wp_unslash( $_POST['enabled-types'] );
	}

	$all_types = cboxol_get_member_types( array(
		'enabled' => null,
	) );

	foreach ( $all_types as $type ) {
		if ( $type->get_is_enabled() && ! in_array( $type->get_slug(), $enabled_types, true ) ) {
			wp_update_post( array(
				'ID' => $type->get_wp_post_id(),
				'post_status' => 'draft',
			) );
		} elseif ( ! $type->get_is_enabled() && in_array( $type->get_slug(), $enabled_types, true ) ) {
			wp_update_post( array(
				'ID' => $type->get_wp_post_id(),
				'post_status' => 'publish',
			) );
		}
	}
}

// @todo should be abstracted for group types
function cboxol_membertypes_labels_metabox( WP_Post $post ) {
	$type = \CBOX\OL\MemberType::get_instance_from_wp_post( $post );

	?>
	<p><?php esc_html_e( 'Provide the labels that will be used in the interface when describing members of this type.', 'cbox-openlab-core' ); ?></p>

	<table class="widefat cboxol-metabox-table">
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

function cboxol_membertypes_settings_metabox( WP_Post $post ) {
	$type = \CBOX\OL\MemberType::get_instance_from_wp_post( $post );

	$can_create_courses = $type->get_can_create_courses();

	// Exclude current type from list. @todo add exclude as option in cboxol_get_member_types()?
	$member_types_raw = cboxol_get_member_types();
	$member_types = array();
	foreach ( $member_types_raw as $member_type_slug => $member_type_raw ) {
		if ( $type->get_slug() !== $member_type_raw->get_slug() ) {
			$member_types[ $member_type_slug ] = $member_type_raw;
		}
	}
	$can_change_to = $type->get_selectable_types();

	wp_enqueue_style( 'cbox-ol-admin' );

	?>

	<table class="widefat cboxol-metabox-table">
		<?php /* @todo When Group Types are in place, check for Courses */ ?>
		<fieldset>
			<tr>
				<th scope="row">
					<legend><?php esc_html_e( 'Member may create courses', 'cbox-openlab-core' ); ?></legend>
				</th>

				<td>
					<label for="member-type-can-create-courses-yes"><input type="radio" name="member-type-can-create-courses" value="yes" id="member-type-can-create-courses-yes" <?php checked( $can_create_courses ); ?> /> <?php esc_html_e( 'Yes', 'cbox-openlab-core' ); ?> &nbsp; <label for="member-type-can-create-courses-no"><input type="radio" name="member-type-can-create-courses" value="no" id="member-type-can-create-courses-no" <?php checked( ! $can_create_courses ); ?> /><?php esc_html_e( 'No', 'cbox-openlab-core' ); ?></label>
				</td>
			</tr>
		</fieldset>

		<tr>
			<th scope="row">
				<label for="member-type-can-change-to"><?php esc_html_e( 'Member may change Type to', 'cbox-openlab-core' ); ?></legend>
			</th>

			<td>
				<ul>
				<?php foreach ( $member_types as $member_type ) : ?>
					<li>
						<input type="checkbox" id="member-type-can-change-to-<?php echo esc_attr( $member_type->get_slug() ); ?>" name="member-type-can-change-to[]" value="<?php echo esc_attr( $member_type->get_slug() ); ?>" <?php checked( in_array( $member_type->get_slug(), $can_change_to, true ) ); ?> /> <label for="member-type-can-change-to-<?php echo esc_attr( $member_type->get_slug() ); ?>"><?php echo esc_html( $member_type->get_name() ); ?></label>
					</li>
				<?php endforeach; ?>
				</ul>
			</td>
		</tr>
	</table>
	<?php

	wp_nonce_field( 'type_settings-' . $post->ID, 'type-settings-nonce', false );
}

function cboxol_membertypes_save_settings( $post_id ) {
	if ( ! isset( $_POST['type-settings-nonce'] ) || ! wp_verify_nonce( $_POST['type-settings-nonce'], 'type_settings-' . $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$can_create_courses = 'yes' === isset( $_POST['member-type-can-create-courses'] ) && $_POST['member-type-can-create-courses'] ? 'yes' : 'no';
	update_post_meta( $post_id, 'cboxol_member_type_can_create_courses', $can_create_courses );

	// @todo validate
	$can_change_to = array();
	if ( ! empty( $_POST['member-type-can-change-to'] ) ) {
		foreach ( $_POST['member-type-can-change-to'] as $change_to_type ) {
			$can_change_to[] = wp_unslash( $change_to_type );
		}
	}
	update_post_meta( $post_id, 'cboxol_member_type_selectable_types', $can_change_to );
}

/**
 * Get the (singular) label corresponding to a user's member type.
 *
 * @param int $user_id
 * @return string
 */
function cboxol_get_user_member_type_label( $user_id ) {
	$label = '';
	$member_type = bp_get_member_type( $user_id );
	if ( $member_type ) {
		$member_type_obj = bp_get_member_type_object( $member_type );
		if ( $member_type_obj ) {
			$label = $member_type_obj->labels['singular_name'];
		}
	}

	return $label;
}

/**
 * Get a list of selectable member types for a given user.
 *
 * @param int $user_id
 * @return array
 */
function cboxol_get_selectable_member_types_for_user( $user_id ) {
	$selectable_types = array();

	$type = bp_get_member_type( $user_id );
	if ( $type ) {
		$type_obj = cboxol_get_member_type( $type );
		if ( $type_obj ) {
			$selectable_types = $type_obj->get_selectable_types();
		}
	}

	return $selectable_types;
}
