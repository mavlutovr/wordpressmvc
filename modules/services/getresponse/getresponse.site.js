wdpro.ready(function ($) {

	// Форма подписки
	/*$('#js-getresponse-form').wdpro_each(function (form) {
		var nameInput = form.find('.js-getresponse-name');
		var emailInput = form.find('.js-getresponse-email');

		var updateName = function () {
			var email = emailInput.val();
			email = email.toLocaleLowerCase();
			if (email != emailInput.val())
				emailInput.val(email);
			nameInput.val(emailInput.val());
		};

		emailInput.on('change', updateName).on('keyup', updateName);
		form.on('submit', updateName);
	});*/
	
	wdpro.forms.onForm('getresponse-form', function (form) {
		
		form.ajax(function (data) {
			
			wdpro.ajax(
				{
					'action': 'getresponse-subscribe',
					'data': data
				},
				function (result) {
					
					form.loadingStop();
					
					if (result['error']) {
						form.showErrorMessage(result['error']);
					}
					
					else {
						if (result['url']) {
							window.location = result['url'];
						}
					}
				}
			);
		});
	});
});