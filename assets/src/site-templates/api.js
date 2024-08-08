/**
 * Internal dependencies
 */
import { buildQueryString } from './util';

const { endpoint, nonce, perPage, categoryMap } = window.SiteTemplatePicker;
const currentGroupType = window.CBOXOL_Group_Create.new_group_type;

export async function getSiteTemplates( category, page = 1, groupId = 0 ) {
	let templateCategory;

	if ( ! category ) {
		templateCategory = [ 0 ]
		for ( var catId in categoryMap ) {
			if ( -1 !== categoryMap[ catId ].indexOf( currentGroupType ) ) {
				templateCategory.push( catId )
			}
		}
	} else {
		templateCategory = category
	}

	const query = buildQueryString( {
		_fields: [ 'id', 'title', 'excerpt', 'featured_media', 'template_category', 'site_id', 'image', 'categories' ],
		template_category: templateCategory,
		group_id: groupId,
		orderby: 'menu_order',
		order: 'asc',
		per_page: Number( perPage ),
		page,
		_wpnonce: nonce,
	} );

	const response = await fetch(
		endpoint + '?' + query,
		{
			credentials: 'include'
		}
	)
	const items = await response.json();

	if ( ! response.ok ) {
		throw new Error( items.message );
	}

	const totalPages = Number( response.headers.get( 'X-WP-TotalPages' ) );

	const templates = items.map( ( item ) => {
		return {
			id: item.id,
			title: item.title.rendered,
			excerpt: item.excerpt.rendered,
			image: item.image,
			categories: item.categories,
			siteId: item.site_id
		};
	} );

	return {
		templates,
		prev: page > 1 ? page - 1 : null,
		next: totalPages > page ? page + 1 : null,
	};
}
