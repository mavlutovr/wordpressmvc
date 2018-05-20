(function ($) {

	$(document).ready(function () {

		$('.js-scrollto-anchor').click(function () {

			var anchor = $(this).attr('href');
			anchor = anchor.replace('#', '');

			var target = $('#'+anchor);
			if (!target.length) {
				target = $('a[name="'+anchor+'"]');
			}
			if (target.length) {
				$(document).scrollTo(target);
			}

			return false;
		});
	});

})(jQuery);