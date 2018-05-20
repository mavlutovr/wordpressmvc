(function ($) {
	
	wdpro.callback = {

		/**
		 * Открывает окошко с формой заказа обратного звонка
		 */
		openWindow: function () {
			
			var dialog = new wdpro.dialogs.Dialog({
				title: 'Заказ обратного звонка',
				content: 'Загрузка...',
				substrate: true
			});
			dialog.show();
			
			wdpro.callback.getForm(function (form) {

				form.getHtml(function (html) {
					dialog.setContent(html);
				});

				form.ajax(function (data)
				{
					wdpro.ajax(
						'callback-form',

						data,

						function (result) {

							form.loadingStop();

							dialog.setContent(result['message']);
						}
					);
				});
			});
		},


		/**
		 * Возвращает форму заказа обратного звонка
		 * 
		 * Можно переопределять, например, в скриптах темы
		 * 
		 * @param Return {function} Каллбэк, в который отправлять форму
		 */
		getForm: function (Return) {

			var form = new wdpro.forms.Form();
			form.add({
				'name': 'name',
				'top': 'Ваше имя',
				'*': true
			});
			form.add({
				'name': 'phone',
				'top': 'Телефон',
				'*': true
			});
			form.add({
				'type': 'submit',
				'text': 'Отправить'
			});
			
			Return(form);
		}
	};
	
	
	$(document).ready(function () {
		
		$('#js-callback-button').click(function () {
			
			wdpro.callback.openWindow();
		});
	});
	
	
})(jQuery);