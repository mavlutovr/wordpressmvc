wdpro.ready(function ($) {
	$('#js-pay-start').each(function () {

		var form = $(this).find('form').hide();

		var container = $('<div style="display: inline-block"></div>');
		var div = $('<div/>').width(300).height(40).loading();
		container.append(div);
		$(this).after(container);
		div.loading();

		form.submit();

	});
});