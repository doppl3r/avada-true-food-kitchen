(function($) {
	'use strict';
	
	// Add toggle function for ".tfk-list"
	$(document).on('click', '.tfk-list [aria-selected]', function(e) {
		e.preventDefault();
		var attr = 'aria-selected';
		var ariaSelected = $(this).attr(attr);
		$(this).attr(attr, (ariaSelected == 'false' ? true : false)); // toggle true/false
	});

	// Add slider functionality
	$(document).ready(function(){
		if ($('.tfk-slider').length) {
			$('.tfk-slider').slick({ dots: true, speed: 1000, fade: true });
		}
	});
	
})(jQuery);