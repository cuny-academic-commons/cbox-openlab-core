<?php

namespace CBOX\OL;

interface ItemType {
	public function get_slug();
	public function get_name();
	public function get_labels();
	public function get_label( $label_type );
	public function get_is_enabled();
	public function get_order();
	public function get_wp_post_id();
	public function get_can_be_deleted();

	public static function get_instance_from_wp_post( \WP_Post $post );
	public static function get_dummy();

	public function get_for_endpoint();

	public function save();
}
