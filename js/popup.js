(function($) {
	'use strict';

	// Run when document is ready
	$(document).ready(function(){
		// If HTML script exists
		if ($('.popup-alert').length > 0) {
			var cookie = $('[data-cookie]').attr('data-cookie');
			var cookieSleep = $('[data-cookie-sleep]').attr('data-cookie-sleep');
			if (cookieSleep == null) cookieSleep = 0; // Default no sleep
	
			// Check cookie for cookie attribute
			if (Cookies.get(cookie) != 'false' || cookie == null) {
				// Add popup
				$('html').addClass('disable');
				$('.popup-alert').addClass('active');
				$('.close-popup').focus();

				// Add popup functionality (and set cookie)
				$('.popup-alert a').on('click', function(e){
					e.preventDefault();
					var href = $(this).attr('href');
					$('html').removeClass('disable');
					$(this).closest('.popup-alert').removeClass('active');
					
					// Set cookie value
					if (cookie != null) Cookies.set(cookie, 'false', { expires: parseInt(cookieSleep) });

					// Redirect page
					if (href != '#') { window.location.href = href; }
				});
			}
		}
	});
})(jQuery);