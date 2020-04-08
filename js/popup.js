(function($) {
	'use strict';

	// Run when document is ready
	$(document).ready(function(){
        var cookie = $('[data-cookie]').attr('data-cookie');
		var endDate = $('[data-expires]').attr('data-expires');
        var currentDate = new Date().setHours(0, 0, 0, 0);

        // Format scheduled date
        if (endDate == null) endDate = '9999-12-31';
		endDate = new Date(endDate);

		// Check if within date range
		if (currentDate < endDate) {
			// Check cookie for cookie attribute
			if (Cookies.get(cookie) != 'false' || cookie == null) {
				// Add popup
				$('html').addClass('disable');
                $('.popup-alert').addClass('active');
                $('.close-popup').focus();

				// Add popup functionality (and set cookie)
				$('.close-popup').on('click', function(e){
					e.preventDefault();
					$('html').removeClass('disable');
					$(this).closest('.popup-alert').removeClass('active');
					if (cookie != null) Cookies.set(cookie, 'false', { expires: 1 })
				});
			}
		}
	});
})(jQuery);