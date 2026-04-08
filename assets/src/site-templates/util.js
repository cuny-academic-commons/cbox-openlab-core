/**
 * Generates URL-encoded query string using input query data.
 *
 * It is intended to behave equivalent as PHP's `http_build_query`, configured
 * with encoding type PHP_QUERY_RFC3986 (spaces as `%20`).
 *
 * @url @wordpress/url
 * @example
 * ```js
 * const queryString = buildQueryString( {
 *    simple: 'is ok',
 *    arrays: [ 'are', 'fine', 'too' ],
 *    objects: {
 *       evenNested: {
 *          ok: 'yes',
 *       },
 *    },
 * } );
 * // "simple=is%20ok&arrays%5B0%5D=are&arrays%5B1%5D=fine&arrays%5B2%5D=too&objects%5BevenNested%5D%5Bok%5D=yes"
 * ```
 *
 * @param {Record<string,*>} data Data to encode.
 *
 * @return {string} Query string.
 */
export function buildQueryString( data ) {
	let string = '';

	const stack = Object.entries( data );

	let pair;
	while ( ( pair = stack.shift() ) ) {
		let [ key, value ] = pair;

		// Support building deeply nested data, from array or object values.
		const hasNestedData =
			Array.isArray( value ) || ( value && value.constructor === Object );

		if ( hasNestedData ) {
			// Push array or object values onto the stack as composed of their
			// original key and nested index or key, retaining order by a
			// combination of Array#reverse and Array#unshift onto the stack.
			const valuePairs = Object.entries( value ).reverse();
			for ( const [ member, memberValue ] of valuePairs ) {
				stack.unshift( [ `${ key }[${ member }]`, memberValue ] );
			}
		} else if ( value !== undefined ) {
			// Null is treated as special case, equivalent to empty string.
			if ( value === null ) {
				value = '';
			}

			string +=
				'&' + [ key, value ].map( encodeURIComponent ).join( '=' );
		}
	}

	// Loop will concatenate with leading `&`, but it's only expected for all
	// but the first query parameter. This strips the leading `&`, while still
	// accounting for the case that the string may in-fact be empty.
	return string.substr( 1 );
}

/**
 * Processes HTML content to make anchor links open in a new window.
 *
 * Adds target="_blank" and rel="noopener noreferrer" to all anchor elements,
 * and appends a "new window" icon after each link.
 *
 * @param {string} html The HTML content to process.
 * @return {string} The processed HTML with modified links.
 */
export function processDescriptionLinks( html ) {
	if ( ! html ) {
		return html;
	}

	const container = document.createElement( 'div' );
	container.innerHTML = html;

	const links = container.querySelectorAll( 'a' );
	const newWindowIcon = `<svg class="site-template-new-window-icon" aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>`;

	links.forEach( ( link ) => {
		link.setAttribute( 'target', '_blank' );
		link.setAttribute( 'rel', 'noopener noreferrer' );

		// Add screen reader text for accessibility.
		const srText = document.createElement( 'span' );
		srText.className = 'screen-reader-text';
		srText.textContent = ' (opens in a new tab)';
		link.appendChild( srText );

		// Insert the icon after the link.
		link.insertAdjacentHTML( 'afterend', newWindowIcon );
	} );

	return container.innerHTML;
}
