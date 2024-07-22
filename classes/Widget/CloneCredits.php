<?php

namespace CBOX\OL\Widget;

use \WP_Widget;

class CloneCredits extends WP_Widget {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'openlab_clone_credits_widget',
			__( 'Credits', 'commons-in-a-box' ),
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
		$group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );
		$group    = groups_get_group( $group_id );
		$history  = openlab_get_group_clone_history_list( $group_id, $group->creator_id );

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['before_widget'];

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $args['before_title'] . esc_html__( 'Credits', 'commons-in-a-box' ) . $args['after_title'];
		echo '<ul class="clone-credits">';

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $history;

		echo '</ul>';

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
		<p><?php echo esc_html( $group_type->get_label( 'clone_credits_widget_description' ) ); ?></p>
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
