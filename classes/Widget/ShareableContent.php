<?php

namespace CBOX\OL\Widget;

use \WP_Widget;

class ShareableContent extends WP_Widget {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'openlab_shareable_content_widget',
			__( 'Sharing', 'commons-in-a-box' ),
			array(
				'description' => '',
			)
		);
	}

	/**
	 * Outputs the widget content.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// Don't show if the user can't clone.
		$group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );

		$group_type = cboxol_get_group_group_type( $group_id );

		$clone_link = add_query_arg(
			array(
				'group_type' => $group_type->get_slug(),
				'clone'      => $group_id,
			),
			bp_get_groups_directory_permalink() . 'create/step/group-details/'
		);

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['before_widget'];

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['before_title'] . esc_html__( 'Sharing', 'commons-in-a-box' ) . $args['after_title'];

		$can_clone = false;
		if ( is_user_logged_in() ) {
			if ( $group_type->get_is_course() ) {
				$can_clone = cboxol_user_can_create_courses( bp_loggedin_user_id() );
			} else {
				$can_clone = true;
			}
		}

		echo '<p>';
		if ( $can_clone ) {
			echo sprintf( '<a class="btn btn-default btn-block btn-primary link-btn" href="%s"><i class="fa fa-clone" aria-hidden="true"></i> %s</a>', esc_attr( $clone_link ), esc_html( $group_type->get_label( 'clone_this_group' ) ) );
		} else {
			esc_html_e( 'Logged-in members can clone.', 'commons-in-a-box' );
		}
		echo '</p>';

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['after_widget'];
	}

	/**
	 * Admin form.
	 */
	public function form( $instance ) {
		$group_type = cboxol_get_group_site_type( get_current_blog_id() );
		if ( ! $group_type || is_wp_error( $group_type ) ) {
			return;
		}

		?>
		<p><?php echo esc_html( $group_type->get_label( 'shareable_content_widget_description' ) ); ?></p>
		<?php
	}

	/**
	 * Process form options.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
}
