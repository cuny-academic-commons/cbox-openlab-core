<?php

namespace CBOX\OL;

class MemberType {
	protected $data = array(
		'name' => '',
		'description' => '',
		'labels' => array(),
	);

	public function get_labels() {
		return $this->data['labels'];
	}

	public static function get_instance_from_wp_post( \WP_Post $post ) {
		$type = new self();

		$type->set_name( $post->post_title );
		$type->set_description( $post->post_content );

		$saved_labels = get_post_meta( $post->ID, 'cboxol_member_type_labels', true );
		if ( empty( $saved_labels ) ) {
			$saved_labels = array();
		}

		foreach ( self::get_label_types() as $label_type => $label_labels ) {
			if ( isset( $saved_labels[ $label_type ] ) ) {
				$label_labels['value'] = $saved_labels[ $label_type ];
			}

			$type->set_label( $label_type, $label_labels );
		}

		return $type;
	}

	protected function set_name( $name ) {
		$this->data['name'] = $name;
	}

	protected function set_description( $description ) {
		$this->data['description'] = $description;
	}

	protected function set_label( $label_type, $label ) {
		$this->data['labels'][ $label_type ] = $label;
	}

	protected static function get_label_types() {
		return array(
			'singular' => array(
				'label' => _x( 'Singular', 'Member Type singular label', 'cbox-openlab-core' ),
				'description' => __( 'Used wherever a specific member\'s Type is mentioned, such as the User Edit interface.', 'cbox-openlab-core' ),
				'value' => '',
			),
			'plural' => array(
				'label' => _x( 'Plural', 'Member Type plural label', 'cbox-openlab-core' ),
				'description' => __( 'Used in directory titles.', 'cbox-openlab-core' ),
				'value' => '',
			),
		);
	}
}
