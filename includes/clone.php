<?php

/**
 * Functionality related to cloning.
 *
 * @since 1.2.0
 */

/**
 * Loads cloning widgets.
 *
 * @since 1.2.0
 */
function cboxol_register_clone_widgets() {
	$widgets = [
		'\CBOX\OL\Widget\CloneCredits',
		'\CBOX\OL\Widget\ShareableContent',
	];

	foreach ( $widgets as $widget ) {
		register_widget( $widget );
	}
}
add_action( 'widgets_init', 'cboxol_register_clone_widgets' );

function cboxol_unregister_clone_widgets() {
	$group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );
	if ( $group_id ) {
		return;
	}

	$widgets = [
		'\CBOX\OL\Widget\CloneCredits',
		'\CBOX\OL\Widget\ShareableContent',
	];

	foreach ( $widgets as $widget ) {
		unregister_widget( $widget );
	}
}
add_action( 'bp_setup_globals', 'cboxol_unregister_clone_widgets' );

/**
 * Get the clone history of a group.
 *
 * Returns an array of group IDs, ordered from oldest to newest.
 *
 * @since 1.2.0
 *
 * @param int $group_id ID of the group.
 * @return array
 */
function openlab_get_group_clone_history( $group_id ) {
	$history = [];

	$clone_source_group_id = groups_get_groupmeta( $group_id, 'clone_source_group_id', true );
	if ( ! $clone_source_group_id ) {
		return $history;
	}

	$history[] = $clone_source_group_id;

	$source_history = openlab_get_group_clone_history( $clone_source_group_id );

	$history = array_merge( $source_history, $history );

	return array_map( 'intval', $history );
}

/**
 * Gets all clones of a group.
 *
 * Returns only direct children.
 *
 * @param int $group_id ID of the parent group.
 * @return array Array of IDs.
 */
function openlab_get_clones_of_group( $group_id ) {
	global $wpdb, $bp;

	$clone_ids = wp_cache_get( $group_id, 'openlab_clones_of_group' );
	if ( false === $clone_ids ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$clone_ids = $wpdb->get_col( $wpdb->prepare( "SELECT group_id FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'clone_source_group_id' AND meta_value = %s", $group_id ) );

		wp_cache_set( $group_id, $clone_ids, 'openlab_clones_of_group' );
	}

	return array_map( 'intval', $clone_ids );
}

/**
 * Returns all clone descendants of a group.
 *
 * @param int   $group_id            ID of the group.
 * @param array $exclude_creator_ids Exclude groups created by these users.
 * @param bool  $exclude_hidden      Whether to exclude hidden groups.
 * @return array Array of IDs.
 */
function openlab_get_clone_descendants_of_group( $group_id, $exclude_creator_ids = [], $exclude_hidden = false ) {
	$descendants = openlab_get_clones_of_group( $group_id );
	if ( ! $descendants ) {
		return [];
	}

	foreach ( $descendants as $descendant ) {
		$descendants = array_merge( $descendants, openlab_get_clone_descendants_of_group( $descendant, $exclude_creator_ids, $exclude_hidden ) );
	}

	if ( $exclude_creator_ids ) {
		$descendants = array_filter(
			$descendants,
			function( $descendant_id ) use ( $exclude_creator_ids ) {
				$descendant = groups_get_group( $descendant_id );
				return ! in_array( $descendant->creator_id, $exclude_creator_ids, true );
			}
		);
	}

	if ( $exclude_hidden ) {
		$descendants = array_filter(
			$descendants,
			function( $descendant_id ) {
				$descendant = groups_get_group( $descendant_id );
				return 'hidden' !== $descendant->status;
			}
		);
	}

	return $descendants;
}

/**
 * Returns clone descendants count of a group.
 *
 * @param int  $group_id       ID of the group.
 * @param bool $exclude_hidden Whether to exclude hidden groups from the count.
 * @return int
 */
function openlab_get_clone_descendant_count_of_group( $group_id, $exclude_hidden = false ) {
	$group = groups_get_group( $group_id );

	$descendants = openlab_get_clone_descendants_of_group( $group_id, [ $group->creator_id ], $exclude_hidden );

	return count( $descendants );
}

/**
 * Busts the cache of ancestor clone caches.
 */
function openlab_invalidate_ancestor_clone_cache( $group_id ) {
	$ancestor_ids = openlab_get_group_clone_history( $group_id );
	foreach ( $ancestor_ids as $ancestor_id ) {
		wp_cache_delete( $ancestor_id, 'openlab_clones_of_group' );
	}
}

/**
 * Ensures that the cache of ancestor clones is invalidated on group deletion.
 */
add_action( 'groups_before_delete_group', 'openlab_invalidate_ancestor_clone_cache' );

/**
 * Get the clone historty of a group.
 *
 * @param int $group_id        ID for the group.
 * @param int $exclude_creator Whether to exclude groups created by the specified user.
 *                             These groups are trimmed only from the end of the ancestry chain.
 *                             Default true.
 * @return array $history The clone history.
 */
function openlab_get_group_clone_history_data( $group_id, $exclude_creator = null ) {
	$source_ids = openlab_get_group_clone_history( $group_id );

	$source_datas = array();
	foreach ( $source_ids as $source_id ) {
		$source_datas[] = openlab_get_group_data_for_clone_history( $source_id );
	}

	// Trim exclude_creator groups.
	if ( $source_datas && null !== $exclude_creator ) {
		$exclude_creator = intval( $exclude_creator );
		$source_count    = count( $source_datas );
		for ( $i = 0; $i < $source_count; $i++ ) {
			if ( $source_datas[ $i ]['group_creator_id'] !== $exclude_creator ) {
				continue;
			}

			unset( $source_datas[ $i ] );
		}
	}

	return $source_datas;
}

/**
 * Gets the formatted data for a group, for use in clone history.
 *
 * @since 1.3.0
 *
 * @param int $source_id
 * @return array
 */
function openlab_get_group_data_for_clone_history( $source_id ) {
	$source_group = groups_get_group( $source_id );

	$course_code = groups_get_groupmeta( $source_id, 'cboxol_course_code' );
	$group_type  = cboxol_get_group_group_type( $source_id );

	$group_creators = openlab_get_group_creators( $source_id );

	$admins = [];
	foreach ( $group_creators as $group_creator ) {
		switch ( $group_creator['type'] ) {
			case 'member':
				$user = get_user_by( 'slug', $group_creator['member-login'] );

				if ( $user ) {
					$admins[] = [
						'name' => bp_core_get_user_displayname( $user->ID ),
						'url'  => bp_core_get_user_domain( $user->ID ),
					];
				}
				break;

			case 'non-member':
				$admins[] = [
					'name' => $group_creator['non-member-name'],
					'url'  => '',
				];
				break;
		}
	};

	$group_type_label = ! is_wp_error( $group_type ) ? $group_type->get_label( 'singular' ) : '';

	$source_data = array(
		'group_id'           => $source_id,
		'group_url'          => bp_get_group_permalink( $source_group ),
		'group_name'         => $course_code ? $course_code : $group_type_label,
		'group_admins'       => $admins,
		'group_creator_id'   => $source_group->creator_id,
		'group_creator_name' => bp_core_get_user_displayname( $source_group->creator_id ),
		'group_creator_url'  => bp_core_get_user_domain( $source_group->creator_id ),
	);

	return $source_data;
}

/**
 * Formats the clone history as unordered list items of structured links.
 *
 * Note that you need to provide the <ul> wrapper yourself.
 *
 * @since 1.3.0
 */
function openlab_format_group_clone_history_data_list( $history ) {
	$credits_groups = array_map(
		function( $clone_group ) {
			$admin_names = array_map(
				function( $admin ) {
					if ( ! empty( $admin['url'] ) ) {
						return sprintf(
							'<a href="%s">%s</a>',
							esc_attr( $admin['url'] ),
							esc_html( $admin['name'] )
						);
					} else {
						return $admin['name'];
					}
				},
				$clone_group['group_admins']
			);

			return sprintf(
				'<li><a href="%s">%s</a> by %s</li>',
				esc_attr( $clone_group['group_url'] ),
				esc_html( $clone_group['group_name'] ),
				implode( ', ', $admin_names )
			);
		},
		$history
	);

	return implode( "\n", $credits_groups );
}

/**
 * Gets a text-based list of links to clone history.
 *
 * @since 1.2.0
 *
 * @param int $group_id Group ID.
 * @return string
 */
function openlab_get_group_clone_history_list( $group_id, $exclude_creator = null ) {
	$list = '';

	$history_data = openlab_get_group_clone_history_data( $group_id, $exclude_creator );
	if ( ! $history_data ) {
		return $list;
	}

	$lis = openlab_format_group_clone_history_data_list( $history_data );

	return '<ul class="group-credits">' . $lis . '</ul>';
}

/**
 * Get the group ID of a group's clone source.
 *
 * @param int $group_id
 * @return int $group_id
 */
function cboxol_get_clone_source_group_id( $group_id ) {
	return (int) groups_get_groupmeta( $group_id, 'clone_source_group_id' );
}

/**
 * Determines whether a group can be cloned.
 *
 * @param int $group_id The group ID.
 * @param bool
 */
function openlab_group_can_be_cloned( $group_id = null ) {
	if ( null === $group_id ) {
		$group_id = bp_get_current_group_id();
	}

	if ( ! $group_id ) {
		return false;
	}

	$sharing_enabled_for_group = groups_get_groupmeta( $group_id, 'enable_sharing', true );

	return ! empty( $sharing_enabled_for_group );
}

/**
 * Determines whether a current user can clone the group.
 *
 * @param int $group_id The group id.
 * @return bool
 */
function openlab_user_can_clone_group( $group_id ) {
	$group_type = cboxol_get_group_group_type( $group_id );

	if ( is_wp_error( $group_type ) ) {
		return false;
	}

	if ( is_super_admin() ) {
		return true;
	}

	$user_id       = get_current_user_id();
	$can_be_cloned = $group_type->get_can_be_cloned();

	if ( groups_is_user_admin( $user_id, $group_id ) ) {
		return $can_be_cloned;
	}

	$member_type = cboxol_get_user_member_type( $user_id );

	if ( is_wp_error( $member_type ) ) {
		return false;
	}

	$sharing_enabled = openlab_group_can_be_cloned( $group_id );

	if ( $group_type->get_is_course() && $sharing_enabled ) {
		return $member_type->get_can_create_courses();
	}

	if ( $sharing_enabled ) {
		return $can_be_cloned;
	}

	return false;
}

/**
 * Flushes rewrite rules on newly created sites.
 *
 * @since 1.2.4
 */
function cboxol_flush_rewrite_rules_on_newly_created_sites() {
	if ( bp_is_root_blog() ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( get_option( 'cboxol_initial_rewrite_flush' ) ) {
		return;
	}

	update_option( 'cboxol_initial_rewrite_flush', time() );

	flush_rewrite_rules( false );
}
add_action( 'shutdown', 'cboxol_flush_rewrite_rules_on_newly_created_sites' );

/**
 * Gets the Credits data to be used in the Acknowledgements section.
 */
function openlab_get_credits( $group_id ) {
	$post_credits_markup = '';

	$all_group_contacts = cboxol_get_all_group_contact_ids( $group_id );
	if ( count( $all_group_contacts ) <= 1 ) {
		$exclude_creator = $all_group_contacts[0];
	} else {
		$exclude_creator = null;
	}

	// Remove items that have been deleted, or have incomplete values.
	$clone_history = openlab_get_group_clone_history_data( $group_id );

	// Remove items that exactly match the credits of the current group.
	$this_item_clone_data = openlab_get_group_data_for_clone_history( $group_id );

	$clone_history = array_filter(
		$clone_history,
		function( $item ) use ( $this_item_clone_data ) {
			$admins_a = $item['group_admins'];
			$admins_b = $this_item_clone_data['group_admins'];

			// Exclude groups with no Creators.
			if ( empty( $admins_a ) ) {
				return false;
			}

			$sort_cb = function( $a, $b ) {
				return $a > $b ? 1 : -1;
			};

			usort( $admins_a, $sort_cb );
			usort( $admins_b, $sort_cb );

			return $admins_a !== $admins_b;
		}
	);

	$has_non_member_creator = false;

	$group_creators = openlab_get_group_creators( $group_id );
	foreach ( $group_creators as $group_creator ) {
		if ( 'non-member' === $group_creator['type'] ) {
			$has_non_member_creator = true;
			break;
		}
	}

	$contact_creator_mismatch = false;
	if ( ! $has_non_member_creator ) {
		$creator_ids = array_map(
			function( $creator ) {
				$user = get_user_by( 'slug', $creator['member-login'] );
				return $user->ID;
			},
			$group_creators
		);

		sort( $creator_ids );
		sort( $all_group_contacts );

		$contact_creator_mismatch = $creator_ids !== $all_group_contacts;
	}
	$credits_chunks = [];

	$additional_text = openlab_get_group_creators_additional_text( $group_id );

	/*
	 * Non-clones show Acknowledgements only if Creators differ from Contacts,
	 * or if there is Additional Text to show.
	 */
	$show_acknowledgements = false;
	if ( ! $clone_history || $has_non_member_creator || $contact_creator_mismatch ) {
		$credits_markup = '';

		if ( $has_non_member_creator || $contact_creator_mismatch ) {
			$creator_items = array_map(
				function( $creator ) {
					switch ( $creator['type'] ) {
						case 'member':
							$user = get_user_by( 'slug', $creator['member-login'] );

							if ( ! $user ) {
								return null;
							}

							return sprintf(
								'<a href="%s">%s</a>',
								esc_attr( bp_core_get_user_domain( $user->ID ) ),
								esc_html( bp_core_get_user_displayname( $user->ID ) )
							);

						case 'non-member':
							return esc_html( $creator['non-member-name'] );
					}
				},
				$group_creators
			);

			$creator_items = array_filter( $creator_items );

			if ( $creator_items ) {
				$show_acknowledgements = true;

				$credits_intro_text = __( 'Acknowledgements: Created by:', 'commons-in-a-box' );
				$credits_markup     = implode( ', ', $creator_items );

				$credits_chunks[] = [
					'intro' => $credits_intro_text,
					'items' => $credits_markup,
				];
			}

			if ( $clone_history ) {
				$clone_intro_text = __( 'It is based on the following:', 'commons-in-a-box' );

				$credits_chunks[] = [
					'intro' => $clone_intro_text,
					'items' => openlab_format_group_clone_history_data_list( $clone_history ),
				];
			}
		}
	} else {
		$credits_markup     = openlab_format_group_clone_history_data_list( $clone_history );
		$credits_intro_text = __( 'Acknowledgements: Based on the following:', 'commons-in-a-box' );

		$credits_chunks[] = [
			'intro' => $credits_intro_text,
			'items' => $credits_markup,
		];

		$show_acknowledgements = true;
	}

	if ( $additional_text ) {
		$show_acknowledgements = true;
		if ( $credits_chunks ) {
			$post_credits_markup = '<p>' . wp_kses( $additional_text, openlab_creators_additional_text_allowed_tags() ) . '</p>';
		} else {
			$credits_intro_text = sprintf(
				// translators: Acknowledgements text.
				__( 'Acknowledgements: %s', 'commons-in-a-box' ),
				wp_kses( $additional_text, openlab_creators_additional_text_allowed_tags() )
			);

			$credits_chunks[] = [
				'intro' => $credits_intro_text,
				'items' => '',
			];
		}
	}

	$retval = [
		'show_acknowledgements' => $show_acknowledgements,
		'credits_chunks'        => $credits_chunks,
		'post_credits_markup'   => $post_credits_markup,
	];

	return $retval;
}
