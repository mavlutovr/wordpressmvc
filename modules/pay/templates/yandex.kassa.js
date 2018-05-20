wdpro.ready(function ($) {
	
	false && $('#js-yandex-kassa').wdpro_each(function (form) {
		
		var json = form.find('.js-data').text();
		var data = wdpro.parseJSON(json);
		
		var methodInput = form.find('.js-method').val('');
		
		form.on('submit', function () {
			
			// Типы оплаты установлен, запускаем форму оплаты
			if (methodInput.val()) {
				
				return true;
			}

			// Тип оплаты еще не был установлен
			else {

				var methodsN = wdpro.objectLength(data['methods']);

				// Методы не указаны
				if (!methodsN) {

					methodInput.val('AC');

					return true;
				}

				// Указан 1 метод
				else if (methodsN == 1) {

					wdpro.each(data['methods'], function (methodName, methodKey) {
						methodInput.val(methodKey);
					});

					return true;
				}

				// Список методов
				var methodForm = new wdpro.forms.Form();
				methodForm.add({
					'name': 'method',
					//'top': 'Выберите способ оплаты',
					'*': true,
					'type': 'select',
					'options': data['methods']
				});
				methodForm.add({
					'type': 'submit',
					'value': 'Перейти к оплате'
				});

				// Отправка
				methodForm.ajax(function (data) {

					methodInput.val(data['method']);
					dialog.close();
					form.find('input[type="submit"]').loading();
					form.submit();
				});

				var dialog = new wdpro.dialogs.Dialog({
					title: 'Выберите способ оплаты',
					'substrate': true
				});

				methodForm.getHtml(function (html) {
					dialog.setContent(html);
				});

				return false;
			}
		});
	});
	
	
	// Методы оплаты
	$('#js-yandex-kassa-methods').wdpro_each(function (form) {
		
		form.on('submit', function () {
			
			if (form.find('.js-yandex-method:checked').length) {
				
				return true
			}
			
			alert('Пожалуйста, выберите способ оплаты.');
			return false;
		});
	});
});