import { __ } from '@wordpress/i18n';

wp.domReady( function() {
	wp.blocks.registerBlockVariation(
		'core/navigation-link', {
		name: 'openlab-group-profile',
		title: __( 'Group Home Link', 'commons-in-a-box' ),
		description: __( 'A link to the Home page of the site\'s associated group. The text will be changed at the time of clone to the appropriate group type (eg "Project Home").', 'commons-in-a-box' ),
		icon: 'groups',
		attributes: {
			label: __( '[ Group Home ]', 'commons-in-a-box' ),
			url: '#group-home-placeholder',
			kind: 'custom',
			type: 'custom',
			opensInNewTab: false,
			rel: '',
			className: 'openlab-group-profile-link'
		},
		scope: [ 'inserter', 'transform' ],
		isActive: ( blockAttributes ) => {
			return blockAttributes.className &&
				   blockAttributes.className.includes( 'openlab-group-profile-link' );
		}
	} );
} );
