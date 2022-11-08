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
 * Sitewide header markup
 * Includs sitewide logo and sitewide search
 */
function openlab_sitewide_header( $location = 'header' ) {
	$logo_url = openlab_get_logo_url();
	?>

	<div class="header-mobile-wrapper visible-xs">
		<div class="container-fluid">
			<div class="navbar-header clearfix">
				<header class="menu-title pull-left">
					<a href="<?php echo esc_attr( bp_get_root_domain() ); ?>" title="<?php echo esc_attr( _x( 'Home', 'Home page banner link title', 'commons-in-a-box' ) ); ?>" style="background-image: url('<?php echo esc_url( $logo_url ); ?>');"><span class="screen-reader-text"><?php bp_site_name(); ?></span></a>
				</header>
				<div class="pull-right search">
					<div class="search-trigger-wrapper">
						<button class="search-trigger btn-link" data-mode="mobile" data-location="<?php echo esc_attr( $location ); ?>" href="#"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only"><?php esc_html_e( 'Open Search', 'commons-in-a-box' ); ?></span></button>
					</div>
				</div>
			</div>
			<div class="search search-form row">
				<?php openlab_mu_site_wide_bp_search( 'mobile', $location ); ?>
			</div>
		</div>
	</div>

	<?php
}

function openlab_sitewide_header_to_admin_and_group_sites() {
	global $pagenow;

	// No need to append on the main site.
	if ( bp_is_root_blog() ) {
		return;
	}

	// We don't do this on the front end.
	if ( ! is_admin() ) {
		return;
	}

	// Don't do this on widgets.php, which is where Legacy Widgets are served in an iframe.
	if ( 'widgets.php' === $pagenow ) {
		return;
	}

	?>

	<nav class="navbar navbar-default oplb-bs navbar-location-oplb-bs visible-xs" role="navigation">
		<?php openlab_sitewide_header(); ?>
	</nav>

	<?php
}
add_action( 'wp_footer', 'openlab_sitewide_header_to_admin_and_group_sites' );
add_action( 'in_admin_header', 'openlab_sitewide_header_to_admin_and_group_sites' );

function openlab_mu_site_wide_bp_search( $mode = 'desktop', $location = '' ) {
	$mobile_mup = '';

	$button_sr_text = esc_html__( 'Open Search', 'commons-in-a-box' );

	if ( 'desktop' === $mode ) {

		$mobile_mup .= <<<HTML
<div class="search-trigger-wrapper">
    <button class="search-trigger btn-link" data-mode="desktop" data-location={$location} href="#"><span class="fa fa-search" aria-hidden="true"></span><span class="sr-only">{$button_sr_text}</span></button>
</div>
HTML;
	}

	$form_action = trailingslashit( bp_get_root_domain() );
	$nonce       = wp_create_nonce( 'bp_search_form' );

	$sr_text            = esc_html__( 'Search', 'commons-in-a-box' );
	$sr_text_button     = esc_html__( 'Submit', 'commons-in-a-box' );
	$search_placeholder = esc_attr__( 'Search', 'commons-in-a-box' );

	$options = array(
		'<option value="members">' . esc_html__( 'People', 'commons-in-a-box' ) . '</option>',
	);

	foreach ( cboxol_get_group_types() as $group_type ) {
		$options[] = sprintf(
			'<option value="%s">%s</option>',
			esc_attr( $group_type->get_slug() ),
			esc_html( $group_type->get_label( 'plural' ) )
		);
	}

	$options_html = implode( "\n\r", $options );

	$mobile_mup .= <<<HTML
    <div class="search-form-wrapper search-mode-{$mode} search-form-location-{$location}">
    <form action="{$form_action}" method="post" id="search-form-{$mode}-{$location}" class="form-inline">
        <div class="form-group">
		<label for="search-terms-{$mode}-{$location}" class="screen-reader-text">{$sr_text}</label>
        <input id="search-terms-{$mode}-{$location}" class="form-control search-terms search-terms-{$mode}" type="text" name="search" placeholder="{$search_placeholder}" />

        <button class="btn btn-primary top-align search-submit" id="search-submit-{$mode}-{$location}" type="submit"><span class="screen-reader-text">{$sr_text_button}</span><i class="fa fa-search"></i></button>
        <input type="hidden" id="_bp_search_nonce_{$mode }_{$location}" name="_bp_search_nonce" value="{$nonce}" />
        </div>
    </form><!-- #search-form -->
    </div>
HTML;

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $mobile_mup;
}

/**
 * Catch and redirect searches.
 */
function openlab_mu_search_override() {
	if ( isset( $_POST['search'] ) ) {
		if ( ! isset( $_POST['_bp_search_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_bp_search_nonce'], 'bp_search_form' ) ) {
			return;
		}

		$search = wp_unslash( $_POST['search'] );

		$redirect    = null;
		$search_page = cboxol_get_brand_page( 'search-results' );
		if ( $search_page ) {
			$redirect = add_query_arg( 'search', $search, get_permalink( $search_page['id'] ) );
		}

		if ( $redirect ) {
			wp_safe_redirect( $redirect );
			die();
		}
	}
}
add_action( 'wp', 'openlab_mu_search_override', 1 );

/**
 * Removing the default WP admin bar styles; a customized version of the default styles can now be found
 * in the mu-plugins folder
 * This is done for two reasons:
 *
 *  1) The current default css uses a an all selector ('*') to apply a number of default styles that completely
 *     inhibit the ability to inject Bootstrap into the admin, and make custom responsive styling an immense challenge
 *  2) Because this admin bar is now highly customized, we do not want future releases of WP to upset those customizations
 *     without proper vetting
 *
 * Note: in addition to removing the all selector styles, there are also a number of customizations to the custom default admin
 * bar css, to reduce overall styling overhead
 *
 * @param type $styles
 */
function openlab_remove_admin_bar_default_css( $styles ) {
	$styles->remove( 'admin-bar' );
}
add_action( 'wp_default_styles', 'openlab_remove_admin_bar_default_css', 99999 );

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

		// remove BP admin bar styling too
		add_filter( 'bp_core_register_common_styles', array( $this, 'remove_bp_admin_bar_styles' ) );

		// Add a body style to distinguish between sites
		add_action( 'body_class', array( &$this, 'body_class' ), 999 );
		add_action( 'admin_body_class', array( &$this, 'admin_body_class' ), 999 );

		// Enqueue styles
		add_action( 'wp_print_styles', array( &$this, 'enqueue_styles' ) );
		add_action( 'admin_print_styles', array( &$this, 'enqueue_styles' ) );

		// Removes the rude WP logo menu item
		remove_action( 'admin_bar_menu', 'wp_admin_bar_wp_menu', 10 );

		// Removes the Search menu item
		remove_action( 'admin_bar_menu', 'wp_admin_bar_search_menu', 4 );

		// restricting network menu to group sites only
		if ( get_current_blog_id() !== 1 || is_admin() ) {
			add_action( 'admin_bar_menu', array( $this, 'add_network_menu' ), 1 );
			add_filter( 'body_class', array( $this, 'adminbar_special_body_class' ) );
		}

		if ( get_current_blog_id() === 1 ) {
			// adjust the padding at the top of the page
			add_action( 'wp_head', array( $this, 'admin_bar_html_update' ), 99999 );
		} else {
			// adjust the padding at the top of the page - group sites
			add_action( 'wp_head', array( $this, 'admin_bar_group_sites_html_update' ), 99999 );
			// add meta tag for viewport (some of the themes lack this)
			add_action( 'wp_head', array( $this, 'groups_sites_fix_for_mobile' ) );
		}

		// for top padding in admin
		add_action( 'admin_footer', array( $this, 'admin_bar_padding_in_admin' ) );

		// for hamburger menu on mobile
		add_action( 'admin_bar_menu', array( $this, 'openlab_hamburger_menu' ), 1 );

		// Logged-in only
		if ( is_user_logged_in() ) {

			//hamburger mol menu
			add_action( 'admin_bar_menu', array( $this, 'openlab_hamburger_mol_menu' ), 1 );

			//remove the default mobile dashboard toggle, we need a custom one for this for styling purposes
			remove_action( 'admin_bar_menu', 'wp_admin_bar_sidebar_toggle', 0 );
			add_action( 'admin_bar_menu', array( $this, 'custom_admin_bar_sidebar_toggle' ), 0 );

			if ( get_current_blog_id() === 1 && ! is_admin() ) {
				add_action( 'admin_bar_menu', array( $this, 'add_middle_group_for_mobile' ), 200 );
				add_action( 'admin_bar_menu', array( $this, 'add_mobile_mol_link' ), 9999 );
			}

			add_action( 'admin_bar_menu', array( $this, 'add_my_openlab_menu' ), 2 );
			add_action( 'admin_bar_menu', array( $this, 'change_howdy_to_hi' ), 7 );
			add_action( 'admin_bar_menu', array( $this, 'prepend_my_to_my_openlab_items' ), 99 );

			add_action( 'admin_bar_menu', array( $this, 'remove_notifications_hook' ), 5 );

			// Don't show the My Sites menu
			remove_action( 'admin_bar_menu', 'wp_admin_bar_my_sites_menu', 20 );

			// Don't show the Edit Group or Edit Member menus
			remove_action( 'admin_bar_menu', 'bp_groups_group_admin_menu', 99 );
			remove_action( 'admin_bar_menu', 'bp_members_admin_bar_user_admin_menu', 99 );

			// Don't show the My Achievements menu item.
			remove_action( 'admin_bar_menu', 'dpa_admin_bar_menu' );

			// Add the notification menus
			add_action( 'admin_bar_menu', array( $this, 'add_invites_menu' ), 22 );
			add_action( 'admin_bar_menu', array( $this, 'add_messages_menu' ), 24 );
			add_action( 'admin_bar_menu', array( $this, 'add_activity_menu' ), 26 );

			// customizations for site menu
			remove_action( 'admin_bar_menu', 'wp_admin_bar_site_menu', 30 );
			add_action( 'admin_bar_menu', array( $this, 'openlab_custom_admin_bar_site_menu' ), 30 );

			add_action( 'admin_bar_menu', array( $this, 'maybe_remove_thisblog' ), 99 );

			add_action( 'admin_bar_menu', array( $this, 'remove_adduser' ), 9999 );

			//removing the default account information item and menu so we can a custom Bootstrap-style one
			remove_action( 'admin_bar_menu', 'wp_admin_bar_my_account_item', 7 );
			add_action( 'admin_bar_menu', array( $this, 'openlab_custom_my_account_item' ), 7 );
			remove_action( 'admin_bar_menu', 'wp_admin_bar_my_account_menu', 0 );
			add_action( 'admin_bar_menu', array( $this, 'openlab_custom_my_account_menu' ), 0 );

			add_action( 'admin_bar_menu', array( $this, 'add_logout_item' ), 9999 );

			//creating custom menus for comments, new content, and editing

			remove_action( 'admin_bar_menu', 'wp_admin_bar_updates_menu', 50 );
			add_action( 'admin_bar_menu', array( $this, 'add_custom_updates_menu' ), 50 );

			if ( ! is_network_admin() && ! is_user_admin() ) {
				remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
				remove_action( 'admin_bar_menu', 'wp_admin_bar_new_content_menu', 70 );
				add_action( 'admin_bar_menu', array( $this, 'add_dashboard_link' ), 50 );
				add_action( 'admin_bar_menu', array( $this, 'add_custom_comments_menu' ), 60 );
				add_action( 'admin_bar_menu', array( $this, 'add_custom_content_menu' ), 70 );
			}

						remove_action( 'admin_bar_menu', 'wp_admin_bar_edit_menu', 80 );
						add_action( 'admin_bar_menu', array( $this, 'add_custom_edit_menu' ), 80 );

						//for cleanning up any plugin add ons
						add_action( 'wp_before_admin_bar_render', array( $this, 'adminbar_plugin_cleanup' ), 9999 );
		} else {
			add_action( 'admin_bar_menu', array( $this, 'add_signup_item' ), 30 );
			add_action( 'admin_bar_menu', array( $this, 'fix_tabindex' ), 999 );
		}
	}

	public function remove_bp_admin_bar_styles( $styles ) {
		unset( $styles['bp-admin-bar'] );
		return $styles;
	}

	/**
	 * Custom dashboard toggle on mobile
	 */
	public function custom_admin_bar_sidebar_toggle( $wp_admin_bar ) {
		if ( is_admin() ) {

			$sr_text = __( 'Menu', 'commons-in-a-box' );

			$hamburger = <<<HTML
                    <button type="button" class="navbar-toggle mobile-toggle">
                        <span class="sr-only">{$sr_text}</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
HTML;

			$wp_admin_bar->add_menu(
				array(
					'id'    => 'menu-toggle',
					'title' => $hamburger,
					'href'  => '#',
				)
			);
		}
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
				'href'  => bp_get_root_domain(),
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
				'href'   => bp_get_root_domain(),
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
	 * The MOL link on mobile needs to sit between the hamburger menus and the logout link
	 * So we'll need a third group for this (makes styling easier)
	 */
	public function add_middle_group_for_mobile( $wp_admin_bar ) {
		$wp_admin_bar->add_group(
			array(
				'id'   => 'mobile-centered',
				'meta' => array(
					'class' => 'ab-mobile-centered',
				),
			)
		);
	}

	/**
	 * Mol link on mobile
	 */
	public function add_mobile_mol_link( $wp_admin_bar ) {
		$current_user = wp_get_current_user();

		//truncating to be on the safe side
		$username = $current_user->display_name;
		if ( mb_strlen( $username ) > 50 ) {
			$username = substr( $username, 0, 50 ) . '...';
		}
		if ( mb_strlen( $username ) > 12 ) {
			$username_small = substr( $username, 0, 12 ) . '...';
		} else {
			$username_small = $username;
		}

		// translators: display name of logged-in user
		$howdy = '<span class="small-size">' . sprintf( __( 'Hi, %1$s', 'commons-in-a-box' ), $username ) . '</span>';

		// translators: display name of logged-in user
		$howdy_small = '<span class="very-small-size">' . sprintf( __( 'Hi, %1$s', 'commons-in-a-box' ), $username_small ) . '</span>';

			$wp_admin_bar->add_menu(
				array(
					'parent' => 'mobile-centered',
					'id'     => 'my-openlab-mobile',
					'title'  => $howdy . $howdy_small,
					'href'   => bp_loggedin_user_domain(),
					'meta'   => array(
						'class' => 'visible-xs',
					),
				)
			);
	}

	/**
	 * Adds 'My Profile' menu
	 */
	public function add_my_openlab_menu( $wp_admin_bar ) {

		$current_user = wp_get_current_user();

		// translators: display name of logged-in user
		$howdy = sprintf( __( 'Hi, %1$s', 'commons-in-a-box' ), $current_user->display_name );

		$wp_admin_bar->add_node(
			array(
				'id'    => 'my-openlab',
				'title' => esc_html__( 'My Profile', 'commons-in-a-box' ) . ' <span class="fa fa-caret-down" aria-hidden="true"></span>',
				'href'  => bp_loggedin_user_domain(),
				'meta'  => array(
					'class'    => 'admin-bar-menu',
					'tabindex' => 0,
				),
			)
		);
	}

	/**
	 * Hamburger menu (mobile only)
	 */
	public function openlab_hamburger_menu( $wp_admin_bar ) {

		$hamburger = <<<HTML
                    <button type="button" class="navbar-toggle mobile-toggle direct-toggle network-menu" data-target="#wp-admin-bar-network-menu-mobile .ab-sub-wrapper" data-plusheight="19">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
HTML;
		$wp_admin_bar->add_node(
			array(
				'id'    => 'my-hamburger',
				'title' => $hamburger,
				'meta'  => array(
					'class' => 'visible-xs hamburger',
				),
			)
		);
		//
		$wp_admin_bar->add_node(
			array(
				'id'    => 'network-menu-mobile',
				'title' => 'My OpenLab <span class="fa fa-caret-down" aria-hidden="true"></span>',
				'meta'  => array(
					'class'    => 'visible-xs mobile-menu admin-bar-menu',
					'tabindex' => 0,
				),
			)
		);

		$this->openlab_menu_items( 'network-menu-mobile' );

	}

	/**
	 * Hamurger menu (mobile only)
	 */
	public function openlab_hamburger_mol_menu( $wp_admin_bar ) {

		$hamburger = <<<HTML
                    <button type="button" class="navbar-toggle mobile-toggle direct-toggle mol-menu" data-target="#wp-admin-bar-my-openlab .ab-sub-wrapper" data-plusheight="19">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
HTML;
		$wp_admin_bar->add_node(
			array(
				'id'    => 'my-hamburger-mol',
				'title' => $hamburger,
				'meta'  => array(
					'class' => 'visible-xs hamburger',
				),
			)
		);

	}

	/**
	 * Change 'Howdy' message to 'Hi'
	 */
	public function change_howdy_to_hi( $wp_admin_bar ) {
		global $bp;
		$wp_admin_bar->add_node(
			array(
				'id'    => 'my-account',
				'title' => sprintf( 'Hi, %s', $bp->loggedin_user->userdata->display_name ),
				'meta'  => array(),
			)
		);
	}

	/**
	 * Removes BP default "My" items, and builds our own
	 */
	public function prepend_my_to_my_openlab_items( $wp_admin_bar ) {
		$nodes            = $wp_admin_bar->get_nodes();
		$my_openlab_nodes = array();

		foreach ( $nodes as $id => $node ) {
			if ( 'my-account-buddypress' === $node->parent ) {
				$wp_admin_bar->remove_node( $id );
				$my_openlab_nodes[] = $id;
			}
		}

		// Loop through one more time and remove submenus (those with a parent that is a
		// child of my-openlab)
		unset( $nodes );
		$nodes = $wp_admin_bar->get_nodes();
		foreach ( $nodes as $id => $node ) {
			if ( in_array( $node->parent, $my_openlab_nodes, true ) ) {
				$wp_admin_bar->remove_node( $id );
			}
		}

		// Now add our menus
		// profile, courses, projects, clubs, portfolio, friends, messages, invitations, dashboard
		$wp_admin_bar->add_node(
			array(
				'parent' => 'my-openlab',
				'id'     => 'my-profile',
				'title'  => 'My Profile',
				'href'   => bp_loggedin_user_domain(),
				'meta'   => array(
					'class' => 'admin-bar-menu-item mobile-no-hover',
				),
			)
		);

		$wp_admin_bar->add_node(
			array(
				'parent' => 'my-openlab',
				'id'     => 'my-settings',
				'title'  => 'My Settings',
				'href'   => trailingslashit( bp_loggedin_user_domain() . 'settings' ),
				'meta'   => array(
					'class' => 'admin-bar-menu-item mobile-no-hover',
				),
			)
		);

		$group_types = cboxol_get_group_types(
			array(
				'exclude_portfolio' => true,
			)
		);

		foreach ( $group_types as $group_type ) {
			$wp_admin_bar->add_node(
				array(
					'parent' => 'my-openlab',
					'id'     => 'my-groups-' . $group_type->get_slug(),
					'title'  => $group_type->get_label( 'my_groups' ),
					'href'   => cboxol_get_user_group_type_directory_url( $group_type, bp_loggedin_user_id() ),
					'meta'   => array(
						'class' => 'admin-bar-menu-item mobile-no-hover',
					),
				)
			);
		}

		if ( bp_is_active( 'friends' ) ) {
			$request_ids   = friends_get_friendship_request_user_ids( bp_loggedin_user_id() );
			$request_count = openlab_admin_bar_counts( count( $request_ids ) );
			$wp_admin_bar->add_node(
				array(
					'parent' => 'my-openlab',
					'id'     => 'my-friends',
					'title'  => 'My Friends ' . $request_count,
					'href'   => trailingslashit( bp_loggedin_user_domain() . bp_get_friends_slug() ),
					'meta'   => array(
						'class' => 'admin-bar-menu-item mobile-no-hover',
					),
				)
			);
		}

		if ( bp_is_active( 'messages' ) ) {
				$messages_count = openlab_admin_bar_counts( bp_get_total_unread_messages_count() );
			$wp_admin_bar->add_node(
				array(
					'parent' => 'my-openlab',
					'id'     => 'my-messages',
					'title'  => sprintf( 'My Messages %s', $messages_count ),
					'href'   => trailingslashit( bp_loggedin_user_domain() . bp_get_messages_slug() ),
					'meta'   => array(
						'class' => 'admin-bar-menu-item mobile-no-hover',
					),
				)
			);
		}

		if ( bp_is_active( 'groups' ) ) {
			$invites      = groups_get_invites_for_user();
			$invite_count = openlab_admin_bar_counts( isset( $invites['total'] ) ? (int) $invites['total'] : 0 );
			$wp_admin_bar->add_node(
				array(
					'parent' => 'my-openlab',
					'id'     => 'my-invitations',
					'title'  => sprintf( 'My Invitations %s', $invite_count ),
					'href'   => trailingslashit( bp_loggedin_user_domain() . bp_get_groups_slug() . '/invites' ),
					'meta'   => array(
						'class' => 'admin-bar-menu-item mobile-no-hover',
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
	 * Add the Invites menu (Friend Requests + Group Invitations)
	 */
	public function add_invites_menu( $wp_admin_bar ) {
		if ( ! bp_is_active( 'friends' ) || ! bp_is_active( 'groups' ) ) {
			return;
		}

		// We need this data up front so we can provide counts
		$request_ids   = friends_get_friendship_request_user_ids( bp_loggedin_user_id() );
		$request_count = count( (array) $request_ids );

		$invites      = groups_get_invites_for_user();
		$invite_count = isset( $invites['total'] ) ? (int) $invites['total'] : 0;

		$total_count = openlab_admin_bar_counts( $request_count + $invite_count, ' sub-count' );

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'invites',
				'title' => '<span class="toolbar-item-icon fa fa-user" aria-hidden="true"></span><span class="sr-only">Invitations and Friend Requests</span>' . $total_count,
				'meta'  => array(
					'class' => 'hidden-xs',
				),
			)
		);

		/**
		 * FRIEND REQUESTS
		 */

		// "Friend Requests" title
		$wp_admin_bar->add_node(
			array(
				'parent' => 'invites',
				'id'     => 'friend-requests-title',
				'title'  => 'Friend Requests',
				'meta'   => array(
					'class' => 'submenu-title bold',
				),
			)
		);

		$members_args = array(
			'max' => 0,
		);

		$members_args['include'] = ! empty( $request_ids ) ? implode( ',', array_slice( $request_ids, 0, 3 ) ) : '0';

		if ( ! empty( $request_ids ) && bp_has_members( $members_args ) ) {
			while ( bp_members() ) {
				bp_the_member();

				// avatar
				$title = '<div class="row"><div class="col-sm-6"><div class="item-avatar"><a href="' . bp_get_member_link() . '"><img class="img-responsive" src ="' . bp_core_fetch_avatar(
					array(
						'item_id' => bp_get_member_user_id(),
						'object'  => 'member',
						'type'    => 'full',
						'html'    => false,
					)
				) . '" alt="Profile picture of ' . bp_get_member_name() . '"/></a></div></div>';

				// name link
				$title .= '<div class="col-sm-18"><p class="item"><a class="bold" href="' . bp_get_member_link() . '">' . bp_get_member_name() . '</a></p>';

				// accept/reject buttons
				$title .= '<p class="actions clearfix"><a class="btn btn-primary link-btn accept" href="' . bp_get_friend_accept_request_link() . '">' . __( 'Accept', 'commons-in-a-box' ) . '</a> &nbsp; <a class="btn btn-default link-btn reject" href="' . bp_get_friend_reject_request_link() . '">' . __( 'Reject', 'commons-in-a-box' ) . '</a></p></div></div>';

				$wp_admin_bar->add_node(
					array(
						'parent' => 'invites',
						'id'     => 'friendship-' . bp_get_friend_friendship_id(),
						'title'  => $title,
						'meta'   => array(
							'class' => 'nav-content-item nav-friendship-request',
						),
					)
				);
			}
		} else {
			// The user has no friend requests
			$wp_admin_bar->add_node(
				array(
					'parent' => 'invites',
					'id'     => 'friend-requests-none',
					'title'  => '<div class="row"><div class="col-sm-24"><p>No new friendship requests.</p></div></div>',
					'meta'   => array(
						'class' => 'nav-no-items nav-content-item',
					),
				)
			);
		}

		/**
		 * INVITATIONS
		 */

				$title = 'Invitations';
		if ( ! empty( $invites['groups'] ) ) {
			$title .= '<span class="see-all pull-right"><a class="regular" href="' . trailingslashit( bp_loggedin_user_domain() . bp_get_groups_slug() ) . '/invites">See All Invites</a></span>';
		}
		// "Invitations" title
		$wp_admin_bar->add_node(
			array(
				'parent' => 'invites',
				'id'     => 'invitations-title',
				'title'  => $title,
				'meta'   => array(
					'class' => 'submenu-title bold',
				),
			)
		);

		$groups_args = array(
			'type'    => 'invites',
			'user_id' => bp_loggedin_user_id(),
		);

		if ( ! empty( $invites['groups'] ) ) {
			$group_counter = 0;
			foreach ( (array) $invites['groups'] as $group ) {
				if ( $group_counter < 3 ) {
					// avatar
					$title = '<div class="row"><div class="col-sm-6"><div class="item-avatar"><a href="' . bp_get_group_permalink( $group ) . '"><img class="img-responsive" src ="' . bp_core_fetch_avatar(
						array(
							'item_id' => $group->id,
							'object'  => 'group',
							'type'    => 'full',
							'html'    => false,
						)
					) . '" alt="Profile picture of ' . stripslashes( $group->name ) . '"/></a></div></div>';

					// name link
					$title .= '<div class="col-sm-18"><p class="item-title"><a class="bold" href="' . bp_get_group_permalink( $group ) . '">' . stripslashes( $group->name ) . '</a></p>';

					// accept/reject buttons
					$title .= '<p class="actions clearfix"><a class="btn btn-primary link-btn accept" href="' . bp_get_group_accept_invite_link( $group ) . '">' . __( 'Accept', 'commons-in-a-box' ) . '</a> &nbsp; <a class="btn btn-default link-btn reject" href="' . bp_get_group_reject_invite_link( $group ) . '">' . __( 'Reject', 'commons-in-a-box' ) . '</a></p></div></div>';

					$wp_admin_bar->add_node(
						array(
							'parent' => 'invites',
							'id'     => 'invitation-' . $group->id,
							'title'  => $title,
							'meta'   => array(
								'class' => 'nav-content-item nav-invitation',
							),
						)
					);
				}

				$group_counter++;
			}
		} else {
			// The user has no group invites
			$wp_admin_bar->add_node(
				array(
					'parent' => 'invites',
					'id'     => 'group-invites-none',
					'title'  => '<div class="row"><div class="col-sm-24"><p>No new invitations.</p></div></div>',
					'meta'   => array(
						'class' => 'nav-no-items nav-content-item',
					),
				)
			);
		}

	}

	/**
	 * Add the Messages menu
	 */
	public function add_messages_menu( $wp_admin_bar ) {
		if ( ! bp_is_active( 'messages' ) ) {
			return;
		}

		$total_count = openlab_admin_bar_counts( bp_get_total_unread_messages_count(), ' sub-count' );

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'messages',
				'title' => '<span class="toolbar-item-icon fa fa-envelope" aria-hidden="true"></span><span class="sr-only">Messages</span>' . $total_count,
				'meta'  => array(
					'class'    => 'hidden-xs',
					'tabindex' => 0,
				),
			)
		);

		// Only show the first 5
		$messages_counter = 0;

		$messages_args = array(
			'type' => 'unread',
		);
		if ( bp_has_message_threads( $messages_args ) ) {
			global $messages_template;

			while ( bp_message_threads() ) {
				bp_message_thread();

				if ( $messages_counter < 5 ) {
					// avatar
					$title = '<div class="row"><div class="col-sm-6"><div class="item-avatar"><a href="' . bp_core_get_user_domain( $messages_template->thread->last_sender_id ) . '"><img class="img-responsive" src ="' . bp_core_fetch_avatar(
						array(
							'item_id' => $messages_template->thread->last_sender_id,
							'object'  => 'member',
							'type'    => 'full',
							'html'    => false,
						)
					) . '" alt="Profile picture of ' . $messages_template->thread->last_sender_id . '"/></a></div></div>';

					// subject
					$title .= '<div class="col-sm-18"><p class="item"><a class="bold" href="' . bp_get_message_thread_view_link() . '">' . bp_create_excerpt( bp_get_message_thread_subject(), 30 ) . '</a>';

					// last sender
					$title .= '<span class="last-sender"><a href="' . bp_core_get_user_domain( $messages_template->thread->last_sender_id ) . '">' . bp_core_get_user_displayname( $messages_template->thread->last_sender_id ) . '</a></span></p>';

					// date and time
					$title .= '<p class="message-excerpt">' . bp_format_time( strtotime( $messages_template->thread->last_message_date ) ) . '<br />';

					// Message excerpt
					$title .= wp_strip_all_tags( bp_create_excerpt( $messages_template->thread->last_message_content, 75 ) ) . ' <a class="message-excerpt-see-more" href="' . bp_get_message_thread_view_link() . '">' . __( 'See More', 'commons-in-a-box' ) . '<span class="sr-only">' . bp_create_excerpt( bp_get_message_thread_subject(), 30 ) . '</span></a></p></div></div>';

					$wp_admin_bar->add_node(
						array(
							'parent' => 'messages',
							'id'     => 'message-' . bp_get_message_thread_id(),
							'title'  => $title,
							'meta'   => array(
								'class' => 'nav-content-item nav-message',
							),
						)
					);
				}

				$messages_counter++;

			}
		} else {
			// The user has no unread messages
			$wp_admin_bar->add_node(
				array(
					'parent' => 'messages',
					'id'     => 'messages-none',
					'title'  => '<div class="row"><div class="col-sm-24"><p>No new messages.</p></div></div>',
					'meta'   => array(
						'class' => 'nav-content-item nav-no-items',
					),
				)
			);
		}

		// "Go to Inbox" Makes sense that users should always see this
		$wp_admin_bar->add_node(
			array(
				'parent' => 'messages',
				'id'     => 'messages-more',
				'title'  => '<span class="see-all">See All Messages</span>',
				'href'   => trailingslashit( bp_loggedin_user_domain() . bp_get_messages_slug() ),
				'meta'   => array(
					'class' => 'menu-bottom-link',
				),
			)
		);
	}

	/**
	 * Add the Activity menu (My Group activity)
	 */
	public function add_activity_menu( $wp_admin_bar ) {
		$wp_admin_bar->add_menu(
			array(
				'id'    => 'activity',
				'title' => sprintf( '<span class="toolbar-item-name fa fa-bullhorn" aria-hidden="true"></span><span class="sr-only">%s</span>', __( 'Activity', 'commons-in-a-box' ) ),
				'meta'  => array(
					'class'    => 'hidden-xs',
					'tabindex' => 0,
				),
			)
		);

		$activity_args = array(
			'user_id' => bp_loggedin_user_id(),
			'scope'   => 'groups',
			'max'     => 5,
		);

		if ( bp_has_activities( $activity_args ) ) {
			global $activities_template;
			while ( bp_activities() ) {
				bp_the_activity();

				// avatar
				$title = sprintf(
					'<div class="row activity-row"><div class="col-sm-6"><div class="item-avatar"><a href="%s"><img class="img-responsive" src="%s" alt="%s" /></a></div></div>',
					bp_get_activity_user_link(),
					bp_core_fetch_avatar(
						array(
							'item_id' => bp_get_activity_user_id(),
							'object'  => 'member',
							'type'    => 'full',
							'html'    => false,
						)
					),
					sprintf(
						// translators: user name
						__( 'Profile picture of %s', 'commons-in-a-box' ),
						bp_core_get_user_displayname( bp_get_activity_user_id() )
					)
				);

				// action
				$title .= '<div class="col-sm-18">';

				//the things we do...
				$action_output     = '';
				$action_output_raw = $activities_template->activity->action;
				$action_output_ary = explode( '<a', $action_output_raw );
				$count             = 0;

				foreach ( $action_output_ary as $action_redraw ) {
					if ( ! ctype_space( $action_redraw ) ) {
						$class          = 0 === $count ? 'activity-user' : 'activity-action';
						$action_output .= '<a class="' . $class . '"' . $action_redraw;
						$count++;
					}
				}

				$title .= '<p class="item inline-links hyphenate">' . $action_output . '</p>';
				$title .= '<p class="item">' . bp_insert_activity_meta( '' ) . '</p>';
				$title .= '</div></div>';

				$wp_admin_bar->add_node(
					array(
						'parent' => 'activity',
						'id'     => 'activity-' . bp_get_activity_id(),
						'title'  => $title,
						'meta'   => array(
							'class' => 'nav-content-item nav-activity',
						),
					)
				);
			}
		}

		$link = trailingslashit( bp_loggedin_user_domain() . bp_get_activity_slug() );
		if ( bp_is_active( 'groups' ) ) {
			$link .= trailingslashit( bp_get_groups_slug() );
		}

		// "Go to Inbox" Makes sense that users should always see this
		$wp_admin_bar->add_node(
			array(
				'parent' => 'activity',
				'id'     => 'activity-more',
				'title'  => '<span class="see-all">See All Activity</span>',
				'href'   => $link,
				'meta'   => array(
					'class' => 'menu-bottom-link exit',
				),
			)
		);
	}

	public function openlab_custom_admin_bar_site_menu( $wp_admin_bar ) {
		// Don't show for logged out users.
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Show only when the user is a member of this site, or they're a super admin.
		if ( ! is_user_member_of_blog() && ! is_super_admin() ) {
			return;
		}

		$blogname = get_bloginfo( 'name' );

		if ( empty( $blogname ) ) {
			$blogname = preg_replace( '#^(https?://)?(www.)?#', '', get_home_url() );
		}

		if ( is_network_admin() ) {
			// translators: Site name
			$blogname = sprintf( esc_html__( 'Network Admin: %s', 'commons-in-a-box' ), esc_html( get_current_site()->site_name ) );
		} elseif ( is_user_admin() ) {
			// translators: Network main site name
			$blogname = sprintf( esc_html__( 'Global Dashboard: %s', 'commons-in-a-box' ), esc_html( get_current_site()->site_name ) );
		}

		$title       = wp_html_excerpt( $blogname, 40, '&hellip;' );
		$title_short = wp_html_excerpt( $blogname, 15, '&hellip;' );

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'site-name',
				'title' => '<span class="hidden-sm hidden-md">' . $title . ' <span class="fa fa-caret-down" aria-hidden="true"></span></span><span class="hidden-sm visible-md">' . $title_short . ' <span class="fa fa-caret-down" aria-hidden="true"></span></span><span class="fa fa-desktop visible-sm" aria-hidden="true"></span><span class="sr-only visible-sm">' . $title . '</span>',
				'href'  => is_admin() ? home_url( '/' ) : admin_url(),
				'meta'  => array(
					'class'    => 'admin-bar-menu hidden-xs',
					'tabindex' => 0,
				),
			)
		);

		// Create submenu items.

		if ( is_admin() ) {
				// Add an option to visit the site.
				$wp_admin_bar->add_menu(
					array(
						'parent' => 'site-name',
						'id'     => 'view-site',
						'title'  => __( 'Visit Site', 'commons-in-a-box' ),
						'href'   => home_url( '/' ),
					)
				);

			if ( is_blog_admin() && is_multisite() && current_user_can( 'manage_sites' ) ) {
					$wp_admin_bar->add_menu(
						array(
							'parent' => 'site-name',
							'id'     => 'edit-site',
							'title'  => __( 'Edit Site', 'commons-in-a-box' ),
							'href'   => network_admin_url( 'site-info.php?id=' . get_current_blog_id() ),
						)
					);
			}
		} else {
			// We're on the front end, link to the Dashboard.
			$wp_admin_bar->add_menu(
				array(
					'parent' => 'site-name',
					'id'     => 'dashboard',
					'title'  => __( 'Dashboard', 'commons-in-a-box' ),
					'href'   => admin_url(),
				)
			);

			// Add the appearance submenu items.
			wp_admin_bar_appearance_menu( $wp_admin_bar );
		}
	}

	public function add_custom_edit_menu( $wp_admin_bar ) {
		global $tag, $wp_the_query;
		$post = get_post();

		if ( is_admin() ) {
			$current_screen = get_current_screen();

			if ( 'post' === $current_screen->base && 'add' !== $current_screen->action && current_user_can( 'read_post', $post->ID ) ) {
				$post_type_object = get_post_type_object( $post->post_type );
				if ( $post_type_object && $post_type_object->public && $post_type_object->show_in_admin_bar ) {
					$wp_admin_bar->add_menu(
						array(
							'id'    => 'view',
							'title' => sprintf( '<span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only">%s</span>', $post_type_object->labels->view_item ),
							'href'  => get_permalink( $post->ID ),
							'meta'  => array(
								'tabindex' => 0,
							),
						)
					);
				}
			} elseif ( 'edit-tags' === $current_screen->base && isset( $tag ) && is_object( $tag ) ) {
				$tax = get_taxonomy( $tag->taxonomy );
				if ( $tax && $tax->public ) {
					$wp_admin_bar->add_menu(
						array(
							'id'    => 'view',
							'title' => sprintf( '<span class="fa fa-eye" aria-hidden="true"></span><span class="sr-only">%s</span>', $tax->labels->view_item ),
							'href'  => get_term_link( $tag ),
							'meta'  => array(
								'tabindex' => 0,
							),
						)
					);
				}
			}
		} else {
			$current_object = $wp_the_query->get_queried_object();

			if ( empty( $current_object ) ) {
				return;
			}

			if ( ! empty( $current_object->post_type ) ) {
				$post_type_object = get_post_type_object( $current_object->post_type );
				if ( $post_type_object
					&& current_user_can( 'edit_post', $current_object->ID )
					&& $post_type_object->show_ui && $post_type_object->show_in_admin_bar ) {
					$wp_admin_bar->add_menu(
						array(
							'id'    => 'edit',
							'title' => sprintf( '<span class="fa fa-pencil" aria-hidden="true"></span><span class="sr-only">%s</span>', esc_html( $post_type_object->labels->edit_item ) ),
							'href'  => get_edit_post_link( $current_object->ID ),
							'meta'  => array(
								'class'    => 'hidden-xs',
								'tabindex' => 0,
							),
						)
					);
				}
			} elseif ( ! empty( $current_object->taxonomy ) ) {
				$tax = get_taxonomy( $current_object->taxonomy );
				if ( $tax
					&& current_user_can( $tax->cap->edit_terms )
					&& $tax->show_ui ) {
					$wp_admin_bar->add_menu(
						array(
							'id'    => 'edit',
							'title' => sprintf( '<span class="fa fa-pencil aria-hidden="true"></span><span class="sr-only">%s</span>', esc_html( $tax->labels->edit_item ) ),
							'href'  => get_edit_term_link( $current_object->term_id, $current_object->taxonomy ),
							'meta'  => array(
								'class'    => 'hidden-xs',
								'tabindex' => 0,
							),
						)
					);
				}
			}
		}
	}

	/**
	 * Cleaning up any plugin addons to the admin bar
	 * @param type $wp_admin_bar
	 */
	public function adminbar_plugin_cleanup( $wp_admin_bar ) {
		global $wp_admin_bar;

		$wp_admin_bar->remove_menu( 'tribe-events' );

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

	public function add_dashboard_link( $wp_admin_bar ) {
		global $bp;

		$current_screen       = new stdClass();
		$current_screen->base = '';

		if ( is_admin() ) {
			$current_screen = get_current_screen();
		}

		if ( current_user_can( 'edit_published_posts' ) && 'my-sites' !== $current_screen->base ) {

			$title = ( is_admin() ? '<span class="ab-icon dashicon-icon dashicons dashicons-admin-home" aria-hidden="true"></span>' : '<span class="ab-icon dashicon-icon dashicons dashicons-dashboard" aria-hidden="true"></span>' );

			$href = ( is_admin() ? get_site_url() : admin_url() );

			$wp_admin_bar->add_menu(
				array(
					'id'    => 'dashboard-link',
					'title' => $title . '<span class="sr-only">' . esc_html__( 'Home', 'commons-in-a-box' ) . '</span>',
					'href'  => $href,
					'meta'  => array(
						'title' => _x( 'Dashboard', 'admin bar menu group label', 'commons-in-a-box' ),
						'class' => 'mobile-no-hover visible-xs',
					),
				)
			);
		}
	}

	public function add_custom_updates_menu( $wp_admin_bar ) {
		$update_data = wp_get_update_data();

		if ( ! $update_data['counts']['total'] ) {
			return;
		}

		$title  = '<span> ' . number_format_i18n( $update_data['counts']['total'] ) . '</span>';
		$title .= '<span class="sr-only">' . $update_data['title'] . '</span>';

		$icon = '<span class="fa fa fa-cogs" aria-hidden="true"></span>';

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'updates',
				'title' => $icon . $title,
				'href'  => network_admin_url( 'update-core.php' ),
				'meta'  => array(
					'title'    => $update_data['title'],
					'class'    => 'mobile-no-hover',
					'tabindex' => 0,
				),
			)
		);
	}

	/**
	 * Custom comments menu
	 * @param type $wp_admin_bar
	 * @return type
	 */
	public function add_custom_comments_menu( $wp_admin_bar ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$awaiting_mod   = wp_count_comments();
		$awaiting_mod   = $awaiting_mod->moderated;
		$awaiting_count = openlab_admin_bar_counts( number_format_i18n( $awaiting_mod ), ' sub-count' );

		// translators: Number of comments awaiting moderation
		$awaiting_title = esc_attr( sprintf( _n( '%s comment awaiting moderation', '%s comments awaiting moderation', $awaiting_mod, 'commons-in-a-box' ), number_format_i18n( $awaiting_mod ) ) );

		$icon = sprintf(
			'<span class="fa fa-comment hidden-xs" aria-hidden="true"></span><span class="ab-icon dashicon-icon visible-xs" aria-hidden="true"></span><span class="sr-only">%s</span>',
			esc_html__( 'Comments', 'commons-in-a-box' )
		);

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'comments',
				'title' => $icon,
				'href'  => admin_url( 'edit-comments.php' ),
				'meta'  => array(
					'title'    => $awaiting_title,
					'class'    => 'mobile-no-hover',
					'tabindex' => 0,
				),
			)
		);
	}

	/**
	 * Remove + > User
	 */
	public function remove_adduser( $wp_admin_bar ) {
		$wp_admin_bar->remove_menu( 'new-user' );
	}

	/**
	 * Add a 'Log Out' link to the far right
	 */
	public function add_logout_item( $wp_admin_bar ) {
		$wp_admin_bar->add_menu(
			array(
				'parent' => 'top-secondary',
				'id'     => 'top-logout',
				'href'   => add_query_arg( 'redirect_to', bp_get_root_domain(), wp_logout_url() ),
				'title'  => 'Log Out',
				'meta'   => array(
					'class' => 'bold',
				),
			)
		);
	}

	public function openlab_custom_my_account_item( $wp_admin_bar ) {

		$user_id      = get_current_user_id();
		$current_user = wp_get_current_user();
		$profile_url  = get_edit_profile_url( $user_id );

		if ( ! $user_id ) {
			return;
		}

		// translators: user name
		$howdy = sprintf( esc_html__( 'Hi, %s', 'commons-in-a-box' ), esc_html( $current_user->display_name ) );

		// translators: user name
		$howdy_short = sprintf( esc_html__( 'Hi, %s', 'commons-in-a-box' ), esc_html( wp_html_excerpt( $current_user->display_name, 15, '&hellip;' ) ) );

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'my-account',
				'parent' => 'top-secondary',
				'title'  => '<span class="visible-lg">' . $howdy . '</span><span class="hidden-lg">' . $howdy_short . '</span>',
				'href'   => $profile_url,
				'meta'   => array(
					'class' => 'hidden-xs',
					'title' => __( 'My Account', 'commons-in-a-box' ),
				),
			)
		);
	}

		/**
		 * Custom account menu
		 * @param type $wp_admin_bar
		 * @return type
		 */
	public function openlab_custom_my_account_menu( $wp_admin_bar ) {
		$user_login   = '';
		$user_id      = get_current_user_id();
		$current_user = wp_get_current_user();
		$profile_url  = get_edit_profile_url( $user_id );

		if ( ! $user_id ) {
			return;
		}

		if ( $current_user->display_name !== $current_user->user_login ) {
			$user_login = "<span class='username'>{$current_user->user_login}</span>";
		}

		// avatar
		$user_info = '<div class="row"><div class="col-sm-8"><div class="item-avatar"><a href="' . $profile_url . '"><img class="img-responsive" src ="' . bp_core_fetch_avatar(
			array(
				'item_id' => $user_id,
				'object'  => 'member',
				'type'    => 'full',
				'html'    => false,
			)
		) . '" alt="Profile picture of ' . $current_user->display_name . '"/></a></div></div>';

		// name link
		$user_info .= '<div class="col-sm-16"><p class="item-title"><span class="display-name bold">' . $current_user->display_name . '</span><a href="' . $profile_url . '">' . $user_login . '</a></p>';

		// accept/reject buttons
		$user_info .= '<p class="actions clearfix inline-links"><a href="' . $profile_url . '">' . __( 'Edit My Profile', 'commons-in-a-box' ) . '</a> | <span class="exit"><a href="' . wp_logout_url() . '">' . __( 'Log Out', 'commons-in-a-box' ) . '</a></span></p></div></div>';

		$wp_admin_bar->add_node(
			array(
				'parent' => 'my-account',
				'id'     => 'user-listing',
				'title'  => $user_info,
				'meta'   => array(
					'class' => 'nav-content-item',
				),
			)
		);
	}

	/**
	 * Fix the logout redirect
	 */
	public function fix_logout_redirect( $wp_admin_bar ) {
		$wp_admin_bar->add_menu(
			array(
				'id'   => 'logout',
				'href' => add_query_arg( 'redirect_to', bp_get_root_domain(), wp_logout_url() ),
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

	public function fix_tabindex( $wp_admin_bar ) {
		$wp_admin_bar->add_menu(
			array(
				'id'   => 'bp-login',
				'meta' => array(
					'tabindex' => 0,
				),
			)
		);

		$signup = $wp_admin_bar->get_node( 'bp-register' );
		if ( $signup ) {
			$wp_admin_bar->add_menu(
				array(
					'id'   => 'bp-register',
					'meta' => array(
						'tabindex' => 0,
					),
				)
			);
		}
	}

	public function body_class( $body_class ) {
		if ( bp_is_root_blog() ) {
			$body_class[] = 'openlab-main';
		} else {
			$body_class[] = 'openlab-member';
		}

		return $body_class;
	}

	public function admin_body_class( $body_class ) {
		if ( bp_is_root_blog() ) {
			$body_class .= ' openlab-main ';
		} else {
			$body_class .= ' openlab-member ';
		}

		return $body_class;
	}

	public function enqueue_styles() {
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

	public function adminbar_special_body_class( $classes ) {

		$classes[] = 'adminbar-special';

		return $classes;

	}

	public function admin_bar_html_update() {
		?>

		<style type="text/css" media="screen">
			html { margin-top: 0px !important; }
			* html body { margin-top: 0px !important; }
			@media screen and ( max-width: 782px ) {
					html { margin-top: 0px !important; }
					* html body { margin-top: 0px !important; }
			}
		</style>

		<?php
	}

	public function admin_bar_group_sites_html_update() {
		?>

		<style type="text/css" media="screen">
			html { margin-top: 0px !important; }
			* html body { margin-top: 0px !important; }
			@media screen and ( max-width: 782px ) {
					html { margin-top: 0px !important; }
					* html body { margin-top: 0px !important; }
			}
		</style>

		<?php
	}

	public function admin_bar_padding_in_admin() {
		?>

			<style type="text/css" media="screen">
					html.wp-toolbar {
						padding-top: 0;
					}
					html.wp-toolbar #wpcontent,
					html.wp-toolbar #adminmenuwrap{
							padding-top: 80px;
						}
						@media (max-width: 767px){
									html.wp-toolbar #wpcontent,
									html.wp-toolbar #adminmenuwrap{
										padding-top: 120px;
									}
									html.wp-toolbar #wpbody{
										padding-top: 0;
									}
								}
			</style>

		<?php
	}

	public function groups_sites_fix_for_mobile() {
		?>

			<meta name="viewport" content="width=device-width">

		<?php
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
