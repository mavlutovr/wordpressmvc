wdpro.ready(function ($) {
	
	wdpro.forms.onForm('contactsBack', function (form) {
		
		form.ajax(function (data) {
			
			wdpro.ajax('contactsBack', data, function (res) {
				
				form.showMessage(res['message'], { hideForm: true });
			});
		});
	});
});