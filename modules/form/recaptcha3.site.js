(function () {

	grecaptcha.ready(function () {

		wdpro.ready(function ($) {


			grecaptcha

				.execute(
					wdpro.reCaptcha3SiteKey,
					{action: 'homepage'}
				)

				.then(function (token) {
					console.log('TOKEN', token);
						$('.js-recaptcha3-input').val(token);

						wdpro.forms.onFormAddedToPage(function (form) {

							form.html.find('.js-recaptcha3-input').val(token);
						});
					}
				);
		});


	});

})();