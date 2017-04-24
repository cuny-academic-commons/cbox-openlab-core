<?php

/**
 * Get a group type by group id
 *
 * @param int $group_id
 * @return string
 */
function openlab_get_group_type( $group_id = 0 ) {
	if ( ! bp_is_active( 'groups' ) ) {
		return '';
	}

	if ( !$group_id ) {
		$group_id = openlab_fallback_group();
	}

	$group_type = groups_get_groupmeta( $group_id, 'wds_group_type' );

	/*
	if ( !in_array( $group_type, openlab_group_types() ) ) {
		$group_type = 'group';
	}
	*/

	return $group_type;
}

function openlab_is_group_type( $group_id = 0, $type = 'group' ) {
	return $type == openlab_get_group_type( $group_id );
}

function openlab_is_course( $group_id = 0 ) { return openlab_is_group_type( $group_id, 'course' ); }

function openlab_is_project( $group_id = 0 ) { return openlab_is_group_type( $group_id, 'project' ); }

function openlab_is_portfolio( $group_id = 0 ) { return openlab_is_group_type( $group_id, 'portfolio' ); }

function openlab_is_club( $group_id = 0 ) { return openlab_is_group_type( $group_id, 'club' ); }

// @todo abstract with Member Types?
function cboxol_grouptypes_admin_page() {
	wp_enqueue_script( 'cbox-ol-app' );

	$types = cboxol_get_member_types( array(
		'enabled' => null,
	) );

	$type_data = array();
	foreach ( $types as $type ) {
		$type_data[ $type->get_slug() ] = $type->get_for_endpoint();
	}

	$dummy = \CBOX\OL\MemberType::get_dummy();
	$dummy_data = $dummy->get_for_endpoint();

	?>
	<div class="wrap cboxol-admin-wrap">
		<?php cboxol_admin_header( 'group-settings', 'types' ); ?>

		<div class="cboxol-admin-content">
			<?php /* @todo */ ?>
			<p>Group Types are et officia pariatur tenetur autem. Libero illum quaerat cum iusto non. Voluptatem dignissimos et suscipit nesciunt eum nobis deleniti maiores. Dolor voluptatem qui aut maiores ut. Veritatis rerum velit aut laborum et ut ut. Aut quo nostrum assumenda dolorem quibusdam deleniti consequatur doloremque.</p>

			<script type="text/javascript">
				var CBOXOL_ObjectType = 'member';
				var CBOXOL_Types = <?php echo json_encode( $type_data ); ?>;
				var CBOXOL_Dummy = <?php echo json_encode( $dummy_data ); ?>;
			</script>

			<div id="cboxol-types-admin">
				<cboxol-types-admin object="member"></cboxol-types-admin>
			</div>
		</div>
	</div>
	<?php
}
