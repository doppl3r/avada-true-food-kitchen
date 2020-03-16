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
		var slider = $('.tfk-slider');
		if (slider.length) {
			slider.on('init', function(event, slick) {
				// Add pause toggle button
				var slideStateHTML = $('<a href="#" class="slider-toggle" aria-label="pause">pause</a>');
				$(this).addClass('animating');
				$('.slick-dots').append(slideStateHTML);
				slideStateHTML.on('click', function(e) {
					e.preventDefault();
					var slideState = slideStateHTML.text();
					var slideStateOption = (slideState == "play") ? "pause" : "play";
					slideStateHTML.attr('aria-label', slideStateOption);
					slideStateHTML.text(slideStateOption);
					if (slideState == 'play') slider.slick('slickPlay');
					else slider.slick('slickPause');
				});
			});
			slider.on('beforeChange', function(event, slick){
				slider.removeClass('animating');
				setTimeout(function(){ slider.addClass('animating'); }, 50);
			});
			slider.on('afterChange', function(event, slick){
				
			});
			slider.slick({ autoplay: true, dots: true, speed: 1000, fade: true });
		}
	});
	
})(jQuery);