<?php

/**
 * "Connected Group" dashboard widget.
 *
 * @package cbox-openlab-core
 */

namespace CBOX\OL\DashboardWidget;

/**
 * "Connected Group" dashboard widget.
 *
 * @since 1.8.0
 */
class GroupSite {
	/**
	 * Registers the widget.
	 */
	public static function register() {
		add_action( 'wp_dashboard_setup', [ __CLASS__, 'add_widget' ], 0 );
	}

	/**
	 * Adds the widget to the dashboard.
	 */
	public static function add_widget() {
		$group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );
		if ( ! $group_id ) {
			return;
		}

		wp_add_dashboard_widget(
			'cboxol_group_site_dashboard_widget',
			__( 'Connected Group', 'commons-in-a-box' ),
			[ __CLASS__, 'render_widget' ],
			null,
			null,
			'normal',
			'high'
		);
	}

	/**
	 * Renders the content of the widget.
	 */
	public static function render_widget() {
		$group_id = openlab_get_group_id_by_blog_id( get_current_blog_id() );
		$group    = groups_get_group( $group_id );

		$group_type = cboxol_get_group_group_type( $group_id );

		$user_is_admin = current_user_can( 'bp_moderate' ) || groups_is_user_admin( bp_loggedin_user_id(), $group_id ) || groups_is_user_mod( bp_loggedin_user_id(), $group_id );

		?>

		<style type="text/css">
			.cboxol-group-site-dashboard-widget {
				display: flex;
				gap: 20px;
				padding: 16px 8px;
			}

			.cboxol-group-site-dashboard-widget > div {
				flex: 0 1 50%;
			}

			.cboxol-group-site-dashboard-widget-avatar img {
				max-width: 100%;
			}

			.cboxol-group-site-dashboard-widget-avatar .padded-img.darker {
				border-radius: 4px;
				border: 25px solid #f0f0f0;
				border-color: #444444;
				background: #444444;
			}

			.cboxol-group-site-dashboard-widget-avatar > div {
				display: flex;
			}

			.cboxol-group-site-dashboard-widget-info {
				padding-right: 20px;
			}

			.cboxol-group-site-dashboard-widget .btn {
				padding: 9px 12px;
				text-align: left;
				text-decoration: none;
				margin-top: 5px;
				color: #ffffff;
				display: block;
				width: 100%;
				background-color: #444444;
				border-color: #373737;
				max-width: 100%;
			}

			.cboxol-group-site-dashboard-widget .dashicons-link {
				display: inline-block;
				position: relative;
				padding-left: 20px;
			}

			.cboxol-group-site-dashboard-widget .dashicons-link:before {
				font-family: 'Dashicons';
				font-size: 16px;
				position: absolute;
				left: 0;
				top: -2px;
			}

			.cboxol-group-site-dashboard-widget .dashicons-link.link-home:before {
				content: "\f102";
			}

			.cboxol-group-site-dashboard-widget .dashicons-link.link-edit-description:before {
				content: "\f464";
			}

			.cboxol-group-site-dashboard-widget .dashicons-link.link-users:before {
				content: "\f110";
			}

			.cboxol-group-site-dashboard-widget .dashicons-link.link-email:before {
				content: "\f465";
			}

			.cboxol-group-site-dashboard-widget .dashicons-link.link-privacy:before {
				content: "\f194";
			}
		</style>

		<div class="cboxol-group-site-dashboard-widget">
			<div class="cboxol-group-site-dashboard-widget-avatar">
				<div class="padded-img darker">
					<?php
					$group_avatar = bp_core_fetch_avatar(
						array(
							'item_id' => $group_id,
							'object'  => 'group',
							'type'    => 'full',
							'html'    => false,
						)
					);
					?>
					<img class="img-responsive" src="<?php echo esc_attr( $group_avatar ); ?>" alt="<?php echo esc_attr( $group_name ); ?>"/>
				</div>

				<?php if ( $user_is_admin ) : ?>
					<div id="group-action-wrapper">
						<a class="btn btn-default btn-block btn-primary link-btn" href="<?php echo esc_url( bp_get_group_manage_url( $group_id, bp_groups_get_path_chunks( array( 'edit-details' ), 'settings' ) ) ); ?>#avatar-panel"><?php esc_html_e( 'Upload Avatar', 'commons-in-a-box' ); ?></a>
					</div>
				<?php endif; ?>
			</div>

			<div class="cboxol-group-site-dashboard-widget-info">
				<p>
					<?php
					printf(
						// translators: %s is replaced with the name of the group, linked to the group home page.
						esc_html__( 'This site is connected to the group: %s', 'commons-in-a-box' ),
						'<a href="' . esc_url( bp_get_group_permalink( $group ) ) . '">' . esc_html( $group->name ) . '</a>'
					);
					?>
				</p>

				<ul class="cboxol-group-site-dashboard-widget-links">
					<li>
						<a class="dashicons-link link-home" href="<?php echo esc_url( bp_get_group_permalink( $group ) ); ?>">
							<?php echo esc_html( $group_type->get_label( 'group_home' ) ); ?>
						</a>
					</li>

					<?php if ( $user_is_admin ) : ?>
						<li>
							<a class="dashicons-link link-edit-description" href="<?php echo esc_url( bp_get_group_manage_url( $group ) ); ?>#panel-details">
								<?php esc_html_e( 'Edit Description', 'commons-in-a-box' ); ?>
							</a>
						</li>

						<li>
							<a class="dashicons-link link-users" href="<?php echo esc_url( bp_get_group_manage_url( $group, bp_groups_get_path_chunks( array( 'manage-members' ), 'settings' ) ) ); ?>">
								<?php esc_html_e( 'Manage Users', 'commons-in-a-box' ); ?>
							</a>
						</li>

						<li>
							<a class="dashicons-link link-email" href="<?php echo esc_url( bp_get_group_manage_url( $group, bp_groups_get_path_chunks( array( 'notifications' ), 'settings' ) ) ); ?>">
								<?php esc_html_e( 'Email Members', 'commons-in-a-box' ); ?>
							</a>
						</li>

						<li>
							<a class="dashicons-link link-privacy" href="<?php echo esc_url( bp_get_group_manage_url( $group ) ); ?>#panel-privacy">
								<?php esc_html_e( 'Manage Visibility/Privacy on Homepage and Directory', 'commons-in-a-box' ); ?>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>

		<?php
	}
}
