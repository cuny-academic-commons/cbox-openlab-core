<?php

namespace CBOX\OL;

use \WP_Error;

class SignupCode {
	protected $data = array(
		'author_id'   => null,
		'code'        => null,
		'group_id'    => null,
		'member_type' => null,
		'wp_post_id'  => null,
	);

	/**
	 * Save to the database.
	 *
	 * @return bool
	 */
	public function save() {
		// @todo prevent dupes
		$post_args = array(
			'post_type'   => 'cboxol_signup_code',
			'post_title'  => $this->get_code(),
			'post_author' => $this->get_author_id(),
			'post_status' => 'publish',
		);

		$wp_post_id = $this->get_wp_post_id();
		if ( $wp_post_id ) {
			$post_args['ID'] = $wp_post_id;

			$saved = wp_update_post( $post_args );
		} else {
			$saved = wp_insert_post( $post_args );
		}

		if ( ! $saved ) {
			// errors?
			return false;
		}

		$wp_post_id = $saved;
		$this->set_wp_post_id( $wp_post_id );

		$post = get_post( $wp_post_id );
		$this->set_author_id( $post->post_author );

		update_post_meta( $wp_post_id, 'cboxol_signup_code_code', $this->get_code() );
		update_post_meta( $wp_post_id, 'cboxol_signup_code_group_id', $this->get_group_id() );
		$member_type = $this->get_member_type();
		if ( ! is_wp_error( $member_type ) ) {
			update_post_meta( $wp_post_id, 'cboxol_signup_code_member_type', $member_type->get_slug() );
		}

		return true;
	}

	public function get_for_endpoint() {
		$retval = array(
			'code'       => $this->get_code(),
			'group'      => array(
				'name' => '',
				'slug' => '',
			),
			'memberType' => array(
				'name' => '',
				'slug' => '',
			),
			'wpPostId'   => $this->get_wp_post_id(),
		);

		$group = $this->get_group();
		if ( $group ) {
			$retval['group']['name'] = $group->name;
			$retval['group']['slug'] = $group->slug;
		}

		$member_type = $this->get_member_type();
		if ( ! is_wp_error( $member_type ) ) {
			$retval['memberType']['name'] = $member_type->get_label( 'singular' );
			$retval['memberType']['slug'] = $member_type->get_slug();
		}

		return $retval;
	}

	public static function get_instance_from_wp_post( \WP_Post $post ) {
		$code = new self();

		$code->set_author_id( $post->post_author );
		$code->set_wp_post_id( $post->ID );

		$code_value = get_post_meta( $post->ID, 'cboxol_signup_code_code', true );
		$code->set_code( $code_value );

		$group_id = get_post_meta( $post->ID, 'cboxol_signup_code_group_id', true );
		$code->set_group_id( $group_id );

		$member_type = get_post_meta( $post->ID, 'cboxol_signup_code_member_type', true );
		$code->set_member_type( $member_type );

		return $code;
	}

	/**
	 * Get author ID.
	 *
	 * @return int
	 */
	public function get_author_id() {
		return (int) $this->data['author_id'];
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function get_code() {
		return $this->data['code'];
	}

	/**
	 * Get group.
	 *
	 * @return BP_Groups_Group|null
	 */
	public function get_group() {
		$group = groups_get_group( $this->get_group_id() );
		if ( ! $group ) {
			return null;
		} else {
			return $group;
		}
	}

	/**
	 * Get group ID.
	 *
	 * @return int
	 */
	public function get_group_id() {
		return (int) $this->data['group_id'];
	}

	/**
	 * Get member type.
	 *
	 * @return \CBOX\OL\MemberType|WP_Error
	 */
	public function get_member_type() {
		return cboxol_get_member_type( $this->data['member_type'] );
	}

	/**
	 * Get WP post ID.
	 *
	 * @return int
	 */
	public function get_wp_post_id() {
		return (int) $this->data['wp_post_id'];
	}

	/**
	 * Set author ID.
	 *
	 * @param int
	 */
	public function set_author_id( $author_id ) {
		$this->data['author_id'] = (int) $author_id;
	}

	/**
	 * Set code.
	 *
	 * @param string
	 */
	public function set_code( $code ) {
		$this->data['code'] = $code;
	}

	/**
	 * Set group ID.
	 *
	 * @param int
	 */
	public function set_group_id( $group_id ) {
		$this->data['group_id'] = (int) $group_id;
	}

	/**
	 * Set member type
	 *
	 * @param string
	 */
	public function set_member_type( $type ) {
		$this->data['member_type'] = $type;
	}

	/**
	 * Set WP post ID.
	 *
	 * @param int
	 */
	public function set_wp_post_id( $wp_post_id ) {
		$this->data['wp_post_id'] = (int) $wp_post_id;
	}
}
