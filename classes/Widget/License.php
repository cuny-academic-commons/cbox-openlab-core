<?php

namespace CBOX\OL\Widget;

use \WP_Widget;

class License extends WP_Widget {
	protected $licenses = [];

	public function __construct() {
		$this->licenses = [
			'by'           => [
				'label' => __( 'Attribution (CC BY)', 'commons-in-a-box' ),
				'url'   => 'https://creativecommons.org/licenses/by/4.0',
			],
			'by-sa'        => [
				'label' => __( 'Attribution-ShareAlike (CC BY-SA)', 'commons-in-a-box' ),
				'url'   => 'https://creativecommons.org/licenses/by-sa/4.0',
			],
			'by-nd'        => [
				'label' => __( 'Attribution-NoDerivs (CC BY-ND)', 'commons-in-a-box' ),
				'url'   => 'http://creativecommons.org/licenses/by-nd/4.0',
			],
			'by-nc'        => [
				'label' => __( 'Attribution-NonCommercial (CC BY-NC)', 'commons-in-a-box' ),
				'url'   => 'http://creativecommons.org/licenses/by-nc/4.0',
			],
			'by-nc-sa'     => [
				'label' => __( 'Attribution-NonCommercial-ShareAlike (CC BY-NC-SA)', 'commons-in-a-box' ),
				'url'   => 'http://creativecommons.org/licenses/by-nc-sa/4.0',
			],
			'by-nc-nd'     => [
				'label' => __( 'Attribution-NonCommercial-NoDerivs (CC BY-NC-ND)', 'commons-in-a-box' ),
				'url'   => 'http://creativecommons.org/licenses/by-nc-nd/4.0',
			],
			'cc-zero'      => [
				'label' => __( 'Public Domain, CC0', 'commons-in-a-box' ),
				'url'   => 'http://creativecommons.org/publicdomain/zero/1.0/',
			],
			'publicdomain' => [
				'label' => __( 'Public Domain', 'commons-in-a-box' ),
				'url'   => 'https://wiki.creativecommons.org/Public_domain',
			],
		];

		parent::__construct(
			'openlab_license',
			__( 'Creative Commons License', 'commons-in-a-box' ),
			array(
				'description' => '',
			)
		);
	}

	public function widget( $args, $instance ) {
		wp_enqueue_style( 'cboxol-license-widget', CBOXOL_PLUGIN_URL . '/assets/css/license-widget.css', [], cboxol_get_asset_version() );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_widget'];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_title'];

		echo esc_html( $instance['title'] );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_title'];

		$license_slug = $instance['license'];
		$license_data = $this->licenses[ $license_slug ];

		$learn_more_text = sprintf(
			'<a href="%s">%s</a>',
			esc_attr( $license_data['url'] ),
			esc_html__( 'Learn more.', 'commons-in-a-box' )
		);

		$license_link = sprintf(
			'<a href="%s">%s</a>',
			esc_attr( $license_data['url'] ),
			esc_html( $license_data['label'] )
		);

		if ( ! empty( $instance['author_name'] ) ) {
			if ( ! empty( $instance['author_url'] ) ) {
				$author_text = sprintf(
					'<a href="%s">%s</a>',
					esc_attr( $instance['author_url'] ),
					esc_html( $instance['author_name'] )
				);
			} else {
				$author_text = esc_html( $instance['author_name'] );
			}

			$text = sprintf(
				'<a class="cc-widget-icon-link" href="%s"><img src="%s" alt="%s" /><span class="screen-reader-text">%s</span></a><p class="cc-widget-text">%s</p>',
				esc_attr( $license_data['url'] ),
				esc_attr( CBOXOL_PLUGIN_URL . '/assets/img/cc/' . $license_slug . '.png' ),
				esc_attr( $license_data['label'] ),
				esc_html__( 'Link to license', 'commons-in-a-box' ),
				sprintf(
					// translators: 1. Link to site author; 2. Linked name of CC license.
					esc_html__( 'Except where otherwise noted, this site by %1$s is distributed under the following license: %2$s.', 'commons-in-a-box' ),
					$author_text,
					$license_link
				)
			);
		} else {
			$text = sprintf(
				'<a class="cc-widget-icon-link" href="%s"><img src="%s" alt="%s" /><span class="screen-reader-text">%s</span></a><p class="cc-widget-text">%s %s</p>',
				esc_attr( $license_data['url'] ),
				esc_attr( CBOXOL_PLUGIN_URL . '/assets/img/cc/' . $license_slug . '.png' ),
				esc_attr( $license_data['label'] ),
				esc_html__( 'Link to license', 'commons-in-a-box' ),
				sprintf(
					// translators: Linked  of CC license.
					esc_html__( 'Except where otherwise noted, this site is distributed under the following license: %s', 'commons-in-a-box' ),
					$license_link
				)
			);
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $text;

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$r = array_merge(
			[
				'author_name' => bp_core_get_user_displayname( get_current_user_id() ),
				'author_url'  => bp_core_get_user_domain( get_current_user_id() ),
				'license'     => 'by-nc',
				'title'       => __( 'License', 'commons-in-a-box' ),
			],
			$instance
		);

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'commons-in-a-box' ); ?></label>
			<input type="text" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" value="<?php echo esc_attr( $r['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'author_name' ) ); ?>"><?php esc_html_e( 'Site Author:', 'commons-in-a-box' ); ?></label>
			<input type="text" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'author_name' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'author_name' ) ); ?>" value="<?php echo esc_attr( $r['author_name'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'author_url' ) ); ?>"><?php esc_html_e( 'Site Author URL:', 'commons-in-a-box' ); ?></label>
			<input type="text" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'author_url' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'author_url' ) ); ?>" value="<?php echo esc_attr( $r['author_url'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'license' ) ); ?>"><?php esc_html_e( 'Choose a License:', 'commons-in-a-box' ); ?></label>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'license' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'license' ) ); ?>">
				<?php foreach ( $this->licenses as $slug => $data ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $r['license'] ); ?>><?php echo esc_html( $data['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$new_license = isset( $new_instance['license'] ) ? wp_unslash( $new_instance['license'] ) : 'by';

		$instance = [
			'author_name' => isset( $new_instance['author_name'] ) ? $new_instance['author_name'] : '',
			'author_url'  => isset( $new_instance['author_url'] ) ? $new_instance['author_url'] : '',
			'license'     => isset( $this->licenses[ $new_license ] ) ? $new_license : 'by-nc',
			'title'       => isset( $new_instance['title'] ) ? $new_instance['title'] : '',
		];

		return $instance;
	}
}
