(function ($) {



	$(document).ready(function () {
		
		// Формы
		$('.js-wdpro-form').wdproForm();
		wdpro.on('content', function (content) {
			content.find('.js-wdpro-form').wdproForm();
		});
		
	});
})(jQuery);