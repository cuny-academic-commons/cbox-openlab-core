(function($){
	$(document).ready(function(){
		$(document).ajaxComplete(function(evt,XHR,settings){
			if ( 'undefined' === typeof settings.data ) {
				return;
			}

			if ( -1 === settings.data.indexOf( 'pf_ajax_get_comments' ) ) {
				return;
			}

			hookToReplycontent();
		});
	});

	/*
	 * A few notes:
	 *
	 * - It should be easier to pass custom params (like 'type' etc) to the remoteFilter, otherwise we end up reproducing a lot.
	 * - There seems to be a bug in BP that prevents remote_filter from working - should be remoteFilter
	 * - Inserting puts markup into the textarea. This is not desirable. The markup should be generated on display only.
	 * - The AJAX callback matches all users across the whole network. Probably best to preload with users only from the current site.
	 */
	var hookToReplycontent = function() {
		var mentionsQueryCache = [],
			mentionsItem;

		$('#ef-replycontent').bp_mentions({
			data: PFBPMentions.users,
			delay: 0,

			// Overriding BP's default behavior to avoid additional markup in comment field.
			insertTpl: '@${ID}',

			callbacks: {
				// Disable remote search.
				remoteFilter: function( query, render_view ) {
					mentionsItem = mentionsQueryCache[ query ];
					if ( typeof mentionsItem === 'object' ) {
						render_view( mentionsItem );
						return;
					}
				}
			}
		});
	}
}(jQuery));
