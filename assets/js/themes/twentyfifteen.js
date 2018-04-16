(function($){
	setTimeout(function(){
		// Necessary to fool Twenty Fifteen's scroll detection.
		if ( document.getElementById('sidebar').clientHeight > document.getElementById('primary').clientHeight ) {
			document.getElementById('sidebar').style.position = 'relative';

		} else {
			document.getElementById('sidebar').style.position = 'relative';
			document.getElementById('sidebar').style.height = document.getElementById('primary').clientHeight + 'px';
		console.log(document.getElementById('sidebar').clientHeight);
		console.log(document.getElementById('primary').clientHeight);
		}
	},1000);

	$(document).ready( function() {
		var $sidebar, $footer;
		var sidebarBottom, footerTop;
		$sidebar = $( '#sidebar' ).first();
		$footer  = $('#openlab-footer');

		$(window).on( 'scroll.twentyfifteen', function() {
				footerTop = $footer.offset().top;
				sidebarBottom = $sidebar.offset().top + $sidebar.outerHeight(true);

				if ( sidebarBottom > footerTop ) {
					$sidebar.css('position','relative');
				}
			} );
		} );
}(jQuery))
