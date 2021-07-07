wdpro.ready(function($) {
	
	// Форма
	$('#js-paypal').wdpro_each(function (form) {
		
		var submitButton = form.find('.js-submit');
		var payId = form.attr('data-pay-id');
		var s = form.attr('data-pay-s');

		// Submit
		form.on('submit.paypal', function () {

			submitButton.loading();
			
			wdpro.ajax(
				{
					'action': 'pay_paypal_get_pay_url',
					'payId': payId,
					's': s
				},
				
				function (ob) {
					
					if (ob && ob['url']) {
						
						// console.log(ob);
						
						window.location = ob['url'];
						
						/*form.attr('action', ob['url'])
							.off('submit.paypal')
							.submit()
						;*/
					}
				}
			);
			
			return false;
		});
	});
});