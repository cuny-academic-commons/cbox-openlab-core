<?php

add_action( 'init', 'cboxol_grouptypes_register_post_type' );
add_action( 'bp_groups_register_group_types', 'cboxol_grouptypes_register_group_types' );

// Group creation. We roll our own because we skip BP's validation.
add_action( 'bp_after_group_details_creation_step', 'cboxol_grouptypes_hidden_field' );
add_action( 'groups_group_after_save', 'cboxol_grouptypes_save_group_type', 15 );

// Group creation must always have a group_type.
add_action( 'bp_actions', 'cboxol_enforce_group_type_on_creation' );

// Update nav menu items when changing the name of a group type.
add_action( 'save_post_cboxol_group_type', 'cboxol_update_nav_menu_items_on_group_type_name_change', 10, 2 );

/**
 * Get a group type by group id
 *
 * @param int $group_id
 * @return string
 */
function openlab_get_group_type( $group_id = 0 ) {
	if ( ! bp_is_active( 'groups' ) ) {
		return '';
	}

	if ( ! $group_id ) {
		$group_id = openlab_fallback_group();
	}

	$group_type = groups_get_groupmeta( $group_id, 'wds_group_type' );

	return $group_type;
}

function openlab_is_group_type( $group_id = 0, $type = 'group' ) {
	return openlab_get_group_type( $group_id ) === $type;
}

function openlab_is_course( $group_id = 0 ) {
	return openlab_is_group_type( $group_id, 'course' ); }

function openlab_is_project( $group_id = 0 ) {
	return openlab_is_group_type( $group_id, 'project' ); }

function cboxol_is_portfolio( $group_id = 0 ) {
	if ( ! $group_id ) {
		$group_id = openlab_fallback_group();
	}

	$group_type = cboxol_get_group_group_type( $group_id );

	return ! is_wp_error( $group_type ) && $group_type->get_is_portfolio();
}

function openlab_is_club( $group_id = 0 ) {
	return openlab_is_group_type( $group_id, 'club' ); }

function cboxol_grouptypes_admin_page() {
	wp_enqueue_script( 'cbox-ol-app' );

	$types = cboxol_get_group_types(
		array(
			'enabled' => null,
		)
	);

	$type_data = array();
	foreach ( $types as $type ) {
		$type_data[ $type->get_slug() ] = $type->get_for_endpoint();
	}

	$dummy      = \CBOX\OL\GroupType::get_dummy();
	$dummy_data = $dummy->get_for_endpoint();

	$app_config = array(
		'subapp'     => 'TypesUI',
		'objectType' => 'group',
		'types'      => $type_data,
		'dummy'      => $dummy_data,
	);

	?>

	<div class="cboxol-admin-content">
		<?php /* @todo */ ?>
		<p><?php esc_html_e( 'Group Types allow your site\'s groups to be categorized in various ways. Each group type gets its own directory, and groups of different types may differ in functionality and appearance.', 'commons-in-a-box' ); ?></p>

		<script type="text/javascript">
			var CBOXOL_AppConfig = <?php echo wp_json_encode( $app_config ); ?>;
		</script>

		<div id="cboxol-admin"></div>
	</div>

	<?php
}

/**
 * Register the Group Type post type.
 */
function cboxol_grouptypes_register_post_type() {
	register_post_type(
		'cboxol_group_type',
		array(
			'labels'             => array(
				'name'               => _x( 'Group Types', 'Post type general name', 'commons-in-a-box' ),
				'singular_name'      => _x( 'Group Type', 'Post type singular name', 'commons-in-a-box' ),
				'add_new_item'       => __( 'Add New Group Type', 'commons-in-a-box' ),
				'new_item'           => __( 'New Group Type', 'commons-in-a-box' ),
				'edit_item'          => __( 'Edit Group Type', 'commons-in-a-box' ),
				'view_item'          => __( 'View Group Type', 'commons-in-a-box' ),
				'all_item'           => __( 'All Group Types', 'commons-in-a-box' ),
				'search_items'       => __( 'Search Group Types', 'commons-in-a-box' ),
				'not_found'          => __( 'No group types found.', 'commons-in-a-box' ),
				'not_found_in_trash' => __( 'No group types found in Trash.', 'commons-in-a-box' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
		)
	);
}

/**
 * Register group types with BuddyPress.
 */
function cboxol_grouptypes_register_group_types() {
	$saved_types = cboxol_get_group_types();

	// @todo Conflict checking? Prefixing?
	foreach ( $saved_types as $saved_type ) {
		bp_groups_register_group_type(
			$saved_type->get_slug(),
			array(
				'labels'                => array(
					'name'          => $saved_type->get_label( 'plural' ),
					'singular_name' => $saved_type->get_label( 'singular' ),
				),
				'has_directory'         => true,
				'show_in_create_screen' => false,
				'db_id'                 => $saved_type->get_wp_term_id(),
			)
		);
	}
}

/**
 * Get a single registered Group Type.
 *
 * @param string $type Slug of the type.
 * @return \CBOX\OL\GroupType|null
 */
function cboxol_get_group_type( $slug ) {
	$types = cboxol_get_group_types(
		array(
			'enabled' => null,
		)
	);

	if ( isset( $types[ $slug ] ) ) {
		return $types[ $slug ];
	}

	return new WP_Error( 'no_group_type_found', __( 'No group type found by that slug.', 'commons-in-a-box' ), $slug );

}

/**
 * Get registered Group Types.
 *
 * @params array $args {
 *     Array of optional arguments.
 *     @type bool|null $enabled           Filter by 'enabled' status. True returns only enabled Types, false returns
 *                                        only disabled types. Null returns all.
 *     @type bool      $exclude_portfolio Whether to exclude group types where `is_portfolio` is true. Default false.
 * }
 */
function cboxol_get_group_types( $args = array() ) {
	$r = array_merge(
		array(
			'enabled'           => true,
			'exclude_portfolio' => false,
		),
		$args
	);

	$post_status = 'publish';
	if ( false === $r['enabled'] ) {
		$post_status = 'draft';
	} elseif ( null === $r['enabled'] ) {
		$post_status = 'any';
	}

	$post_args = array(
		'post_type'      => 'cboxol_group_type',
		'post_status'    => $post_status,
		'posts_per_page' => -1,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
		'fields'         => 'ids',
	);

	$switched     = false;
	$main_site_id = cboxol_get_main_site_id();
	if ( get_current_blog_id() !== $main_site_id ) {
		switch_to_blog( $main_site_id );
		$switched = true;
	}

	$last_changed = wp_cache_get_last_changed( 'posts' );
	$cache_key    = 'cboxol_types_' . md5( wp_json_encode( $post_args ) ) . '_' . $last_changed;
	$ids          = wp_cache_get( $cache_key, 'cboxol_group_types' );
	if ( false === $ids ) {
		$ids = get_posts( $post_args );
		_prime_post_caches( $ids );
		wp_cache_set( $cache_key, $ids, 'cboxol_group_types' );
	}

	$type_posts = array_map( 'get_post', $ids );

	$types = array();
	foreach ( $type_posts as $type_post ) {
		$group_type = \CBOX\OL\GroupType::get_instance_from_wp_post( $type_post );

		if ( $r['exclude_portfolio'] && $group_type->get_is_portfolio() ) {
			continue;
		}

		$types[ $type_post->post_name ] = $group_type;
	}

	if ( $switched ) {
		restore_current_blog();
	}

	return $types;
}

/**
 * Get the group type object for a given group.
 *
 * @param int $group_id ID of the group.
 * @return \CBOX\OL\GroupType|WP_Error
 */
function cboxol_get_group_group_type( $group_id ) {
	$type = bp_groups_get_group_type( $group_id );
	if ( ! $type ) {
		return new WP_Error( 'no_group_type', __( 'This group does not have a type.', 'commons-in-a-box' ), $group_id );
	}

	return cboxol_get_group_type( $type );
}

/**
 * Utility for getting group type of group currently being created or edited.
 *
 * @return WP_Error|\CBOX\OL\GroupType
 */
function cboxol_get_edited_group_group_type() {
	$the_group_id = null;
	if ( bp_is_group() ) {
		$the_group_id = bp_get_current_group_id();
	} elseif ( bp_is_group_create() ) {
		$the_group_id = bp_get_new_group_id();
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$group_type = null;
	if ( $the_group_id ) {
		$group_type = cboxol_get_group_group_type( $the_group_id );
	} elseif ( isset( $_GET['group_type'] ) ) {
		$group_type = cboxol_get_group_type( wp_unslash( urldecode( $_GET['group_type'] ) ) );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	if ( ! $group_type ) {
		return new WP_Error( 'no_group_type', __( 'No group type found.', 'commons-in-a-box' ) );
	}

	return $group_type;
}

/**
 * Hidden field for group type.
 */
function cboxol_grouptypes_hidden_field() {
	$group_type = null;

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( bp_is_group_create() && isset( $_GET['group_type'] ) ) {
		$group_type = wp_unslash( urldecode( $_GET['group_type'] ) );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	$group_type_object = cboxol_get_group_type( $group_type );
	if ( is_wp_error( $group_type_object ) ) {
		return;
	}

	printf(
		'<input type="hidden" name="group-type" value="%s" /><input type="hidden" name="group-type-nonce" value="%s" />',
		esc_attr( $group_type_object->get_slug() ),
		esc_attr( wp_create_nonce( 'cboxol_set_group_type' ) )
	);
}

/**
 * Save group type.
 */
function cboxol_grouptypes_save_group_type( $group ) {
	if ( ! isset( $_POST['group-type'] ) ) {
		return;
	}

	if ( ! isset( $_POST['group-type-nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['group-type-nonce'], 'cboxol_set_group_type' ) ) {
		return;
	}

	$group_type = cboxol_get_group_type( wp_unslash( $_POST['group-type'] ) );

	// Errors from here down should send user back to previous screen.
	if ( is_wp_error( $group_type ) ) {
		return;
	}

	// Should make this less dumb.
	if ( $group_type->get_is_course() && ! cboxol_user_can_create_courses( bp_loggedin_user_id() ) ) {
		return;
	}

	bp_groups_set_group_type( $group->id, $group_type->get_slug() );
}

/**
 * Enforce that group creation always has an allowed type.
 *
 * If no group type is found, or if the specifed group type is non-existent or
 * off-limits, redirect to the first legal one found.
 */
function cboxol_enforce_group_type_on_creation() {
	if ( ! bp_is_group_create() ) {
		return;
	}

	if ( ! bp_is_action_variable( 'group-details', 1 ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! empty( $_POST ) ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$group_type = null;
	if ( isset( $_GET['group_type'] ) ) {
		$group_type = cboxol_get_group_type( wp_unslash( urldecode( $_GET['group_type'] ) ) );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	$redirect = false;
	if ( ! $group_type || is_wp_error( $group_type ) ) {
		$redirect = true;
	} elseif ( $group_type->get_is_course() && ! cboxol_user_can_create_courses( bp_loggedin_user_id() ) ) {
		$redirect = true;
	}

	if ( ! $redirect ) {
		return;
	}

	// Grab the first non-course, non-portfolio group type.
	$types         = cboxol_get_group_types();
	$redirect_type = null;
	foreach ( $types as $type ) {
		if ( ! $type->get_is_portfolio() && ! $type->get_is_course() ) {
			$redirect_type = $type;
			break;
		}
	}

	// Sanity check.
	if ( ! $redirect_type ) {
		return;
	}

	$redirect_url = add_query_arg( 'group_type', $redirect_type->get_slug(), bp_get_groups_directory_permalink() . 'create/step/group-details/' );
	wp_safe_redirect( $redirect_url );
	die();
}

/**
 * Get the URL for the group type directory for a user.
 *
 * @param \CBOX\OL\GroupType $group_type Group type object.
 * @param int                $user_id    Optional. Defaults to displayed user.
 * @return string
 */
function cboxol_get_user_group_type_directory_url( \CBOX\OL\GroupType $group_type, $user_id = null ) {
	if ( ! $user_id ) {
		$user_id = bp_displayed_user_id();
	}

	$url = bp_members_get_user_url( $user_id, bp_members_get_path_chunks( [ bp_get_groups_slug() ] ) );
	$url = add_query_arg( 'group_type', $group_type->get_slug(), $url );

	return $url;
}

/**
 * Get the course group type.
 *
 * @return \CBOX\OL\GroupType|null Null if none is found.
 */
function cboxol_get_course_group_type() {
	$group_types = cboxol_get_group_types();
	foreach ( $group_types as $group_type ) {
		if ( $group_type->get_is_course() ) {
			return $group_type;
		}
	}

	return null;
}

/**
 * Gets a list of IDs of all group faculty/contacts.
 *
 * @param int $group_id ID of the group.
 * @return array
 */
function cboxol_get_all_group_contact_ids( $group_id ) {
	$contact_ids = groups_get_groupmeta( $group_id, 'group_contact', false );
	if ( ! $contact_ids ) {
		$contact_ids = [];
	}
	return array_map( 'intval', $contact_ids );
}

/**
 * Prevents built-in group type taxonomy terms from being edited in the UI.
 *
 * @param array  $caps    Required capabilities for this action.
 * @param string $cap     Requested cap.
 * @param int    $user_id ID of the user.
 * @param array  $args    Arguments passed to user_can().
 */
function cboxol_prevent_group_type_edit( $caps, $cap, $user_id, $args ) {
	if ( 'edit_term' !== $cap && 'delete_term' !== $cap ) {
		return $caps;
	}

	if ( empty( $args[0] ) ) {
		return $caps;
	}

	$term = get_term( $args[0], 'bp_group_type' );

	if ( ! $term ) {
		return $caps;
	}

	$cboxol_types = cboxol_get_group_types( [ 'enabled' => null ] );
	$term_slug    = $term->slug;

	if ( ! isset( $cboxol_types[ $term_slug ] ) ) {
		return $caps;
	}

	$caps = [ 'do_not_allow' ];

	return $caps;
}
add_filter( 'map_meta_cap', 'cboxol_prevent_group_type_edit', 10, 4 );

/**
 * Markup for the 'Creator(s)' metabox.
 */
function openlab_group_creators_metabox() {
	$group_id   = bp_get_current_group_id();
	$group_type = cboxol_get_group_group_type( $group_id );

	// Only for supported group types.
	if ( $group_type->get_is_portfolio() ) {
		return false;
	}

	// Ensure numeric indexes at runtime - not stored in DB.
	$c_index  = 1;
	$creators = [];
	foreach ( openlab_get_group_creators( $group_id ) as $creator ) {
		$creator['entry_id'] = 'creator' . $c_index;
		$c_index++;
		$creators[] = $creator;
	}

	$additional_text = openlab_get_group_creators_additional_text( $group_id );

	wp_enqueue_script( 'openlab-creators', CBOXOL_PLUGIN_URL . '/assets/js/creators.js', array( 'jquery-ui-autocomplete' ), cboxol_get_asset_version(), true );

	$member_icon     = '<span class="fa fa-user"></span>';
	$non_member_icon = '<span class="fa fa-globe"></span>';

	?>
	<div id="" class="panel panel-default">
		<fieldset>
			<legend class="panel-heading"><?php esc_html_e( 'Creator(s)', 'commons-in-a-box' ); ?></legend>

			<div class="panel-body">
				<p><?php esc_html_e( 'Creators will be listed in the acknowledgements on the group Profile.', 'commons-in-a-box' ); ?></p>

				<div class="group-creators-section">
					<fieldset>
						<legend><?php esc_html_e( 'Creator(s)', 'commons-in-a-box' ); ?></legend>

						<p><?php esc_html_e( 'The people listed below will be acknowledged as the group&#8217;s Creators. You can add additional creators by typing their name in the box below and selecting it from the dropdown list.', 'commons-in-a-box' ); ?></p>

						<ul id="group-creator-edit-list" class="group-creator-edit-list">
							<?php foreach ( $creators as $creator ) : ?>
								<li>
									<?php openlab_creator_form_entry( $creator ); ?>
								</li>
							<?php endforeach; ?>
						</ul>

						<div class="group-creator-edit-list-add-new">
							<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php esc_html_e( 'Add a creator:', 'commons-in-a-box' ); ?> <button type="button" id="group-creator-add-new-member"><?php echo $member_icon; ?> <?php esc_html_e( 'Select an OpenLab member', 'commons-in-a-box' ); ?></button> <button id="group-creator-add-new-non-member"><?php echo $non_member_icon; ?> <?php esc_html_e( 'Enter the name of a non-member', 'commons-in-a-box' ); ?></button>
						</div>

						<div id="group-creator-empty-row" class="group-creator-empty-row">
							<?php openlab_creator_form_entry( [] ); ?>
						</div>
					</fieldset>
				</div>

				<div class="group-creators-section">
					<label for="creators-additional-text"><?php esc_html_e( 'Additional Text', 'commons-in-a-box' ); ?></label>

					<p><?php esc_html_e( 'Below you can add additional information that will appear in the Acknowledgements section on the group Profile. It will not appear on clones of this group.', 'commons-in-a-box' ); ?></p>

					<?php

					$plugins_cb = function( $plugins ) {
						return [ 'link' ];
					};

					$buttons_cb = function( $buttons ) {
						return [ 'bold', 'italic', 'link' ];
					};

					add_filter( 'tiny_mce_plugins', $plugins_cb );
					add_filter( 'mce_buttons', $buttons_cb );
					add_filter( 'mce_buttons_2', '__return_empty_array' );

					$additional_text = wp_kses( $additional_text, openlab_creators_additional_text_allowed_tags() );

					wp_editor(
						$additional_text,
						'creators-additional-text',
						[
							'media_buttons' => false,
							'quicktags'     => false,
							'textarea_rows' => 4,
							'teeny'         => false,
						]
					);

					remove_filter( 'tiny_mce_plugins', $plugins_cb );
					remove_filter( 'mce_buttons', $buttons_cb );
					remove_filter( 'mce_buttons_2', '__return_empty_array' );
					?>
				</div>
			</div>
		</fieldset>
	</div>

	<?php wp_nonce_field( 'openlab_creators_' . $group_id, 'openlab-creators-nonce', false ); ?>

	<?php
}
add_action( 'bp_after_group_details_admin', 'openlab_group_creators_metabox', 6 );

/**
 * Creates the markup for an entry in the Creators form.
 *
 * @param array $settings Saved settings.
 */
function openlab_creator_form_entry( $settings ) {
	$r = array_merge(
		[
			'entry_id'        => '_nullcreator',
			'type'            => '',
			'member-login'    => '',
			'non-member-name' => '',
		],
		$settings
	);

	$member_display_name = '';
	$member_url          = '';
	if ( ! empty( $r['member-login'] ) ) {
		$user = get_user_by( 'slug', $r['member-login'] );
		if ( $user ) {
			$member_display_name = bp_core_get_user_displayname( $user->ID );
			$member_url          = bp_members_get_user_url( $user->ID );
		}
	}

	?>

	<div class="group-creator-form-entry">
		<div class="entry-actions">
			<button type="button" class="delete-entry">
				<span class="fa fa-minus-circle"></span>
				<span class="sr-only">Delete Entry</span>
			</button>

			<button type="button" class="edit-entry">
				<span class="fa fa-pencil"></span>
				<span class="sr-only">Edit Entry</span>
			</button>
		</div>

		<div class="creator-form-fields creator-fields-type">
			<label class="sr-only" for="<?php echo esc_attr( $r['entry_id'] ); ?>-type">Type</label>
			<select name="group-creators[<?php echo esc_attr( $r['entry_id'] ); ?>][type]" id="<?php echo esc_attr( $r['entry_id'] ); ?>-type" class="creator-type">
				<option value="" <?php selected( $r['type'], '' ); ?>>- Select Creator Type -</option>
				<option class="creator-type-member" value="member" <?php selected( $r['type'], 'member' ); ?>>OpenLab Creator</option>
				<option class="creator-type-non-member" value="non-member" <?php selected( $r['type'], 'non-member' ); ?>>Non-OpenLab Creator</option>
			</select>
		</div>

		<div class="creator-form-fields creator-fields-member-login">
			<label class="sr-only" for="<?php echo esc_attr( $r['entry_id'] ); ?>-member-login]">Member Username</label>
			<input type="text" id="<?php echo esc_attr( $r['entry_id'] ); ?>-member-login" class="member-login-autocomplete member-login" name="group-creators[<?php echo esc_attr( $r['entry_id'] ); ?>][member-login]" value="<?php echo esc_attr( $r['member-login'] ); ?>" placeholder="Start typing to search OpenLab members" />
			<input type="hidden" class="member-display-name" value="<?php echo esc_attr( $member_display_name ); ?>" />
			<input type="hidden" class="member-url" value="<?php echo esc_attr( $member_url ); ?>" />
		</div>

		<div class="creator-form-fields creator-fields-non-member-name">
			<label class="sr-only" for="<?php echo esc_attr( $r['entry_id'] ); ?>-non-member-name]">Non-Member Name</label>
			<input type="text" class="non-member-name" id="<?php echo esc_attr( $r['entry_id'] ); ?>-non-member-name" class="non-member-name" name="group-creators[<?php echo esc_attr( $r['entry_id'] ); ?>][non-member-name]" value="<?php echo esc_attr( $r['non-member-name'] ); ?>" placeholder="Creator's name" />
		</div>
	</div>
	<?php
}

/**
 * Process the saving of Creators data for a group.
 */
function openlab_group_creators_save( $group ) {
	$nonce    = '';
	$group_id = $group->id;

	if ( ! isset( $_POST['openlab-creators-nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['openlab-creators-nonce'], 'openlab_creators_' . $group_id ) ) {
		return;
	}

	// Admins only.
	if ( ! current_user_can( 'bp_moderate' ) && ! groups_is_user_admin( bp_loggedin_user_id(), $group_id ) ) {
		return;
	}

	$creators_to_save = array_filter(
		$_POST['group-creators'],
		function( $creator, $index ) {
			if ( '_nullcreator' === $index ) {
				return false;
			}

			// Discard empty entries.
			if ( empty( $creator['type'] ) ) {
				return false;
			}

			return true;
		},
		ARRAY_FILTER_USE_BOTH
	);

	openlab_save_group_creators( $group_id, $creators_to_save );

	if ( isset( $_POST['creators-additional-text'] ) ) {
		$additional_text = wp_kses( $_POST['creators-additional-text'], openlab_creators_additional_text_allowed_tags() );

		groups_update_groupmeta( $group_id, 'additional_acknowledgements_text', $additional_text );
	}
}
add_action( 'groups_group_after_save', 'openlab_group_creators_save' );

/**
 * Saves a group's Creators.
 *
 * @param int   $group_id
 * @param array $creators
 */
function openlab_save_group_creators( $group_id, $creators ) {
	// Delete existing and replace.
	groups_delete_groupmeta( $group_id, 'group_creator' );

	foreach ( $creators as $creator ) {
		groups_add_groupmeta( $group_id, 'group_creator', $creator );
	}
}

/**
 * Gets a list of allowed HTML tags for 'Additional Text'.
 *
 * Limited to 'a' and its attributes, as well as 'strong' and 'em'.
 *
 * @return array
 */
function openlab_creators_additional_text_allowed_tags() {
	return [
		'a'      => [
			'href'   => [],
			'rel'    => [],
			'title'  => [],
			'target' => [],
		],
		'em'     => [],
		'strong' => [],
		'p'      => [],
	];
}

/**
 * Gets a list of a group's Creators.
 *
 * @param int $group_id
 * @return array
 */
function openlab_get_group_creators( $group_id ) {
	return groups_get_groupmeta( $group_id, 'group_creator', false );
}

/**
 * Gets the 'Additional Text' field for the Acknowledgements section.
 *
 * @param int $group_id
 * @return string
 */
function openlab_get_group_creators_additional_text( $group_id ) {
	return groups_get_groupmeta( $group_id, 'additional_acknowledgements_text', true );
}

/**
 * AJAX handler for Creator autocomplete.
 */
function openlab_group_creator_autocomplete_cb() {
	global $wpdb;

	$term = '';

	// phpcs:disable WordPress.Security.NonceVerification
	if ( isset( $_GET['term'] ) ) {
		$term = urldecode( $_GET['term'] );
	}
	// phpcs:enable WordPress.Security.NonceVerification

	// Direct query for speed.
	$like = '%' . $wpdb->esc_like( $term ) . '%';

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$found = $wpdb->get_results( $wpdb->prepare( "SELECT u.ID, u.display_name, u.user_nicename FROM $wpdb->users u WHERE ( u.display_name LIKE %s OR u.user_nicename LIKE %s )", $like, $like ) );

	$retval = array();
	foreach ( (array) $found as $u ) {
		$retval[] = array(
			'label' => sprintf( '%s (%s)', esc_html( $u->display_name ), esc_html( $u->user_nicename ) ),
			'value' => esc_attr( $u->user_nicename ),
			'url'   => bp_members_get_user_url( $u->ID ),
		);
	}

	echo wp_json_encode( $retval );
	die();
}
add_action( 'wp_ajax_openlab_group_creator_autocomplete', 'openlab_group_creator_autocomplete_cb' );

/**
 * Saves the default group Creators on creation.
 *
 * @param int $group_id
 */
function openlab_save_group_creators_on_creation() {
	$group_id = buddypress()->groups->new_group_id;

	$creators = array_map(
		function( $contact_id ) {
			$username = bp_core_get_username( $contact_id );
			if ( ! $username ) {
				return null;
			}

			return [
				'type'         => 'member',
				'member-login' => $username,
			];
		},
		cboxol_get_all_group_contact_ids( $group_id )
	);

	openlab_save_group_creators( $group_id, array_filter( $creators ) );
}
add_action( 'groups_create_group_step_save_site-details', 'openlab_save_group_creators_on_creation' );

/**
 * Updates the nav menu title corresponding to a group type whose name has just been changed.
 *
 * @since 1.5.0
 *
 * @param int     $post_id ID of the post.
 * @param WP_Post $post    Post object.
 */
function cboxol_update_nav_menu_items_on_group_type_name_change( $post_id, $post ) {
	$main_nav_menu = wp_get_nav_menu_object( __( 'Main Menu', 'commons-in-a-box' ) );
	if ( ! $main_nav_menu ) {
		return;
	}

	$group_type = \CBOX\OL\GroupType::get_instance_from_wp_post( $post );

	$group_type_directory_uri = bp_get_group_type_directory_permalink( $group_type->get_slug() );

	$main_nav_items = wp_get_nav_menu_items( $main_nav_menu->term_id );
	foreach ( $main_nav_items as $main_nav_item ) {
		if ( $group_type_directory_uri !== $main_nav_item->url ) {
			continue;
		}

		if ( $group_type->get_name() === $main_nav_item->title ) {
			continue;
		}

		$menu_item = wp_setup_nav_menu_item( $main_nav_item );

		$new_data = [
			'menu-item-title'       => $group_type->get_name(),
			'menu-item-db-id'       => $main_nav_item->ID,
			'menu-item-object-id'   => $menu_item->object_id,
			'menu-item-object'      => $menu_item->object,
			'menu-item-parent-id'   => $menu_item->menu_item_parent,
			'menu-item-position'    => $menu_item->menu_order,
			'menu-item-type'        => $menu_item->type,
			'menu-item-url'         => $menu_item->url,
			'menu-item-description' => $menu_item->description,
			'menu-item-attr-title'  => $menu_item->attr_title,
			'menu-item-target'      => $menu_item->target,
			'menu-item-classes'     => implode( ' ', $menu_item->classes ),
			'menu-item-xfn'         => $menu_item->xfn,
			'menu-item-status'      => $menu_item->post_status,
		];

		wp_update_nav_menu_item( $main_nav_menu->term_id, $main_nav_item->ID, $new_data );
	}
}
