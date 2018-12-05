(function ($) {
	$(document).ready(function () {


		// Сортировка
		var sortable = function (params) {
			var table = params.table;
			var container = params.container;
			var numbers = params.numbers;
			var rows = params.rows;

			console.log('params', params);


			// Сортировка
			if (table.is('.js-sortable-inited')) return false;
			table.addClass('js-sortable-inited');
			if (!container.length) {
				container = table;
			}
			//var rows = numbers.closest('tr');
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
				//'forceHelperSize': true,
				//'forcePlaceholderSize': true,
				'handle': '.js-wdpro-sorting-number',
				'items': rows,
				'placeholder': "sortable-placeholder",
				//'helper': "clone",
				'_':'_',

				/*'start': function () {
					var width = rows.width();
					console.log('width', width);
					rows.width('100%');
				},*/

				'update': function (event, ui) {

					console.log('ui', ui);

					var rowReplaced = $(ui['item']);

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

					console.log('post', post);

					wdpro.ajax('wdpro_sorting', post, function (ret) {

						console.log('ret', ret);

						wdpro.each(ret['update'], function (sorting, id) {
							numbers.filter('[data-id="'+id+'"]').text(sorting);
						});

						rowReplaced.loadingStop();
					});
				}
			});
		};


		// Элементы
		$('.js-wdpro-elements-sorting').each(function () {

			var table = $(this);
			var container = table.children('tbody');
			if (!container.length) container = table;
			var numbers = container.find('.js-wdpro-sorting-number');

			var params = {
				table: table,
				container: container,
				numbers: numbers,
				rows: numbers.closest('.js-row'),
			};

			sortable(params);
		});


		// Страницы
		$('.js-wdpro-sorting').each(function () {

			var source = $(this);

			var table = source.closest('table');
			var container = table.children('tbody');
			var numbers = container.find('.js-wdpro-sorting-number');

			var params = {
				table: table,
				container: container,
				numbers: numbers,
				rows: numbers.closest('.iedit')
			};

			sortable(params);

		});

	});
})(jQuery);