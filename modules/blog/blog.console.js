(function ($) {
	$(document).ready(function () {

		wdpro.forms.onForm('blog', function (form) {

			var tags = form.find('.js-blog-tags');

			console.log('tags', tags);
		});

	});
})(jQuery);