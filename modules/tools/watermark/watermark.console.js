(function ($) {
	$(document).ready(function () {

		$('#js-watermark-redraw').each(function () {
			var root = $(this);
			var buttonStart = root.find('.js-watermark-button-start');
			var buttonStop = root.find('.js-watermark-button-stop');
			var container = root.find('.js-watermark-container');
			var playMode = false;
			var lastFormClass = null;
			var lastId = 0;

			buttonStart.click(function () {
				start();
			});

			buttonStop.click(function () {
				stop();
			});

			var start = function () {
				playMode = true;
				container.empty();
				step();
				buttonStart.loading();
				buttonStop.show();
			};

			var stop = function () {
				playMode = false;
				buttonStart.loadingStop();
				buttonStop.hide();
				lastId = 0;
				lastFormClass = null;
			};

			var finish = function () {
				container.append('<h3>Завершено</h3>');
				stop();
			};

			// 1 шаг
			var step = function () {

				console.log('Запрос', {
					'lastFormClass': lastFormClass,
					'lastId': lastId
				});

				wdpro.ajax(
					'watermark_redraw_step',
					{
						'lastFormClass': lastFormClass,
						'lastId': lastId
					},
					function (ret) {

						console.log('Ответ', ret);

						if (ret) {
							// Заголовок
							if (lastFormClass != ret['formClass']) {
								container.append('<h3>' + ret['formName'] + '</h3>');
								lastId = 0;
							}

							// Элемент
							else if (ret['element']) {
								container.append('<p>' + ret['element'] + '</p>');
								lastId = ret['id'];
							}
							else {
								finish();
							}

							lastFormClass = ret['formClass'];

							if (playMode) {
								step();
							}
						}

						else {
							finish();
						}
					});
			};
		});

	});
})(jQuery);