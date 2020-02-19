/**
 * Dialog окошки
 *
 * Служат для отоюражения формы обратного звонка либо для формы регистрации
 */

(function ($)
{

	/**
	 * Массив открытых окон
	 *
	 * @type {Set}
	 */
	var dialogsList = {};
	var currentDialogI = 1;

	wdpro.dialogs = {


		// Счетчик открытых окошек
		// Чтобы, например, можно было выдать предупреждение о закрытии окна, когда есть не закрытые окошки
		openedCount: 0,

		animate: 0, // Анимация открытия / закрытия в миллисекундах


		/**
		 * Возвращает объект окна по его html блоку
		 *
		 * @param html {jQuery} Html блок окна
		 * @return {wdpro.dialogs.Dialog}
		 */
		getObjectByJquery: function (html) {
			if (html.length) {
				var N = html.attr('data-n');
				if (N) {
					N = Number(N);
					return dialogsList[N];
				}
			}
		},


		/**
		 * Класс окна
		 *
		 * @this {wdpro.dialogs.Dialog}
		 */
		Dialog: wdpro.Event.extend({

			/**
			 * Конструктор
			 *
			 * @param {{}} [params]
			 * Объект параметров { title: 'Заголовок окна', content: 'Содержимое окна' }
			 */
			init: function (params) {

				var self = this;

				// Сохраняем параметры
				params = $.extend({
					maxWidth: wdpro.dialogs.maxWidth ? wdpro.dialogs.maxWidth : 800,
					margin: 0,
					marginTop: 0,
					closeSymbol: wdpro.dialogs.closeSymbol,
					animate: wdpro.dialogs.animate
				}, params);
				this.params = params;

				// Получаем html код окна из шаблона
				this.html = $(dialog_templates.window(params)).hide();

				// Контейнер контента, чтобы потом менять содержимое
				this.Content = $(this.html).find('.js-dialog-content');

				// Контейнер заголовка
				this.Title = $(this.html).find('.js-dialog-title');

				this.allContainer = $(dialog_templates.container()).prependTo(wdpro.body);



				// Обработка клика по кнопке "Закрыть"
				$(this.html).find('.js-dialog-close').click(function ()
				{
					if (self.params.hideOnClose)
					{
						self.hide();
					}
					else
					{
						self.close();
					}
				});

				this.allContainer.append(this.html);

				// Если требуется подложка
				if (params.substrate)
				{
					// Получаем html код подложки
					this.substrateHtml = $(dialog_templates.substrate()).hide();

					// Добавляем подложку на страницу
					wdpro.body.prepend(this.substrateHtml);

					// При клике по подложке
					$(this.substrateHtml).click(function ()
					{
						if (self.params.hideOnClose)
						{
							self.hide();
						}
						else
						{
							self.close();
						}
					});

					// Добавляем окно в подложку
					this.allContainer.addClass('dialog-container-fixed');

					wdpro.body.addClass('dialog-body-no-scroll');


					// Закрытие при клике по substrate
					(function () {

						var over = false;
						self.html.on('mouseenter', function () {
							over = true;
						})
							.on('mouseleave', function () {
								over = false;
							});

						self.allContainer.on('click', function () {
							if (!over)
								self.close();
						});

					})();
				}


				// Добавляем окно в массив окон и запоминаем индекс окна
				this.N = currentDialogI;
				dialogsList[this.N] = this;
				this.html.attr('data-n', this.N);
				currentDialogI ++;


				// Добавляем обработчик закрытия
				if (typeof this.params.close === 'function')
				{
					self.on('closed', this.params.close);
				}


				// Добавляем обработчик показа
				if (typeof this.params.show === 'function')
				{
					self.on('show', this.params.show);
				}


				// Добавляем обработчик скрытия
				if (typeof this.params.hide === 'function')
				{
					self.on('hide', this.params.hide);
				}


				// Текст
				if (params.content)
				{
					this.setContent(params.content);
				}


				// Заголовок
				if (params.title)
				{
					this.setTitle(params.title);
				}


				// Позиция по-умолчанию (по центру)
				if (typeof this.params.positioning !== 'function')
				{
					this.params.positioning = function (dialogWindow)
					{
						//self.allContainer.addClass('dialog-container-no-scroll');
						dialogWindow.css('margin', 0);

						// X
						dialogWindow.css('max-width', 'none').css('left', '0');
						var dialogWidth = dialogWindow.outerWidth();

						// Ширина окна
						var testWidth = $('<div/>').appendTo(self.allContainer);
						var windowWidth = testWidth.width();
						testWidth.remove();


						var maxWindowWidth = windowWidth - self.params.margin * 2;
						var maxWidth = Math.min(self.params.maxWidth, maxWindowWidth);

						dialogWidth = Math.min(dialogWidth, maxWidth);

						dialogWindow
							.css('max-width', maxWidth+'px');

						var x = Math.round(
							Math.round(windowWidth / 2)
							- Math.round(dialogWidth / 2)
						);

						dialogWindow.css('margin', '').css('top', 0);
						var marginTop = dialogWindow.position().top;

						// Y
						var y = Math.max(
							0,
							Math.round(
								$(window).height() / 2 - dialogWindow.outerHeight() / 2 - marginTop
							)) /*+ $(document).scrollTop()*/;



						dialogWindow
						.css('left', x + 'px')
						.css('top', y + 'px');
						self.allContainer.removeClass('dialog-container-no-scroll');
					};

					this.params.positioning(this.html);
				}


				// Url
				if (this.params.url) {
					this.load(this.params.url);
				}


				this.show();

				this.processingContent();

				$(window).on('resize', function () {
					self.updatePos();
				});

				this.html.on('updatePos', function () {
					self.updatePos();
				});

				if (this.params.draggable) {
					this.html.draggable({
						'cancel': 'form'
					});
				}

				this.html.on('close', function () {
					self.close();
				});

				// Подсчитываем количетсво открытых окошек
				wdpro.dialogs.openedCount ++;
				this.on('closed', function () {
					wdpro.dialogs.openedCount --;
				});
			},


			/**
			 * Загружает адрес
			 *
			 * @param url {string|{}} Адрес или параметры для ajax запроса
			 */
			load: function (url) {
				var self = this;

				this.loadingStart();

				if (typeof url === 'object') {
					url = wdpro.ajaxUrl(url);
				}

				wdpro.ajax(url, function (data) {

					if (typeof data === 'string') {
						data = {
							'html': data
						};
					}

					self.loadingStop();

					if (data['html']) {
						self.setContent(data['html']);
					}

					if (data['content']) {
						self.setContent(data['content']);
					}

					/*if (data['reloadPage']) {
						wdpro.reloadPage();
					}*/
				});
			},


			/**
			* Показывает Loading...
			*/
			loadingStart: function () {
				this.setContent('Загрузка...');
			},


			/**
			* Убирает Loading...
			*/
			loadingStop: function() {
				this.setContent('');
			},


			/**
			 * Обработка html кода текста
			 */
			processingContent: function () {

				var self = this;

				this.html.find('.js-dialog-close').click(function () {

					self.close();
				});

				wdpro.contentProcess(this.html);
			},


			/**
			 * Закрывает окно и удаляет его
			 */
			close: function () {
				var self = this;

				// Если есть функция, обрабатывающая каллбэк
				if (typeof this.params.close === 'function')
				{
					// запускаем каллбэки закрытия
					this.trigger('closed', this);
				}


				// Если нет
				else
				{
					// Просто закрываем
					var close = function ()
					{
						if (!self.params.stopClose)
						{
							// Удаление самого окна

							self.hide(() => {

								$(self.substrateHtml).remove();
								$(self.allContainer).remove();
								$(self.html).remove();

								self.trigger('closed', self);

								// Удаление окна из массива окон
								delete dialogsList[self.N];

								// Включаем скролл страницы
								$('body').removeClass('dialog-body-no-scroll');
							});
						}
					};

					close();
				}
			},


			/**
			 * Скрывает окно
			 *
			 * @param callback {function}
			 */
			hide: function (callback) {


				const finish = () => {
					// Запуск каллбэков скрытия
					this.trigger('hide', this);

					callback && callback();
				};

				if (this.params.animate) {
					$(this.html).fadeOut(this.params.animate, finish);
					$(this.substrateHtml).fadeOut(this.params.animate);
				}

				else {
					$(this.html).hide();
					$(this.substrateHtml).hide();
					finish();
				}
			},


			/**
			 * Показывает окно
			 */
			show: function ()
			{
				const finish = () => {
					// Позиция окна
					this.updatePos();
					// Запуск каллбэков показа
					this.trigger('show', this);
				};

				if (this.params.animate) {
					$(this.html).fadeIn(this.params.animate, finish);
					$(this.substrateHtml).fadeIn(this.params.animate);
					// Позиция окна
					this.updatePos();
				}
				else {
					$(this.html).show();
					$(this.substrateHtml).show();
					finish();
				}
			},


			/**
			 * Обновляет позицию окна
			 */
			updatePos: function () {
				this.params.positioning(this.html);
			},


			/**
			 * Установка нового контента
			 *
			 * @param Content {string|jQuery} Контент
			 */
			setContent: function (Content) {

				$(this.Content).empty().append(Content);

				if (this.params && this.params.positioning)
				{
					this.params.positioning(this.html);
				}

				this.processingContent();
			},


			/**
			 * Установка нового титла
			 *
			 * @param Title {string} Заголовок
			 */
			setTitle: function (Title) {
				$(this.Title).empty().append(Title);
			},


			/**
			 * Возвращает высоту окна
			 *
			 * @returns {number}
			 */
			height: function () {
				return $(this.html).height();
			}


		}),


		/**
		 * Открывает окошко и возвращает его объект
		 *
		 * @param [params] {{}} Параметры окошка
		 * @returns {wdpro.dialogs.Dialog}
		 */
		open: function (params) {

			var dialog = new wdpro.dialogs.Dialog(params);
			dialog.show();

			return dialog;
		}

	};


})(jQuery);
