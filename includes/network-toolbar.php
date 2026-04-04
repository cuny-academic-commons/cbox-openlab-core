<?php
/**
 * OpenLab Top Header Markup
 */

function openlab_color_schemes() {
	return array(
		'red'   => array(
			'label'      => __( 'Red', 'commons-in-a-box' ),
			'icon_color' => '#a9280e',
		),
		'blue'  => array(
			'label'      => __( 'Blue', 'commons-in-a-box' ),
			'icon_color' => '#1d5f7b',
		),
		'green' => array(
			'label'      => __( 'Green', 'commons-in-a-box' ),
			'icon_color' => '#b6d498',
		),
	);
}

function openlab_get_color_scheme() {
	$switched = false;
	if ( ! bp_is_root_blog() ) {
		switch_to_blog( bp_get_root_blog_id() );
		$switched = true;
	}

	$color_scheme = get_theme_mod( 'openlab_color_scheme' );
	if ( ! $color_scheme ) {
		$color_scheme = 'red';
	}

	if ( $switched ) {
		restore_current_blog();
	}

	return $color_scheme;
}

/**
 * Gets the URL of the default CBOX-OL logo, for display in the toolbar.
 *
 * @return string
 */
function cboxol_get_default_logo_url() {
	return CBOXOL_PLUGIN_URL . 'assets/img/cboxol-logo-horizontal-185.png';
}

function openlab_get_logo_url() {
	$url = '';

	$switched = false;
	if ( ! bp_is_root_blog() ) {
		switch_to_blog( bp_get_root_blog_id() );
		$switched = true;
	}

	// This is the default logo, shipped with the plugin.
	$image = array( cboxol_get_default_logo_url() );

	$custom_logo_id = get_theme_mod( 'openlab_logo' );
	if ( $custom_logo_id ) {
		$image = wp_get_attachment_image_src( $custom_logo_id, 'full', false );
	}

	if ( $switched ) {
		restore_current_blog();
	}

	if ( $image ) {
		$url = $image[0];
	}

	return $url;
}

function openlab_get_logo_html( $link = true ) {
	$switched = false;
	if ( ! bp_is_root_blog() ) {
		switch_to_blog( bp_get_root_blog_id() );
		$switched = true;
	}

	$custom_logo_id = get_theme_mod( 'openlab_logo' );

	if ( $custom_logo_id ) {
		$logo_html = wp_get_attachment_image(
			$custom_logo_id,
			'full',
			false,
			array(
				'class'    => 'custom-logo',
				'itemprop' => 'logo',
				'alt'      => __( 'Site Logo', 'commons-in-a-box' ),
			)
		);
	} else {
		$logo_html = sprintf(
			'<img src="%s" class="custom-logo default-cboxol-logo" alt="%s" />',
			esc_url( cboxol_get_default_logo_url() ),
			esc_html__( 'CBOX-OL Logo', 'commons-in-a-box' )
		);
	}

	if ( $link ) {
		$logo_html = sprintf(
			'<a href="%1$s" class="custom-logo-link" rel="home" itemprop="url">%2$s</a>',
			esc_url( home_url( '/' ) ),
			$logo_html
		);
	}

	if ( $switched ) {
		restore_current_blog();
	}

	return $logo_html;
}

/**
 * Bootstrap
 */
add_action( 'add_admin_bar_menus', array( 'OpenLab_Admin_Bar', 'init' ) );

class OpenLab_Admin_Bar {
	public static function init() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new OpenLab_Admin_Bar();
		}
	}

	public function __construct() {
		// Bail if BP is not present
		if ( ! class_exists( 'BP_Core' ) ) {
			return;
		}

		// Removes the WP logo menu item.
		remove_action( 'admin_bar_menu', 'wp_admin_bar_wp_menu', 10 );

		// Add OpenLab logo link.
		add_action( 'admin_bar_menu', array( $this, 'add_openlab_logo_link' ), 1 );

		if ( get_current_blog_id() !== 1 ) {
			// add meta tag for viewport (some of the themes lack this)
			add_action( 'wp_head', array( $this, 'groups_sites_fix_for_mobile' ) );
		}

		// Customize my-account.
		remove_action( 'admin_bar_menu', 'wp_admin_bar_my_account', 0 );
		add_action( 'admin_bar_menu', [ $this, 'my_account' ], 0 );

		// Don't let BP load its admin bar.
		remove_action( 'admin_bar_menu', 'bp_setup_admin_bar', 20 );

		// For cleaning up any plugin add-ons.
		add_action( 'wp_before_admin_bar_render', array( $this, 'adminbar_plugin_cleanup' ), 9999 );

		// Logged-in only
		if ( is_user_logged_in() ) {
			add_action( 'admin_bar_menu', array( $this, 'modify_howdy' ), 9999999 );

			add_action( 'admin_bar_menu', array( $this, 'remove_notifications_hook' ), 5 );

			// Don't show the My Sites menu
			remove_action( 'admin_bar_menu', 'wp_admin_bar_my_sites_menu', 20 );

			// Don't show the My Achievements menu item.
			remove_action( 'admin_bar_menu', 'dpa_admin_bar_menu' );

			add_action( 'admin_bar_menu', array( $this, 'maybe_remove_thisblog' ), 99 );

			remove_action( 'admin_bar_menu', 'wp_admin_bar_updates_menu', 50 );
		} else {
			add_action( 'admin_bar_menu', array( $this, 'add_sign_in_menu' ), 1 );
		}
	}

	/**
	 * Add the main OpenLab logo link.
	 */
	public function add_openlab_logo_link( $wp_admin_bar ) {
		$logo = openlab_get_logo_url();

		$title = sprintf(
			'<span class="screen-reader-text">%s</span> <span class="logo-wrapper"><img src="%s" alt="" /></span>',
			esc_html( get_blog_option( 1, 'blogname' ) ),
			$logo
		);

		$wp_admin_bar->add_node(
			array(
				'id'    => 'openlab',
				'title' => $title,
				'href'  => bp_get_root_domain(),
				'meta'  => array(
					'tabindex' => 90,
					'class'    => 'admin-bar-menu admin-bar-menu-openlab-logo hidden-xs',
				),
			)
		);

		// We add a separate, mobile only item, which appears only for logged-out users.
		if ( ! is_user_logged_in() ) {
			$mobile_title = sprintf(
				'<span class="logo-wrapper"><img class="openlab-logo" src="%s" alt="OpenLab at City Tech" /></span>',
				esc_url( $logo )
			);

			$wp_admin_bar->add_node(
				array(
					'id'    => 'openlab-mobile',
					'title' => $mobile_title,
					'href'  => bp_get_root_domain(),
					'meta'  => array(
						'tabindex' => 90,
						'class'    => 'admin-bar-menu admin-bar-menu-openlab-logo-mobile visible-xs',
					),
				)
			);
		}
	}

	/**
	 * Add the My Account menu.
	 */
	public function my_account( $wp_admin_bar ) {
		$user_id      = get_current_user_id();
		$current_user = wp_get_current_user();

		if ( ! $user_id ) {
			return;
		}

		$my_openlab_url = bp_members_get_user_url( bp_loggedin_user_id() );

		$user_avatar = get_avatar( $user_id, 64 );
		$user_name   = bp_get_loggedin_user_fullname();

		$user = get_user_by( 'id', $user_id );

		$wp_admin_bar->add_group(
			array(
				'parent' => 'my-account',
				'id'     => 'user-actions',
			)
		);

		$user_info = sprintf(
			'<span class="user-avatar">%s</span><span class="username-and-nicename"><span class="username">%s</span><span class="nicename">%s</span></span>',
			$user_avatar,
			$user_name,
			$user->user_nicename
		);

		$wp_admin_bar->add_node(
			array(
				'parent' => 'user-actions',
				'id'     => 'user-info',
				'title'  => $user_info,
				'href'   => $my_openlab_url,
			)
		);

		$wp_admin_bar->add_node(
			array(
				'parent' => 'user-actions',
				'id'     => 'my-openlab-link',
				'title'  => 'My OpenLab',
				'href'   => $my_openlab_url,
			)
		);

		$wp_admin_bar->add_node(
			array(
				'parent' => 'user-actions',
				'id'     => 'my-account-logout-link',
				'title'  => 'Sign Out',
				'href'   => wp_logout_url( bp_get_root_domain() ),
			)
		);
	}

	/**
	 * Modifies the 'Howdy' link, changing it to 'Hi', adding the My OpenLab logo, and changing the link.
	 */
	public function modify_howdy( $wp_admin_bar ) {
		$my_openlab_logo_url = home_url( 'wp-content/mu-plugins/img/my-openlab-icon.png' );

		$title = sprintf(
			'<span class="howdy hidden-xs">Hi, %s</span> <img class="my-openlab-logo hidden-xs" src="%s" alt="My OpenLab" />',
			bp_get_loggedin_user_fullname(),
			$my_openlab_logo_url
		);

		$wp_admin_bar->add_node(
			array(
				'id'    => 'my-account',
				'title' => $title,
				'href'  => bp_members_get_user_url( bp_loggedin_user_id() ),
				'meta'  => array(
					'class' => 'user-display-name',
				),
			)
		);
	}

	/**
	 * Adds the Sign In menu.
	 */
	public function add_sign_in_menu( $wp_admin_bar ) {
		$my_openlab_logo_url = home_url( 'wp-content/mu-plugins/img/my-openlab-icon.svg' );
		$openlab_logo_url    = home_url( 'wp-content/mu-plugins/img/openlab-logo-notext.svg' );

		$title = "<span>Sign In</span> <img class='my-openlab-logo visible-xs' src='$my_openlab_logo_url' alt='OpenLab at City Tech' />";

		$wp_admin_bar->add_node(
			array(
				'id'    => 'openlab-sign-in',
				'title' => $title,
				'href'  => '#',
				'meta'  => array(
					'class' => 'ab-top-secondary',
				),
			)
		);

		$wp_admin_bar->add_group(
			array(
				'parent' => 'openlab-sign-in',
				'id'     => 'openlab-sign-in-actions',
			)
		);

		$info_title = sprintf(
			'<div class="openlab-sign-in-info-container">
				<div class="openlab-sign-in-info-logo"><span class="openlab-sign-in-info-logo-wrap"><img src="%s" alt="OpenLab at City Tech" /></span></div>
				<div class="openlab-sign-in-info-text">
					<div class="openlab-sign-in-info-sitename"><a href="https://openlab.citytech.cuny.edu">OpenLab at City Tech</a></div>
					<div class="openlab-sign-in-info-tagline">A place to learn, work, and share</div>

					<div class="openlab-sign-in-info-signin">
						<a href="%s">Sign In</a>
					</div>

					<div class="openlab-sign-up-info-sign-up">
						Need an account? <a href="%s">Sign Up</a>
					</div>
				</div>
			</div>',
			$openlab_logo_url,
			wp_login_url( home_url() ),
			bp_get_signup_page()
		);

		$wp_admin_bar->add_node(
			[
				'parent' => 'openlab-sign-in-actions',
				'id'     => 'openlab-sign-in-info',
				'title'  => false,
				'meta'   => array(
					'class' => 'openlab-sign-in-info',
					'html'  => $info_title,
				),
			]
		);
	}

	/**
	 * Add the main OpenLab menu
	 */
	public function add_network_menu( $wp_admin_bar ) {
		$logo_url = openlab_get_logo_url();

		$wp_admin_bar->add_node(
			array(
				'id'    => 'openlab',
				'title' => bp_get_option( 'blogname' ),
				'href'  => bp_get_root_url(),
				'meta'  => array(
					'tabindex' => 90,
					'class'    => 'admin-bar-menu hidden-xs main-logo-menu',
				),
			)
		);

		?>
		<style type="text/css">
			.oplb-bs #wpadminbar #wp-toolbar > ul > li#wp-admin-bar-openlab > .ab-item {
				background-image: url('<?php echo esc_url( $logo_url ); ?>');
			}
		</style>
		<?php

		$this->openlab_menu_items( 'openlab' );
	}

	public function openlab_menu_items( $parent ) {
		global $wp_admin_bar;

		$wp_admin_bar->add_node(
			array(
				'parent' => $parent,
				'id'     => 'home-' . $parent,
				'title'  => esc_html__( 'Home', 'commons-in-a-box' ),
				'href'   => bp_get_root_url(),
				'meta'   => array(
					'class' => 'mobile-no-hover',
				),
			)
		);

		$menu_items = openlab_network_nav_items();

		foreach ( $menu_items as $menu_item ) {
			$wp_admin_bar->add_node(
				array(
					'parent' => $parent,
					'id'     => $menu_item->post_name . '-' . $parent,
					'title'  => esc_html( $menu_item->post_title ),
					'href'   => esc_url( $menu_item->url ),
					'meta'   => array(
						'class' => 'mobile-no-hover',
					),
				)
			);
		}
	}

	/**
	 * Remove the Notifications menu
	 *
	 * We have to do it in a function like this because of the way BP adds the menu in the first
	 * place
	 */
	public function remove_notifications_hook( $wp_admin_bar ) {
		remove_action( 'admin_bar_menu', 'bp_members_admin_bar_notifications_menu', 90 );
	}

	/**
	 * Maybe remove the current blog menu
	 */
	public function maybe_remove_thisblog( $wp_admin_bar ) {
		if ( ! current_user_can( 'publish_posts' ) ) {
			$wp_admin_bar->remove_node( 'site-name' );
		}
	}

	/**
	 * Custom content menu
	 * @param type $wp_admin_bar
	 * @return type
	 */
	public function add_custom_content_menu( $wp_admin_bar ) {
		$actions = array();

		$cpts = (array) get_post_types( array( 'show_in_admin_bar' => true ), 'objects' );

		if ( isset( $cpts['post'] ) && current_user_can( $cpts['post']->cap->create_posts ) ) {
			$actions['post-new.php'] = array( $cpts['post']->labels->name_admin_bar, 'new-post' );
		}

		if ( isset( $cpts['attachment'] ) && current_user_can( 'upload_files' ) ) {
			$actions['media-new.php'] = array( $cpts['attachment']->labels->name_admin_bar, 'new-media' );
		}

		if ( current_user_can( 'manage_links' ) ) {
			$actions['link-add.php'] = array( _x( 'Link', 'add new from admin bar', 'commons-in-a-box' ), 'new-link' );
		}

		if ( isset( $cpts['page'] ) && current_user_can( $cpts['page']->cap->create_posts ) ) {
			$actions['post-new.php?post_type=page'] = array( $cpts['page']->labels->name_admin_bar, 'new-page' );
		}

		unset( $cpts['post'], $cpts['page'], $cpts['attachment'] );

		// Add any additional custom post types.
		foreach ( $cpts as $cpt ) {
			// Ignore bbPress content.
			if ( in_array( $cpt->name, [ 'topic', 'forum' ], true ) ) {
				continue;
			}

			if ( ! current_user_can( $cpt->cap->create_posts ) ) {
				continue;
			}

			$key             = 'post-new.php?post_type=' . $cpt->name;
			$actions[ $key ] = array( $cpt->labels->name_admin_bar, 'new-' . $cpt->name );
		}
		// Avoid clash with parent node and a 'content' post type.
		if ( isset( $actions['post-new.php?post_type=content'] ) ) {
			$actions['post-new.php?post_type=content'][1] = 'add-new-content';
		}

		if ( current_user_can( 'create_users' ) || current_user_can( 'promote_users' ) ) {
			$actions['user-new.php'] = array( _x( 'User', 'add new from admin bar', 'commons-in-a-box' ), 'new-user' );
		}

		if ( ! $actions ) {
			return;
		}

		$title = '<span class="fa fa-plus-circle hidden-xs" aria-hidden="true"></span><span class="ab-icon dashicon-icon visible-xs" aria-hidden="true"></span><span class="sr-only">' . _x( 'Add New', 'admin bar menu group label', 'commons-in-a-box' ) . '</span>';

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'new-content',
				'title' => $title,
				'href'  => admin_url( current( array_keys( $actions ) ) ),
				'meta'  => array(
					'title'    => _x( 'Add New', 'admin bar menu group label', 'commons-in-a-box' ),
					'class'    => 'mobile-no-hover admin-bar-menu',
					'tabindex' => 0,
				),
			)
		);

		foreach ( $actions as $link => $action ) {
			list( $title, $id ) = $action;

			$wp_admin_bar->add_menu(
				array(
					'parent' => 'new-content',
					'id'     => $id,
					'title'  => $title,
					'href'   => admin_url( $link ),
					'meta'   => array(
						'class' => 'admin-bar-menu-item',
					),
				)
			);
		}
	}

	/**
	 * Fix the logout redirect
	 */
	public function fix_logout_redirect( $wp_admin_bar ) {
		$wp_admin_bar->add_menu(
			array(
				'id'   => 'logout',
				'href' => add_query_arg( 'redirect_to', bp_get_root_url(), wp_logout_url() ),
			)
		);
	}

	/**
	 * Adds the Sign Up item
	 */
	public function add_signup_item( $wp_admin_bar ) {
		// Remove so we can replace in the right order
		$signup = $wp_admin_bar->get_node( 'bp-register' );
		$login  = $wp_admin_bar->get_node( 'bp-login' );

		$wp_admin_bar->remove_node( 'bp-register' );
		$wp_admin_bar->remove_node( 'bp-login' );

		// Change the title of the signup node
		// Move them both to top-secondary, to appear at the right
		if ( $signup ) {
			$signup->title  = __( 'Sign Up', 'commons-in-a-box' );
			$signup->parent = 'top-secondary';
			$wp_admin_bar->add_node( (array) $signup );
		}

		$login->parent = 'top-secondary';
		$wp_admin_bar->add_node( (array) $login );
	}

	public function groups_sites_fix_for_mobile() {
		?>

			<meta name="viewport" content="width=device-width">

		<?php
	}

	/**
	 * Miscellaneous button cleanup.
	 *
	 * @param type $wp_admin_bar
	 */
	public function adminbar_plugin_cleanup( $wp_admin_bar ) {
		global $wp_admin_bar;

		$wp_admin_bar->remove_menu( 'tribe-events' );
		$wp_admin_bar->remove_menu( 'openlab-favorites' );
		$wp_admin_bar->remove_menu( 'enable-jquery-migrate-helper' );
		$wp_admin_bar->remove_menu( 'new-user' );
		$wp_admin_bar->remove_menu( 'ngg-menu' );
		$wp_admin_bar->remove_menu( 'duplicate-post' );
		$wp_admin_bar->remove_menu( 'new-draft' );

		$wp_admin_bar->remove_menu( 'search' );
		$wp_admin_bar->remove_menu( 'logout' );
	}
}

function openlab_admin_bar_counts( $count, $pull_right = ' pull-right' ) {

	if ( $count < 1 ) {
		return '';
	} else {
		return '<span class="toolbar-item-count count-' . $count . $pull_right . '">' . $count . '</span>';
	}

}

function cac_adminbar_enqueue_scripts() {
	$ver = cboxol_get_asset_version();

	wp_enqueue_script( 'openlab-search-js', CBOXOL_PLUGIN_URL . '/assets/js/search.js', array( 'jquery' ), $ver, true );
	wp_register_script( 'smoothscroll-js', CBOXOL_PLUGIN_URL . '/assets/js/lib/jquery-smooth-scroll/jquery.smooth-scroll.min.js', array( 'jquery' ), $ver, true );
	wp_enqueue_script( 'smoothscroll-js' );
	wp_register_script( 'select-js', CBOXOL_PLUGIN_URL . '/assets/js/lib/select2/select2.min.js', array( 'jquery' ), $ver, true );
	wp_enqueue_script( 'select-js' );
	wp_register_script( 'hyphenator-js', CBOXOL_PLUGIN_URL . '/assets/js/lib/hyphenator/hyphenator.js', array( 'jquery' ), $ver, true );
	wp_enqueue_script( 'hyphenator-js' );
	wp_register_script( 'succinct-mod-js', CBOXOL_PLUGIN_URL . '/assets/js/lib/succint/jQuery.succinct.mod.js', array( 'jquery' ), $ver, true );
	wp_enqueue_script( 'succinct-mod-js' );
	wp_register_script( 'openlab-search-js', CBOXOL_PLUGIN_URL . '/assets/js/lib/openlab/openlab.search.js', array( 'jquery' ), $ver, true );
	wp_enqueue_script( 'openlab-search-js' );

	wp_register_script( 'openlab-truncation-js', CBOXOL_PLUGIN_URL . '/assets/js/lib/openlab/openlab.truncation.js', array( 'jquery' ), $ver, true );
	wp_enqueue_script( 'openlab-truncation-js' );
	wp_localize_script(
		'openlab-truncation-js',
		'OpenLabTruncationStrings',
		array(
			'seeMore' => __( 'See More', 'commons-in-a-box' ),
		)
	);

	wp_register_script( 'openlab-nav-js', CBOXOL_PLUGIN_URL . '/assets/js/lib/openlab/openlab.nav.js', array( 'jquery' ), $ver, true );
	wp_enqueue_script( 'openlab-nav-js' );
	wp_localize_script(
		'openlab-nav-js',
		'utilityVars',
		array(
			'loginForm' => openlab_get_loginform(),
		)
	);

	wp_register_script( 'openlab-theme-fixes-js', CBOXOL_PLUGIN_URL . '/assets/js/lib/openlab/openlab.theme.fixes.js', array( 'jquery' ), $ver, true );
	wp_enqueue_script( 'openlab-theme-fixes-js' );
}
add_action( 'wp_enqueue_scripts', 'cac_adminbar_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'cac_adminbar_enqueue_scripts' );

/**
 * Moved login form so that is injected via a localized variable
 * Allows for additional interaction in openlab.nav.js
 * Moved markup to separate template for easier editing
 */
function openlab_get_loginform() {
	$form_out = '';

	ob_start();
	include CBOXOL_PLUGIN_DIR . '/templates/loginform.php';
	$form_out = ob_get_clean();

	return $form_out;
}

/**
 * The following functions wrap the admin bar in an 'oplb-bs' class to isolate bootstrap styles from the rest of the page
 * This is to avoid styling conflicts on the admin pages and group sites
 * FYI: due to an undiagnosed issue in the LESS compilation, the class has to be wrapped twice to work; definitely will try to fix this in the future
 */
function openlab_wrap_adminbar_top() {
	if ( get_current_blog_id() !== 1 || is_admin() ) :

		$admin_class = ( is_admin() ? ' admin-area' : '' );
		?>
		<div class="oplb-bs adminbar-manual-bootstrap<?php echo esc_attr( $admin_class ); ?>"><div class="oplb-bs adminbar-manual-bootstrap<?php echo esc_attr( $admin_class ); ?>">
	<?php else : ?>
		<div class="oplb-bs"><div class="oplb-bs">
		<?php
	endif;
}

add_action( 'wp_before_admin_bar_render', 'openlab_wrap_adminbar_top' );

function openlab_wrap_adminbar_bottom() {
	?>
		</div></div><!--oplb-bs-->
		<div id="behind_menu_background"></div>
	<?php
}

add_action( 'wp_after_admin_bar_render', 'openlab_wrap_adminbar_bottom' );

/**
 * Fetch the network footer.
 *
 * Built on the main site, with markup stashed in a transient.
 */
function openlab_network_footer() {
	// No need for this if running openlab-theme.
	if ( function_exists( 'openlab_site_footer' ) ) {
		return;
	}

	// Don't add to Legacy Widget previews.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! empty( $_GET['legacy-widget-preview'] ) ) {
		return;
	}

	// Don't add to Legacy Widget previews, redux.
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		global $wp;
		if ( false !== strpos( $wp->request, 'widget-types' ) ) {
			return;
		}
	}

	$footer = get_site_transient( 'cboxol_network_footer' );

	if ( ! $footer ) {
		return;
	}

	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $footer;

	echo preg_replace( '/id="openlab-footer" class="([^"]+)"/', 'id="openlab-footer" class="oplb-bs placeholder" aria-hidden="true" tabindex="-1"', $footer );
	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

}
add_action( 'wp_footer', 'openlab_network_footer', 5 );

/**
 * Fetch the network nav items.
 *
 * Built on the main site, with objects stashed in a transient.
 */
function openlab_network_nav_items() {
	$items = get_site_transient( 'cboxol_network_nav_items' );

	if ( ! $items && bp_is_root_blog() ) {
		$locations    = get_nav_menu_locations();
		$main_menu_id = $locations['main'];
		$items        = wp_get_nav_menu_items( $main_menu_id, array( 'update_post_term_cache' => false ) );

		set_site_transient( 'cboxol_network_nav_items', $items );
	}

	return $items;
}

/**
 * Enqueues global header/footer styles.
 *
 * This is done outside the admin bar class, for sites where the toolbar does not display.
 */
function cboxol_enqueue_global_styles() {
	global $wpdb;

	$root_blog_id = bp_get_root_blog_id();
	$ver          = cboxol_get_asset_version();

	// getting the theme folder for the main site
	$main_site_theme = get_blog_option( $root_blog_id, 'template' );

	wp_register_style( 'google-open-sans', 'https://fonts.googleapis.com/css?family=Open+Sans:400,400italic,600,600italic,700,700italic', array(), $ver, 'all' );
	wp_enqueue_style( 'google-open-sans' );

	$openlab_theme_link = home_url( 'wp-content/themes/' ) . $main_site_theme . '/css/font-awesome.min.css';
	$openlab_theme_link = set_url_scheme( $openlab_theme_link );

	// making sure dashicons fire up for front end
	if ( ! is_admin() ) {
		wp_register_style( 'dashicons', home_url() . '/wp-includes/css/dashicons.min.css', array(), $ver );
		wp_enqueue_style( 'dashicons' );
	}

	// registering font-awesome here so it can be used on the admin bar and on the main site
	wp_register_style( 'font-awesome', $openlab_theme_link, array(), $ver, 'all' );
	wp_enqueue_style( 'font-awesome' );
	//custom admin bar styles

	$adminbar_custom_url = CBOXOL_PLUGIN_URL . '/assets/css/admin-bar-custom.css';
	$adminbar_custom_url = set_url_scheme( $adminbar_custom_url );

	$color_scheme = openlab_get_color_scheme();

	$openlab_toolbar_url = content_url( '/themes/openlab-theme/css/color-schemes/toolbar-' . $color_scheme . '.css' );
	$openlab_toolbar_url = set_url_scheme( $openlab_toolbar_url );

	wp_enqueue_style( 'admin-bar-custom', $adminbar_custom_url, array( 'font-awesome' ), $ver );
	wp_enqueue_style( 'openlab-toolbar', $openlab_toolbar_url, array( 'font-awesome' ), $ver );
}
add_action( 'wp_enqueue_scripts', 'cboxol_enqueue_global_styles' );
add_action( 'admin_enqueue_scripts', 'cboxol_enqueue_global_styles' );
