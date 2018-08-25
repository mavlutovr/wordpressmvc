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
					maxWidth: 800,
					margin: 20
				}, params);
				this.params = params;

				// Получаем html код окна из шаблона
				this.html = $(dialog_templates.window(params)).hide();

				// Контейнер контента, чтобы потом менять содержимое
				this.Content = $(this.html).find('.JS_dialog_content');

				// Контейнер заголовка
				this.Title = $(this.html).find('.JS_dialog_title');

				// Добавляем html код окна на страницу
				$('body').prepend(this.html);

				// Обработка клика по кнопке "Закрыть"
				$(this.html).find('.JS_dialog_close').click(function ()
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


				// Если требуется подложка
				if (params.substrate)
				{
					// Получаем html код подложки
					this.substrateHtml = $(dialog_templates.substrate()).hide();

					// Добавляем подложку на страницу
					$('body').prepend(this.substrateHtml);

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
				}


				// Добавляем окно в массив окон и запоминаем индекс окна
				this.N = currentDialogI;
				dialogsList[this.N] = this;
				currentDialogI ++;


				// Добавляем обработчик закрытия
				if (typeof this.params.close == 'function')
				{
					self.on('closed', this.params.close);
				}


				// Добавляем обработчик показа
				if (typeof this.params.show == 'function')
				{
					self.on('show', this.params.show);
				}


				// Добавляем обработчик скрытия
				if (typeof this.params.hide == 'function')
				{
					self.on('hide', this.params.hide);
				}


				// Текст
				if (params.content != null)
				{
					this.setContent(params.content);
				}


				// Заголовок
				if (params.title != null)
				{
					this.setTitle(params.title);
				}


				// Позиция по-умолчанию (по центру)
				if (typeof this.params.positioning != 'function')
				{
					this.params.positioning = function (dialogWindow)
					{
						// X
						$(dialogWindow).css('max-width', 'none');
						var width = $(dialogWindow).outerWidth();

						var maxWindowWidth = $(window).width() - self.params.margin * 2;
						var maxWidth = Math.min(self.params.maxWidth, maxWindowWidth);

						width = Math.min(width, maxWidth);

						var x = Math.round(
							Math.round($(window).width() / 2)
							- Math.round(width / 2)
						);

						// Y
						var y = Math.max(0, Math.round($(window).height() / 2 - $(dialogWindow).outerHeight() / 2)) +
						        $(document).scrollTop();



						$(dialogWindow)
						.css('max-width', maxWidth+'px')
						.css('left', x + 'px')
						.css('top', y + 'px');
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

				this.html.draggable({
					'cancel': '.JS_input_container'
				});

				this.html.on('close', function () {
					self.close();
				});
			},


			/**
			* Загружает адрес
			*/
			load: function (url) {
				var self = this;

				this.loadingStart();

				wdpro.ajax(url, function (data) {

					self.loadingStop();

					self.setContent(data['html']);

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

				// Отправляем событие, чтобы можно было обрабатывать контент другим скриптам через wdpro.on('content', function...);
				wdpro.trigger('content', this.html);
			},


			/**
			 * Закрывает окно и удаляет его
			 */
			close: function () {
				var self = this;

				// Если есть функция, обрабатывающая каллбэк
				if (typeof this.params.close == 'function')
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
							$(self.html).remove();

							// Удаление подложки
							$(self.substrateHtml).remove();

							// Удаление окна из массива окон
							delete dialogsList[self.N];
						}
					};

					close();
				}
			},


			/**
			 * Скрывает окно
			 */
			hide: function () {

				$(this.html).hide();
				$(this.substrateHtml).hide();

				// Запуск каллбэков скрытия
				this.trigger('hide', this);
			},


			/**
			 * Показывает окно
			 */
			show: function ()
			{
				// Показываем окно, чтобы правильно посчиталась позиция
				$(this.html).show();
				$(this.substrateHtml).show();

				// Позиция окна
				this.updatePos();

				// Запуск каллбэков показа
				this.trigger('show', this);
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
