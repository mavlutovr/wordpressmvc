(function ($) {
	$(document).ready(function () {

		$('.js-wdpro-sorting').each(function () {

			var source = $(this);

			// Сортировка
			var table = source.closest('table');
			if (table.is('.js-sortable-inited')) return false;
			table.addClass('js-sortable-inited');
			var container = table.children('#the-list');
			if (!container.length) {
				container = table;
			}
			var numbers = container.find('.js-wdpro-sorting-number');
			var rows = numbers.closest('tr');
			var forward = true;

			// Определяем направление сортировки
			var lastNumber;
			numbers.each(function () {
				var number = Number($(this).text());

				if (lastNumber) {
					if (number < lastNumber) {
						forward = false;
					}
				}

				lastNumber = number;
			});

			container.sortable({
				'axis': 'y',
				'forceHelperSize': true,
				'forcePlaceholderSize': true,
				'handle': '.js-wdpro-sorting-number',
				'items': rows,
				'placeholder': "sortable-placeholder",
				//'helper': "clone",
				'_':'_',

				'start': function () {
					var width = rows.width();
					console.log('width', width);
					rows.width('100%');
				},

				'update': function (event) {

					var rowReplaced = $(event['toElement']);

					// В зависимости от направления сортировки указываем разные prev и next
					var up = rowReplaced.prev().find('.js-wdpro-sorting-number').attr('data-id');
					var down = rowReplaced.next().find('.js-wdpro-sorting-number').attr('data-id');

					var post = {
						'change': {
							'row': rowReplaced.find('.js-wdpro-sorting-number').attr('data-id'),
							'prev': forward ? up : down,
							'next': forward ? down : up
						},
						'get': wdpro.parseQueryString()
					};

					rowReplaced.loading();

					wdpro.ajax('wdpro_sorting', post, function (ret) {

						wdpro.each(ret['update'], function (sorting, id) {
							numbers.filter('[data-id="'+id+'"]').text(sorting);
						});

						rowReplaced.loadingStop();
					});
				}
			})
		});

	});
})(jQuery);