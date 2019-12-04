(function ($) {



	$(document).ready(function () {
		
		// Формы
		if ($.fn.wdproForm) {
			$('.js-wdpro-form').wdproForm();
			wdpro.on('content', function (content) {
				content.find('.js-wdpro-form').wdproForm();
			});
		}



	});
})(jQuery);