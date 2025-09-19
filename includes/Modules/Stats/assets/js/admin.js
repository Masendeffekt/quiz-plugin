( function( $ ) {
	"use strict";

	$( document ).ready( function() {
		$( '.wp-quiz-datepicker' ).datepicker({
			dateFormat: 'yy-mm-dd',
			beforeShow : function(){
				$( '#ui-datepicker-div' ).wrap( '<div class="wp-quiz-datepicker-wrapper"></div>' );
			}
		});
	});
})( jQuery );
