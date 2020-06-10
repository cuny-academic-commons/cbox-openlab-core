(function($){
	$( document ).ready(
		function() {
			$footer = $( '#openlab-footer' );
			$( '#content' ).height( $footer.offset().top + 200 );
		}
	);
}(jQuery))
