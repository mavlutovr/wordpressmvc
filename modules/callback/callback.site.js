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
							if (result['title']) {
								dialog.setTitle(result['title']);
							}

							wdpro.yandexMetrika('reachGoal', 'callback');
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
				'center': 'Ваше имя',
				'*': true
			},{
				'name': 'phone',
				'center': 'Телефон',
				'class': 'js-phone-mask',
				'*': true
			});
			form.add({
				'type': 'submit',
				'text': 'Заказать обратный звонок'
			});
			form.add({
				'type': 'check',
				'right': 'Я даю свое согласие на обработку персональных данных и соглашаюсь с условиями и <a href="/privacy-policy/" target="_blank">политикой конфиденциальности</a>.',
				'required': true,
				'checked': true,
				'containerClass': 'privacy-check-container'
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