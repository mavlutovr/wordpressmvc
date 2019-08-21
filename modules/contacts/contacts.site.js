wdpro.ready(function ($) {
	
	wdpro.forms.onForm('contactsBack', function (form) {
		
		form.ajax(function (data) {
			
			wdpro.ajax('contactsBack', data, function (res) {

				if (res['message']) {
					form.showMessage(res['message'], { hideForm: true });
				}

				else if (res['error']) {
					form.showErrorMessage(res['error'], { hideForm: false });
					form.loadingStop();
				}
			});
		});
	});
});