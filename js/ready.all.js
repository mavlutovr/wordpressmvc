(function ($) {
	
	$(document).ready(function () {
		
		// Формы
		$('.js-wdpro-form').each(function ()
		{
			var container = $(this);

			// Получаем параметры формы
			var jsonDiv = container.find('.js-params');
			var json = jsonDiv.text();
			var data = wdpro.parseJSON(json);

			// Создаем форму
			var form = new wdpro.forms.Form(data);

			form.getHtml(function (html) {
				container.append(html);
			});
			
			
		});
		
	});
	
})(jQuery);