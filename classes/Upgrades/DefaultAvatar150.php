<?php
/**
 * Create a user-modifiable default avatar.
 *
 * @package cbox-openlab-core
 *
 * @since 1.5.0
 */

namespace CBOX\OL\Upgrades;

use CBOX\Upgrades\Upgrade;
use CBOX\Upgrades\Upgrade_Item;

/**
 * Create a user-modifiable default avatar.
 */
class DefaultAvatar150 extends Upgrade {
	/**
	 * Internal ID.
	 *
	 * @var string
	 */
	public $id = 'default_avatar_150';

	/**
	 * Flag used for DB saving.
	 *
	 * @var string
	 */
	const FLAG = 'cboxol_150_default_avatar_150';

	/**
	 * Setup method.
	 */
	public function setup() {
		global $wpdb;

		$this->name = __( 'Setting up your default Profile Photo', 'commons-in-a-box' );

		$this->push( new Upgrade_Item( cbox_get_main_site_id(), array( 'site_id' => cbox_get_main_site_id() ) ) );
	}

	/**
	 * Process item handler.
	 *
	 * @param CBOX\Upgrades\Upgrade_Item $item Item.
	 */
	public function process( $item ) {
		$site_id = $item->get_value( 'site_id' );

		switch_to_blog( $site_id );

		$default_avatar_path = CBOXOL_PLUGIN_URL . 'assets/img/default-avatar-full.png';

		$attachment_id = \CBOX\OL\Install::create_attachment( $default_avatar_path );

		set_theme_mod( 'openlab_default_avatar', $attachment_id );

		restore_current_blog();

		return true;
	}

	/**
	 * Mark upgrade as finished.
	 *
	 * @return void
	 */
	public function finish() {
		update_option( static::FLAG, '1' );
	}
}
