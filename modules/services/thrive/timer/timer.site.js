function countdown_callback(is_promotion) {
	console.log('is_promotion', is_promotion);
	if (true == is_promotion) {
		
	} else {
		wdpro.ready(function ($) {
			
			$('body').addClass('timer-remove');
		});
	}
}


wdpro.ready(function ($) {

	// Скрываемые/показываемые блоки
	(function () {
		var visibleBlocks = $('.js-thrive-timer-visible');
		if (visibleBlocks.length) {

			var timers = {};


			// Обновлялка видимости блоков
			var updateVisible = function (domain, thriveId) {

				var timer = timers[domain][thriveId];

				if (timer.status) {
					if (timer.status['alive']) {
						wdpro.each(timer.blocks, function (block) {

							var blockAlive = block.attr('data-show');

							if (blockAlive == timer.status['alive']) {
								block.show();
							}
							else {
								block.hide();
							}
						});
					}
				}
			};


			visibleBlocks.wdpro_each(function (block) {

				var domain = block.attr('data-domain');
				var thriveId = block.attr('data-thrive-id');

				// Данные по таймеру уже есть
				if (timers[domain] && timers[domain][thriveId]) {

					// Добавляем блок в список блоков таймера
					timers[domain][thriveId].blocks.push(block);
					// Обновляем видимость блоков
					updateVisible(domain, thriveId);
				}


				// Данных по таймеру еще нет
				else {

					timers[domain] = timers[domain] || {};
					timers[domain][thriveId] = {
						callbacks: [],
						blocks: [block]
					};

					// Адрес
					var url = block.attr('data-src');
					url += '?'+$.param({
						'action': 'getStatus',
						'thriveId': thriveId,
						'start': block.attr('data-start')
					});
					console.log(url);

					window.callback = function (data) {
						console.log('window.callback', data);
					};

					$.ajax({
						url: url,
						jsonp: "callback",
						dataType: "jsonp",
						success: function (response) {
							// Созраняем статус
							timers[domain][thriveId].status = response;
							// Обновляем видимость блоков
							updateVisible(domain, thriveId);
						}
					});

					/*var xhr = new XMLHttpRequest;
					xhr.open('GET', url);
					xhr.onload = function () {
						// Созраняем статус
						timers[domain][thriveId].status = JSON.parse(xhr.responseText);
						// Обновляем видимость блоков
						updateVisible(timers[domain][thriveId]);
					};
					xhr.send();*/
					/*$.ajax({
						url: url,
						xhrFields: {
							withCredentials: true
						}
					}).done(function (json) {
						console.log('json', json);
						// Созраняем статус
						timers[domain][thriveId].status = JSON.parse(json);
						// Обновляем видимость блоков
						updateVisible(timers[domain][thriveId]);
					});*/
				}
			});
		}
	})();
});