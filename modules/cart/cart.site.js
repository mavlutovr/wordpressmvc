wdpro.ready(20, ($) => {

	$.fn.wdproCartControl = function () {

		$(this).each(function () {

			const $container = $(this);
			const $buttons = $container.find('.js-cart-control-button');
			const $countInput = $container.find('.js-cart-control-count');

			let min = $countInput.attr('data-min');
			if (min) min = Number(min);
			else min = 0;

			let step = $countInput.attr('data-step');
			if (step) step = Number(step);
			else step = 0;

			const save = (count, removeConfirmMessage) => {

				if (count === undefined) {
					count = $countInput.val();
					if (count) count = Number(count);
				}

				// Минимальное значение
				if (!count || count < 0) count = 0;
				if (count) {

					count = Math.max(min, count);
				}

				// Шаг
				if (step) {
					// Допустим, 3
					let stepCount = count;
					if (min) stepCount -= min;

					stepCount = Math.round(stepCount / step);
					stepCount *= step;
					if (min) stepCount += min;
					count = stepCount;
				}

				// Подтверждение удаления
				if (!count) {
					if (!removeConfirmMessage) {
						removeConfirmMessage = $countInput.data('remove-confirm');
					}

					if (removeConfirmMessage) {
						if (!confirm(removeConfirmMessage))
							return false;
					}
				}

				$container.loading();
				const key = $container.attr('data-key');


				// Запрос на сервер
				wdpro.ajax(
					'cart_count',
					{
						'key': key,
						'count': count
					},

					// Ответ сервера
					function (res) {
						$container.loadingStop();
						let $newHtml;

						if (count || wdpro.data['currentPostName'] !== 'cart') {
							let $newHtmlWrap = $('<div>'+res['html']+'</div>');
							$newHtml = $newHtmlWrap.children('.js-cart-control');
							$container.after($newHtml);
							$newHtml.wdproCartControl();
						}
						$container.remove();
						$newHtml && $newHtml.trigger('cart-item-change');

						wdpro.trigger('cart-summary-info', res['cartInfo']);
					}
				);
			};


			$buttons.on('click', function () {

				const $button = $(this);

				let count = $button.data('count');
				if (count) {
					count = Number(count);
					save(count);
					return true;
				}

				let delta = $button.data('delta');
				if (delta) {
					delta = Number(delta);

					let count = $countInput.val();
					if (!count || count < 0) count = 0;
					count = Number(count);
					count += delta;

					if (delta < 0 && count < min) {
						count = 0;
					}

					save(count, $button.data('remove-confirm'));
				}
			});


			$countInput
				.on('change', function () {
					save();
				});

			wdpro.trigger('cart-item-init', $container);

		});

		return this;
	};

	$('.js-cart-control').wdproCartControl();


});