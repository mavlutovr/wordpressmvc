wdpro.ready(function ($) {

	wdpro.forms.onForm('contactsBack', function (form) {

		// Start fill
		form.on('startFill', () => {
			wdpro.yandexMetrikaGoal('contacts--start-fill');
		});


		form.on('trySend', () => {
			wdpro.yandexMetrikaGoal('contacts--try-to-send');
		});


		// Send
		form.ajax(function (data) {

			wdpro.ajax('contactsBack', data, function (res) {

				if (res['message']) {
					form.showMessage(res['message'], { hideForm: true });

					if (res['metrika']) {
						wdpro.yandexMetrikaGoal(res['metrika']);
					}
				}

				else if (res['error']) {
					form.showErrorMessage(res['error'], { hideForm: false });
					form.loadingStop();
				}
			});
		});
	});
});
