(function ($)
{
	/**
	 * Номер текущего loading
	 * @type {number}
	 */
	var loadingRectId = 1;

	/**
	 * Загрузка поверх блока
	 *
	 * @param [params] настройки
	 * @returns {fn}
	 */
	$.fn.loading = function (params)
	{
		params = $.extend({}, params);

		$(this).each(function ()
		{
			var self = $(this);

			// Увеличиваем ID Loading
			loadingRectId ++;

			// Метка о том, что поле в процессе Loading
			self.data('loading', true);

			var div = $(wdpro.templates.tools.loading());
			div.attr('id', 'loading_rect_'+loadingRectId);
			self.attr('data-loading_id', loadingRectId);

			// Css Класс
			if (!params.css)
			{
				params.css = self.attr('data-loading');
			}
			if (params.css)
			{
				div.addClass(params.css);
			}

			if (params.onClick)
			{
				div.click(params.onClick).css('cursor', 'pointer');
			}

			// Размещение
			// Если это таблица
			if (self.is('td') || self.is('tr'))
			{
				self.closest('table').before(div);
			}
			// Обычный элемент
			else
			{
				self.before(div);
			}

			var bg = div.find('.JS_loading_bg');

			self.css('opacity', 0);

			bg
				.width(self.outerWidth() - 2)
				.height(self.outerHeight() - 2)
			;


			// Отступ сверху
			var targetTop = self.offset().top;
			var loadingTop = div.offset().top;
			if (loadingTop != targetTop)
			{
				var plusTop = targetTop - loadingTop;

				div.css('margin-top', plusTop+'px');
			}


			// Смещение фона
			/*var x = 0;
			 $(this).everyTime(40, function ()
			 {
			 x ++;
			 $(bg).css('background-position', x+'px');
			 });*/
		});

		return this;
	};


	/**
	 * Удаление загрузки
	 */
	$.fn.loadingStop = function ()
	{
		$(this).each(function ()
		{
			var self = $(this);

			self.removeData('loading');
			self.css('opacity', 1);
			//self.stopTime().prev('.JS_loading_rect_over').remove();
			//self.stopTime();

			var id = self.attr('data-loading_id');
			$('#loading_rect_'+id).remove();
		});

		return this;
	};
	
	$.fn.wdpro_tabs = function () {
		
		$(this).wdpro_each(function (container) {
			
			var texts = {};
			var links = {};

			$(container).find('a')
				.wdpro_each(function (a) {
	
					var link = a.attr('href');
					texts[link] = container.find(link);
					links[link] = a;
				})
				.click(function () {
					var a = $(this);
					var link = a.attr('href');
					
					wdpro.each(texts, function (text, textLink) {
						if (text.is(link)) {
							text.show();
							links[textLink].addClass('tab-selected');
						}
						else {
							text.hide();
							links[textLink].removeClass('tab-selected');
						}
					});
					
					return false;
				})
				.eq(0).click()
			;
		});
	};
	
})(jQuery);

