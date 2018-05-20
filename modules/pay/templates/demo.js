jQuery(document).ready(function ($) {

	$('#js-pay-demo-confirm-form').each(function () {

		var form = $(this);
		var confirmInput = form.find('.JS_confirm');

		$('#JS_pay_method_demo_cancel').on('click', function () {

			confirmInput.val(0);
			form.submit();
		});
	});
});