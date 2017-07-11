<?php

namespace CBOX\OL;

class AcademicUnitType {
	protected $data = array(
		'group_types' => array(),
		'member_types' => array(),
		'name' => null,
		'parent' => null,
		'slug' => null,
		'wp_post_id' => null,
	);

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->data['name'];
	}

	/**
	 * Get slug.
	 *
	 * @return string
	 */
	public function get_parent() {
		return (int) $this->data['parent'];
	}

	/**
	 * Get slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->data['slug'];
	}

	/**
	 * Get term ID.
	 *
	 * @return int
	 */
	public function get_wp_term_id() {
		return (int) $this->data['wp_term_id'];
	}

	/**
	 * Get group types.
	 *
	 * @return array
	 */
	public function get_group_types() {
		$group_types = array();
		foreach ( $this->data['group_types'] as $gt ) {
			$group_type = cboxol_get_group_type( $gt );
			if ( ! is_wp_error( $group_type ) ) {
				$group_types[ $gt ] = $group_type;
			}
		}

		return $group_types;
	}

	/**
	 * Get member types.
	 *
	 * @return array
	 */
	public function get_member_types() {
		$member_types = array();
		foreach ( $this->data['member_types'] as $mt ) {
			$member_type = cboxol_get_member_type( $mt );
			if ( ! is_wp_error( $member_type ) ) {
				$member_types[ $mt ] = $member_type;
			}
		}

		return $member_types;
	}

	/**
	 * Get post ID.
	 *
	 * @return int
	 */
	public function get_wp_post_id() {
		return (int) $this->data['wp_post_id'];
	}

	public static function get_dummy() {
		return new self();
	}

	public function get_for_endpoint() {
		$retval = array(
			'name' => $this->get_name(),
			'parent' => $this->get_parent(),
			'slug' => $this->get_slug(),
			'settings' => array(
			),

			// @todo
			'units' => array(
				array(
					'slug' => 'foo',
					'label' => 'Foo',
				),
				array(
					'slug' => 'bar',
					'label' => 'Bar',
				),
			),
			'id' => $this->get_wp_post_id(),

			'canBeDeleted' => true,
			'isCollapsed' => true,
			'isEnabled' => true,
			'isLoading' => false,
			'isModified' => false,
		);

		$group_types = $this->get_group_types();
		foreach ( $group_types as $group_type ) {
			$retval['groupTypes'][] = $group_type->get_slug();
		}

		$member_types = $this->get_member_types();
		foreach ( $member_types as $member_type ) {
			$retval['memberTypes'][] = $member_type->get_slug();
		}

		return $retval;
	}
}
