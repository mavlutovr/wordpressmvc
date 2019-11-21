wdpro.ready(($) => {

	$('.js-cost').wdproCostSpaces();

	$.fn.wdproOrderStatus = function () {

		$(this).each(function () {

			const $container = $(this);
			const $current = $container.find('.js-order-status-current');
			const $list = $container.find('.js-order-status-list');
			const $row = $container.closest('.js-row');
			const key = $row.data('key');

			$list.hide().removeClass('g-hid');

			$current.on('click', function () {
				$list.toggle();
			});

			$list.find('.js-order-statuc-item').on('click', function () {

				$list.hide();
				$current.loading();
				const $new = $(this);


				// Отправка на сервер
				wdpro.ajax(
					'console_order_status',

					{
						'key': key,
						'status': $new.data('status')
					},

					// Ответ сервера
					(res) => {

						$current.loadingStop();

						if (res['html']) {
							const html = $(res.html);
							$container.after(html);
							$container.remove();
							html.wdproOrderStatus();
						}
					}
				);
			});
		});

		return this;
	};

	// Статусы заказов
	$('.js-order-status').wdproOrderStatus();
});