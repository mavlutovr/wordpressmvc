(function ($) {

	wdpro.callback = {

		/**
		 * Открывает окошко с формой заказа обратного звонка
		 */
		openWindow: function (params) {

			params = wdpro.extend({
				title: 'Заказ обратного звонка',
				content: 'Загрузка...',
				substrate: true
			}, params);

			var dialog = new wdpro.dialogs.Dialog(params);
			dialog.show();

			wdpro.yandexMetrika('reachGoal', 'callback--open');

			wdpro.callback.getForm(function (form) {

				form.on('startFill', () => {
					wdpro.yandexMetrikaGoal('callback--start-fill');
				});


				form.on('trySend', () => {
					wdpro.yandexMetrikaGoal('callback--try-to-send');
				});

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

							wdpro.yandexMetrika('reachGoal', 'callback--send');
						}
					);
				});
			});
		},


		/**
		 * Устанавливает кастомную форму
		 *
		 * @param form {wdpro.forms.Form} Форма
		 */
		setForm: function (form) {
			this.form = form;
		},


		/**
		 * Возвращает форму заказа обратного звонка
		 *
		 * Можно переопределять, например, в скриптах темы
		 *
		 * @param Return {function} Каллбэк, в который отправлять форму
		 */
		getForm: function (Return) {

			// if (this.form) {
			// 	Return(this.form);
			// 	return true;
			// }

			var form = new wdpro.forms.Form();

			form.add({
				'name': 'name',
				'center': 'Ваше имя',
				'*': true
			},{
				'name': 'phone',
				'center': 'Телефон',
				'class': 'js-phone-simple-mask',
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
		},


		initHtmlForm: function ($form) {

			$form.on('submit', (e) => {

				const $button = $form.find('input[type="submit"], button[type="submit"]');
				const $inputs = $form.find('input[type="tel"], input[type="text"]');
				const data = $form.serializeObject();

				$button.loading();


				// Отправка на сервер
				wdpro.ajax(
					'callback-form',
					data,


					// Ответ сервера
					(res) => {
						$button.loadingStop();
						$inputs.val('');

						if (res.error) {
							alert(res.error);
							return false;
						}

						wdpro.dialogs.open({
							//substrate: true,
							content: res.message,
							title: res.title
						});
					}
				);

				e.preventDefault();
				return false;
			});
		}
	};


	$(document).ready(function () {

		$('#js-callback-button').click(function () {

			wdpro.callback.openWindow();
		});
	});


})(jQuery);
