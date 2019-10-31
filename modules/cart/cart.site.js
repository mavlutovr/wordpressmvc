wdpro.ready(20, ($) => {

	$.fn.wdproCartControl = function () {

		$(this).each(function () {

			const $container = $(this);
			const $buttons = $container.find('.js-cart-control-button');
			const $countInput = $container.find('.js-cart-control-count');

			let min = $countInput.attr('data-min');
			if (min) min = Number(min);
			else min = 0;

			const save = (count) => {

				if (count === undefined) {
					count = $countInput.val();
				}

				if (!count || count < 0) count = 0;
				if (count) {

					count = Math.max(min, count);
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
						let newHtml = $(res['html']);
						newHtml.wdproCartControl();
						$container.after(newHtml);
						$container.remove();

						wdpro.trigger('cart_info', res['cart_info']);
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

					save(count);
				}
			});


			$countInput
				.on('change', function () {
					save();
				})
				/*.on('keyup', function (e) {
					if (e.keyCode === wdpro.KEY_ENTER) {
						save();
					}
				})*/;

			wdpro.trigger('cart-control', $container);

		});

		return this;
	};

	$('.js-cart-control').wdproCartControl();


});