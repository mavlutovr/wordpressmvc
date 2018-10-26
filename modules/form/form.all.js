(function ($) {
	/**
	 * Forms set of WDPro
	 *
	 * @type {{}}
	 */
	wdpro.forms = {};


	/**
	* Вревращалка слоя с данными формы в саму форму
	*/
	$.fn.wdproForm = function () {

		$(this).each(function ()
		{
			var container = $(this);

			// Получаем параметры формы
			var jsonDiv = container.find('.js-params');
			var json = jsonDiv.text();
			var data = wdpro.parseJSON(json);

			// Создаем форму
			var form = new wdpro.forms.Form(data);

			form.getHtml(function (html) {
				container.append(html);
			});
			
			
		});

		return this;
	};


	$(document).on('DOMNodeInserted', function (e)
	{
		for(var i in e.target.classList)
		{
			if (e.target.classList[i] == 'wdpro-form')
			{
				var id = Number($(e.target).attr('data-form-id'));
				forms[id].trigger('addedToPage');
			}
		}
		/*wdpro.each(typeof e.target.classList, function (value)
		{
			if (value)
			{
				console.log(value);
			}
			if (value == 'wdpro-form')
			{
				console.log(e.target);
			}
		});*/
		/*console.log(typeof e.target.classList);
		if (e.target.classList.indexOf('js-form') != -1)
		{
			console.log(e.target.classList);
		}
		
		if (e.target.nodeName == 'FORM')
		{
			console.log(e.target);
			var formId = $(e.target).attr('data-id');
			if (formId)
			{
				console.log('FORM', formId);
			}
		}*/
	});


	/**
	 * Данные элемента
	 */
	//export interface ElementData
	var ElementData = {

		// Текст слева
		'left': '',

		// Текст справа
		'right': '',

		// Текст сверху
		'top': '',

		// Текст снизу
		'bottom': '',

		// Описание поля
		'label': '',

		// Html атрибуты поля
		'attrs': '',

		// Дополнительный CSS класс поля
		'class': '',

		// Техническое, создается само потом
		inputjQueryMarker: ''
	};


	/**
	 * Порядковый номер, использующийся для ключей полей
	 *
	 * @type {number}
	 */
	var unnamedKeyN = 0;


	/**
	 * Порядковый номер формы
	 * 
	 * Чтобы можно было найти форму по ее jquery объекту
	 * 
	 * @type {number}
	 */
	var currentFormI = 1;

	/**
	 * Список форм по номерам
	 * 
	 * @type {{}}
	 */
	var forms = {};


	/**
	 * Возвращает форму при ее появлении
	 * 
	 * @param [name] {string} Имя формы, которое в параметрах указывается как jsName
	 * @param Return {function} Каллбэк, принимающий форму
	 */
	wdpro.forms.onForm = function (name, Return) {

		var args = wdpro.argumentsSortByTypes(arguments);

		if (args['string']) {
			wdpro.on('form-'+args['string'], args['function'], true);
		}
		else {
			wdpro.on('form', args['function'], true);
		}
	};


	/**
	 * Возвращает форму, когда она уже была добавлена на странцу
	 *
	 * @param name {string} Имя формы
	 * @param callback {function} Каллбэк, в который отправляется форма
	 */
	wdpro.forms.onFormAddedToPage = function (name, callback) {
		wdpro.forms.onForm(name, function (form) {
			form.on('addedToPage', function () {
				callback(form);
			}, true);
		});
	};


	/**
	 * Возвращает объект формы по ее html коду (jquery)
	 *
	 * @param html {jQuery} Html блок формы
	 * @returns {wdpro.forms.Form}
	 */
	wdpro.forms.getFormObjectByHtml = function (html) {
		var id = Number(html.attr('data-id'));
		if (forms[id]) {
			return forms[id];
		}
	};


	/**
	 * Выравнивает все формы
	 */
	wdpro.forms.align = function () {
		wdpro.each(forms, function (form) {
			form.align();
		});
	};


	/**
	 * Класс формы
	 * 
	 * @this {wdpro.forms.Form}
	 */
	wdpro.forms.Form = wdpro.Event.extend({


		/**
		 * Конструктор
		 *
		 * @param [params] {{}} Данные формы
		 */
		init: function (params) {
			var self = this;
			
			this.id = currentFormI;
			currentFormI++;
			
			// Запоминаем форму по номеру
			forms[this.id] = this;

			// Список шаблонов
			this.templates = wdpro.templates.forms;

			// Список скрытых элементов
			this.hiddens = {};

			// Группы
			this.groups = [];

			// Наличие левой колонки с текстом
			this.leftExists = false;

			// Список элементов
			this.elements = {};

			// По-умолчанию
			this.params = wdpro.extend({
				'errorsPrefix': 'Пожалуйста, заполните обязательные поля',
				'action':       '',
				'align': true
			}, params);


			// Перебираем группы
			// var groups:any[] = this.params.groups;
			wdpro.each(this.params.groups, function (groupData) {
				// Создаем группу
				var group = new Group(groupData);

				// Добавляем группу
				self.add(group);
			});


			// Перебираем элементы
			wdpro.each(this.params.elements, function (element) {
				self.add(element);
			});


			// Еще 1 способ инициализации элементов через класс наследника
			this.initElements();
			
			// Установка значений полей
			this.params['data'] && this.setData(this.params['data']);
			
			this.on('addedToPage', function () {
				
				self.eachElements(function (element)
				{
					element.trigger('addedToPage');
				});
			});
			
			// Отправка формы в каллбэки, которые ожидают ее появления
			if (this.params['jsName']) {
				wdpro.trigger('form-'+this.params['jsName'], this);
			}

			wdpro.trigger('form', this);

			this.on('addedToPage', function () {
				self.updateDialogPos();
			});


			// Ajax
			if (this.params['ajax']) {
				this.ajax(function (data) {
					self.loading();

					wdpro.ajax(self.params['action'], data, function (response) {

						if (response['dialogClose']) {
							self.closeDialog();
						}

						self.loadingStop();
					});
				});
			}
		},


		/**
		 * Инициализация элементов в классе наследнике
		 */
		initElements: function () {

		},


		/**
		 * Добавление элемента
		 *
		 * @param elements Элементы одной группы (одной строки),
		 * данные элемента или объект группы
		 * @param [elements.containerClass] string Класс всего блока вместе с описанием
		 * @param [elements.class] Класс самого поля 
		 * @param [elements.left] Подпись слева
		 * @param [elements.top] Подпись сверху
		 * @param [elements.right] Подпись справа
		 * @param [elements.bottom] Подпись снизу
		 * @param [elements.autoLeft] {bool} Выравнивать ширину левой подписи
		 * @param [elements.width] {number} Ширина
		 * @param [elements.attrs] {{}} Список дополнительных атрибутов для поля
		 * @param [elements.label] {string} Подпись поля
		 * @param [elements.style] {string} Стиль для поля
		 */
		add: function (elements) {
			var self = this;
			elements = wdpro.args(arguments);
			
			// Когда внутри первого элемента находится не элемент, а список элементов
			if (elements[0] && elements[0][0] && !elements[1])
			{
				var newElements = [];
				wdpro.each(elements[0], function (element)
				{
					newElements.push(element)
				});
				
				elements = newElements;
			}
			var selfElements = this.elements;
			var selfGroups = this.groups;
			var selfTemplates = this.templates;


			/**
			 * Добавляет элемент в форму
			 *
			 * @param element {BaseElement} Объект элемента
			 */
			var addElement = function (element) {
				// Добавляем элемент в массив элементов
				selfElements[element.getKey()] = element;

				// Устанавливаем данные формы в элемент
				if (self.data)
				{
					element.setFormValue(self.data);
				}

				// Изменение элемента
				element.on('change', function () {
					self.trigger('change', self.getData());
				});
			};


			/**
			 * Добавляет группу
			 *
			 * @param group {Group} Объект группы
			 */
			var groupAdd = function (group) {
				// Добавляем группу в массив групп
				self.groups.push(group);

				// Отправляем в группу набор шаблонов
				group.setTemplates(self.templates);

				// Перечисляем элементы группы
				group.eachElements(function (element) {
					// Добавляем элемент из группы в форму
					addElement(element);
				});

				// Отправляем объект формы в группу
				group.setForm(self);

				// Получаем первый элемент
				var firstElement = group.getElementByN(0);

				// Если у первого элемента есть левый текст
				if (firstElement.leftExists())
				{
					// Запоминаем что у элементов в форме есть левый текст, 
					// чтобы потом поставить левый отступ у кнопки ОК
					self.leftExists = true;
				}

				// Если уже существует html контейнер групп
				if (self.jGroupsContainer)
				{
					// Добавляем в контейнер групп данную группу
					group.getHtml(function (html) {
						$(self.jGroupsContainer).append(html);
						wdpro.jQueryToHtmlRun(self.jGroupsContainer, 'form');

						// Выравниваем колонки
						self.align();
					});
				}
			};


			// Элемент - это группа
			if (elements[0] instanceof Group)
			{
				// Добавляем эту группу
				groupAdd(elements[0]);
			}


			// Элемент - это элемент или данные элемента
			else
			{
				// Создаем группу
				var group = new Group();

				// Перечисляем элементы
				wdpro.each(elements, function (element) {
					// Добавляем элемент в группу
					group.add(element);
				});

				// Добавляем группу в форму
				groupAdd(group);
			}
		},


		/**
		 * Установка формы в контейнер
		 *
		 * @param formContainer Контейнер, куда вставить форму
		 */
		appendTo: function (formContainer) {
			// Получаем html код формы
			this.getHtml(function (html) {
				$(html).appendTo(formContainer);
			});
		},


		/**
		 * Возвращает html код формы
		 *
		 * @param callback {Function} Каллбэк, принимающий html код формы
		 */
		getHtml: function (callback) {
			var self = this;

			// Если jQuery блок уже был создан
			if (this.html)
			{
				callback(this.html);
			}

			// Если jQuery блок еще не создан
			else
			{
				// Данные шаблона
				var templateData = {
					form:     {},
					groups:   [],
					elements: {},
					params: this.params
				};

				// Ожидаемая группа функций
				var waiter = new wdpro.Waiter();

				// Получаем параметры формы
				waiter.wait(function (complete) {
					self.getParams(function (data) {
						templateData.form = data;
						complete();
					});
				});


				// Перебираем группы
				wdpro.each(this.groups, function (group) {
					// Добавляем в группу ожидаемых функций 
					// функцию добавления html кода группы в данные шаблона
					waiter.wait(function (Complete) {
						// Получаем html код группы
						group.getHtml(function (html) {
							// Если есть блок группы
							if (html)
							{
								// Добавляем в данные шаблона метку шаблона, 
								// которая замениться на jQuery блок группы
								templateData.groups.push(
									wdpro.jQueryToHtml(html, 'form')
								);
							}

							// Завершаем ожидание функции
							Complete();
						});
					});
				});

				// Перебираем элементы
				wdpro.each(
					this.elements,

					/**
					 * @param element {BaseElement}
					 */
					function (element) {
						// Добавляем в группу ожидаемых функций функцию, 
						// которая добавляет в данные шаблона html код элемента
						waiter.wait(function (Complete) {
							// Получаем html элемента
							element.getHtml(function (html) {
								// Если есть элемент
								if (html)
								{
									// Сохраняем html в данных шаблона формы
									templateData.elements[element.getName()] =
										wdpro.jQueryToHtml(html, 'form');
								}

								// Завершаем ожидание функции
								Complete();
							});
						});
					}
				);


				// Запускаем выполнение ожидаемых функций
				waiter.onCompletion(function () {
					
					// Получаем шаблон формы
					self.html = $(self.templates.form(templateData));

					// Устанавливааем номер формы
					self.html.attr('data-form-n', self.id);

					// Расставляем по всем меткам соответствующие элементы
					wdpro.jQueryToHtmlRun(self.html, 'form');

					// Запоминаем контейнер групп, 
					// чтобы можно было добавлять элементы в форму после этого места
					self.jGroupsContainer = $(self.html).find('.JS_groups_container');

					// Выравниваем колонки
					self.align();
					
					// Через секунду
					setTimeout(function () { self.align(); }, 10);
					setTimeout(function () { self.align(); }, 1000);

					// Инициализируем созданную форму
					self.initJForm();

					// Копируем специальные классы элементов в их контейнеры
					self.html.find('.JS_class_for_container').each(function () {
						var input = $(this);

						input.closest('.JS_element').addClass(input.attr('JS_class_for_container'));
					});

					// Выравнивание при загрузке документа
					window.onload = function () {
						self.align();
					}

					$(window).resize(function () {
						self.align();
					});
					
					self.html.attr('data-form-id', self.id);

					callback(self.html);
				});
			}
		},


		/**
		 * Возвращает данные формы
		 *
		 * @returns {*}
		 */
		getData: function () {
			
			// Чтобы все поля подготовились к получению данных
			// Это в первую очередь касается Ckeditor
			this.eachElements(function (element) {
				element.trigger('prepareToGetData');
			});

			return this.jForm.serializeObject();
		},

		
		/**
		 * Установка значений полей формы
		 * 
		 * @param data {{}} Значения полей
		 */
		setData: function (data) {
			this.setValues(data);
		},

		
		/**
		 * Проверка заполненности формы на корректность
		 *
		 * @returns {boolean}
		 */
		valid: function (Return) {
			var self = this;

			// Параметры отправки
			var sendParams = {
				submit:       true, // Форма подходит для топравки
				errorsExists: false, // Ошибки (не правильно заполненные поля)
				errorsList:   [] // Список полей с ошибками
			};
			
			
			var waiter = new wdpro.Waiter();

			// Перебираем элементы формы
			self.eachElements(
				function (element) {
					waiter.wait(function (waitComplete)
					{
						// Разрешение на отправку формы от элемента
						element.submitPermit(sendParams, function (submit)
						{
							if (submit === false)
							{
								sendParams.submit = false;
							}
							
							waitComplete();
						});
					});
				}
			);
			
			// При завершении проверок всех полей
			waiter.run(function ()
			{
				// Если есть ошибки
				if (sendParams.errorsExists)
				{
					// Выводим их
					alert(
						self.templates.errors({
							errors:       sendParams.errorsList,
							errorsPrefix: self.params['errorsPrefix']
						})
					);
				}
				
				Return(sendParams.submit);
			});
		},


		/**
		 * Обработка отправки формы
		 *
		 * @returns {boolean}
		 */
		submit: function () {
			var self = this;

			this.loading();
			
			this.valid(function (valid)
			{
				if (valid)
				{
					// Ajax
					if (self._ajax)
					{
						// Получаем данные
						var data = self.getData();

						// Отправляем данные в каллбэк
						self.trigger('submit', data);
					}

					else 
					{
						self.forseSubmit = true;
						self.jForm.submit();
					}
				}
				
				else
				{
					self.loadingStop();
				}
			});

			/*// Если форма заполнена верно
			if (this.valid())
			{
				// Ajax
				if (self._ajax)
				{
					// Получаем данные
					var data = self.getData();

					// Отправляем данные в каллбэк
					self.trigger('submit', data);

					// Останавливаем обычную отправку
					return false;
				}

				return true;
			}

			return false;*/
		},


		// Инициализация формы
		initJForm: function () {
			var self = this;

			// Получаем объект формы
			this.jForm = this.html.find('form');
			this.jForm.attr('data-id', this.id);

			// Контейнер сообщений
			this.messagesContainer = this.html.find('.JS_messages_container');
			
			// При отправке формы
			this.jForm.submit(function (e) {
				
				if (self.forseSubmit)
				{
					return true;
				}
				// Обрабатываем отправку формы
				//return self.submit();
				self.submit();
				e.preventDefault();
				return false;
			});

			// Сообщения, которые надо сразу отобразить
			if (this.params['messages']) {
				wdpro.each(this.params['messages'], function (message) {
					self.showMessage(message['message'], message['params']);
				});
			}
		},


		/**
		 * Ищет в форме как jQuery.find()
		 * 
		 * @param selector {string} Параметры поиска
		 * @returns {*}
		 */
		find: function (selector) {
			
			return this.jForm.find(selector);
		},


		/**
		 * Показать сообщение
		 *
		 * @param message
		 * @param params
		 */
		showMessage: function (message, params) {
			var self = this;

			if (this.messagesContainer)
			{
				// Установка текста
				this.messagesContainer.html(message);

				// Ошибка
				this.messagesContainer.removeClass('_error_message');
				params && params.err && this.messagesContainer.addClass('_error_message');

				// Убираем саму форму
				params && params.hideForm && this.jForm.hide().trigger('hide');

				// Закрываем окно
				params && params.close && (function () {
					// Сразу
					if (params.close === true)
					{
						self.html.trigger('closePop');
					}

					// Через промежуток времени
					else
					{
						wdpro.timeOne(params.close, function () {
							self.html.trigger('closePop');
						});
					}
				})();
			}
		},


		/**
		 * Показать сообщение об ошибке
		 *
		 * @param message
		 */
		showErrorMessage: function (message) {
			this.showMessage(message, {err: true});
		},


		/**
		 * Отображает сообщение
		 *
		 * @param message {string}
		 */
		displayMessage: function (message) {
			this.messagesContainer.html(message);
		},


		/**
		 * Перебор элементов
		 *
		 * @param callback {function}
		 */
		eachElements: function (callback) {
			wdpro.each(this.elements, callback);
		},


		/**
		 * Возвращает данные формы
		 *
		 * @param callback {function}
		 */
		getParams: function (callback) {
			callback(this.params);
		},


		/**
		 * Возвращает параметр формы
		 * 
		 * @param paramName {string} Имя параметра
		 * @returns {*}
		 */
		getParam: function (paramName) {
			
			return this.params[paramName];
		},


		/**
		 * Установкаа данных в форму
		 *
		 * @param data {{}} Данные
		 */
		setValues: function (data) {
			// Перебираем элементы
			this.eachElements(function (element) {
				// Устанавливаем данные формы
				element.setFormValue(data);
			});

			// Запоминаем данные на элементы, которые будут добавлены после
			this.data = data;
		},


		/**
		 * Устанавливает шаблоны для формы
		 *
		 * @param templates
		 */
		setTemplates: function (templates) {
			this.templates = templates;
		},


		/**
		 * Возвращает имя формы
		 *
		 * @returns {string}
		 */
		getName: function () {
			return this.params.name;
		},


		/**
		 * Выравнивание элементов в зависимости от ширины формы
		 */
		align: function () {

			var self = this;

			// Обновляем позицию Dialog, когда форма в окошке
			self.updateDialogPos();
			
			if (this.params['align'])
			{
				// Группы
				var groups = [];

				// Список всех элементов
				var elements = [];

				// Набор элементов jQuery
				var jQueryElements = $(this.html).find('.JS_element:not(.js-form-align-disabe)');

				// Количество колонок
				var collsN = 0;

				// Максимально количество попыток выравнивания
				var alignMaxTryes = 40;

				// Номер текущей попытки
				var alignCurrentTry = 1;

				/**
				 * Массив элементов по колонкам
				 *
				 * @type {Array}
				 */
				//var elementsOfColl = [];


				// X левой колонки, по нему определяется, что элемент не поместился в своей строке 
				// и перешел на следующую
				var firstX = null;


				/**
				 * Удаление переходов на новые строки
				 */
				var removeNewRows = function () {
					// Удаляем слои, которые переводили поля на новые строки
					wdpro.each(self.alignNewRowsDivs, function (div) {
						$(div).remove();
					});

					// Создаем новый массив этих слоев
					self.alignNewRowsDivs = [];
				};

				// Удаляем переходы на новые строки
				removeNewRows();

				var groupN = 0;

				// Добавление группы в список групп
				var groupAdd = function (group) {
					group.n = groupN;
					groupN++;
					groups.push(group);
				};


				/**
				 * Удаляет группу, возвращая ее элементы
				 *
				 * @param group {{}} Группа элементов
				 * @returns {*}
				 */
				var getElementsAndRemoveGroup = function (group) {
					// Получаем элементы группы
					var elements = group.elements;

					// Если у этой группы есть дочерние группы
					if (group.child)
					{
						// Получаем элементы дочерней группы
						var childElements = getElementsAndRemoveGroup(group.child);

						// добавляем в массив элементов дочерние элементы
						elements = elements.concat(childElements);
					}

					// Удаляем группу из списка групп
					// Находим индекс группы
					var i = groups.indexOf(group);

					// Удаляем из массива эту группу
					groups.splice(i, 1);

					return elements;
				};


				var elementsLog = function (elements) {

				};


				// Первичное распределение элементов по колонкам
				// Перебираем группы
				$(this.html).find('.JS_group').each(function () {
					// 1 группа элементов
					var group = {
						elements: [],
						child:    {}
					};

					// Перебираем элементы группы
					$(this).find('.JS_element').each(function (i) {
						var element = this;

						// Увеличиваем общее количество колонок
						collsN = Math.max(i + 1, collsN);

						// Самый левый X
						if (firstX === null)
						{
							firstX = $(element).offset().left;
						}

						// Если у элемента есть левое поле
						$(this).find('.JS_left_text').each(function () {
							// Сбрасываем ширину, чтобы дальше нормально определилась реальная ширина
							$(this).width('auto');

							// Добавляем элемент в массив элементов по колонкам
							//elementsOfColl[i] = elementsOfColl[i] || [];

							// Создаем сводный объект элемента
							var elementOfColl = wdpro.obj({
								element:     element,
								field:       $(element).find('.JS_input_container'),
								left:        $(element).find('.JS_left').width('auto'),
								leftText:    this,
								'autoWidth': null
							});

							// Добавляем этот элемент в массив элементов
							elements.push(elementOfColl);

							// Блок автоширины поля
							var autoWidth = $(element).find('.JS_auto_width');
							if (autoWidth.length)
							{
								elementOfColl['autoWidth'] = $(autoWidth).width('auto');
							}

							// Добавляем элемент в список элементов
							//elementsOfColl[i].push(elementOfColl);

							// Добавляем элемент в группу
							group.elements.push(elementOfColl);
						});
					});

					// Добавляем группу в список групп
					// groups.push(group);
					groupAdd(group);
				});

				// Добавляем child в каждую группу, чтобы каждая группа знала, какая группа идет за ней
				groups.forEach(function (group, i) {
					var nextGroup = groups[i + 1];

					if (nextGroup)
					{
						group.child = nextGroup;
					}
				});


				var N = 0;

				// Выравнивание полей по номерам колонок, которые были им присвоены
				var alignAllColls = function () {
					N++;

					// Останавливаемся если кончились попытки
					if (alignCurrentTry > alignMaxTryes)
					{
						return;
					}


					var logGroup = function (group) {
						var text = group.n + ': ';
						var elementsArr = [];

						group.elements && group.elements.forEach(function (element) {
							if (element)
							{
								elementsArr.push($(element.leftText).text());
							}
							else
							{
								elementsArr.push('UNDEFINED');
							}
						});

						text += elementsArr.join(', ');
					};


					var logGroups = function () {
						groups.forEach(function (group, i) {
							logGroup(group);
						});
					};
					//logGroups();


					// Увеличиваем счетчик попытки
					alignCurrentTry++;

					// Выравнивание полностью завершено
					var alignComplete = false;

					// Выполнять выравнивание
					var alignRun = true;


					// Сброс уже был осуществлен
					var reseted = false;

					// Сброс ширин и отступов, которые были установлены в прошлый раз
					var reset = function () {
						if (true || !reseted)
						{
							wdpro.each(elements, function (element) {
								$(element.leftText).width('auto');
								$(element.left).width('auto');
								$(element.field).width('auto');
								$(element['autoWidth']).width('auto');
							});
							reseted = true;
						}
					};
					reset();

					//console.log('');

					/**
					 * Выравнивание одной колонки
					 *
					 * @param columnN {Number} Индекс колонки
					 */
					var alignColl = function (columnN) {
						// X самого правого фиелда
						var maxX = 0;

						// Сброс ширины у всех элементов колонки
						wdpro.each(groups, function (group) {
							// Если у данной группы есть заданная колонка
							if (group.elements[columnN])
							{
								// Получаем элемент заданной колонки в этой группе
								var element = group.elements[columnN];

								// Класс у самых левых колонок
								if (columnN == 0)
								{
									$(element.element).addClass('seobit-form-left-first');
									$(element.element).removeClass(
										'seobit-form-left-nonFirst');
								}

								// Классы у остальных колонок
								else
								{
									$(element.element).addClass(
										'seobit-form-left-nonFirst');
									$(element.element).removeClass(
										'seobit-form-left-first');
								}

								// Сбрасываем установленную ранее ширину левых полей
								$(element.left).width('auto');
								$(element.leftText).width('auto');
								$(element.field).width('auto');
								$(element['autoWidth']).width('auto');
							}
						});

						// Максимальная ширина
						var maxWidth = 0;

						//alert('maxWidth');
						//console.log('');
						// Определение максимальной ширины и позиции
						// Перебираем группы
						wdpro.each(groups, function (group) {
							// Console.log
							if (false && columnN == 0)
							{
								if (false || group.elements[0])
								{
									element = group.elements[0];

									try
									{
										if (element.leftText)
										{
											console.log(
												'0: ' + $(element.leftText).text());
										}
									}
									catch (err)
									{
										console.log('groups', groups);
										console.log('group', group);
										throw err;
									}
								}
								//console.log(group.elements[0]);
							}

							// Остановка, если выравнивание остановлено
							if (!alignRun)
							{
								return;
							}

							// Если у данной группы есть заданная колонка
							if (group.elements[columnN])
							{
								// Получаем элемент заданной колонки в этой группе
								var element = group.elements[columnN];

								// Получаем X фиелда
								var x = Math.round($(element.field).offset().left);
								/*if (x == 509)
								 {
								 alert('test');
								 }*/

								// Запоминаем x фиелда
								element.x = x;

								// Номер
								//$(element.field).find('input, textarea').val(columnN+': '+x);

								// Увеличиваем maxX
								maxX = Math.max(maxX, x);

								// Максимальная ширина
								if (element['autoWidth'])
								{
									maxWidth =
										Math.max(maxWidth,
											$(element['autoWidth']).outerWidth(true));
								}
							}
						});

						//console.log('columnN: '+columnN+', maxX', maxX+',  N: '+N);

						// Если есть максимальный X фиелда, значит есть колонка
						if (alignRun && maxX/* && N != 7*/)
						{
							// Ставим все фиелды на место самого правого
							// Перебираем группы
							var groupI = 0;
							while (alignRun && groups[groupI])
							{
								// Получаем группу	
								var group = groups[groupI];

								// Если у данной группы есть заданная колонка
								if (group.elements[columnN])
								{
									// Получаем элемент заданной колонки в этой группе
									var element = group.elements[columnN];

									// Получаем значение, на которое надо фиелд подвинуть вправо
									var plus = maxX - element.x;

									// Передвигаем фиелд, увеличивая на данное значение ширину левого текста
									// Получаем текущую ширину
									var leftWidth = $(element.leftText).outerWidth(true);

									// Увеличиваем текущую ширину
									leftWidth += plus;

									// Округляем
									leftWidth = Math.round(leftWidth);

									// Применяем новую ширину к левому блоку
									$(element.leftText).outerWidth(leftWidth);

									//$(element.field).find('input,textarea').val('coll: '+columnN+', group:'+groupI);

									// Применяем новую ширину к блоку фиелда
									$(element['autoWidth']).outerWidth(maxWidth);
								}

								groupI++;
							}


							// Обрабатываем переход на следующую строку
							// Перебираем группы
							groupI = 0;
							while (alignRun && groups[groupI])
							{
								// Получаем группу	
								var group = groups[groupI];

								// Если у данной группы есть заданная колонка
								if (group.elements[columnN])
								{
									// Определение перехода элемента на новую строку, когда он не поместился
									if (columnN > 0 &&
									    $(element.element).offset().left == firstX)
									{
										// Добавляем перед элементом пустой слой, чтобы он остался
										// на этой следующей строке
										var nextRowDiv = $(
											'<div class="JS_next_row"></div>');
										self.alignNewRowsDivs.push(nextRowDiv);
										$(element.element).before(nextRowDiv);

										// Далее надо найти все элементы, которые были отправлены в следующие группы


										// Удаляем переводы строк у последующих элементов
										$(nextRowDiv).nextAll('.JS_next_row').remove();
										/*$(nextRowDiv).closest('.JS_group')
										 .nextAll('.JS_group').find('.JS_next_row').remove();*/

										// Перемещаем этот и последующие элементы так, 
										// чтобы этот элемент начинался с первой колонки

										// Создаем новую группу 
										// и перемещаем в нее этот и последующие элементы группы
										//console.log('Удаление');
										//console.log('До');
										//logGroup(group)
										//console.log('columnN: '+ columnN, 'group.elements.length: '+group.elements.length);
										var newGroup = {
											elements: group.elements.splice(
												columnN,
												group.elements.length - columnN + 1
											)
										};
										// 0 1 2   3 4 5

										//console.log('После');
										//logGroup(group);

										// Добавляем в новую группу дочерние элементы старых дочерних групп
										if (false && group.child)
										{
											var childElements = getElementsAndRemoveGroup(
												group.child);

											newGroup.elements = newGroup.elements.concat(
												childElements
											);
										}

										// Указываем новую группу в старой, чтобы старая группа знала о новой
										group.child = newGroup;

										//var newGroup = group.splice(columnN, group.length - 1);

										// Добавляем группу в массив групп
										//groups.push(newGroup);
										groups.splice(groupI, 0, newGroup);
										//groupAdd(newGroup);

										// Останавливаем выравниваине и запускаем заново
										alignRun = false;

										//alert('alignAllColls');
										// reset();
										//alert('alignAllColls');
									}

								}

								groupI++;
							}
						}
					}

					// Перебираем колонки и выравниваем их
					for (var i = 0; alignRun && i < collsN; i++)
					{
						alignColl(i);
					}

					// Если выравнивание было остановлено
					if (!alignRun)
					{
						// Запускаем выравнивание заново
						alignAllColls();
					}
					else {
						self.updateDialogPos();
					}
				};


				alignAllColls();


				// Выравнивание отступов у верхних и нижних подписей у элементов
				$(jQueryElements).each(function () {
					var left = $(this).find('.JS_left_equalizing_target');

					if ($(left).length)
					{
						var width = $(this).find('.JS_left_equalizing_source').outerWidth(true);
						$(left).outerWidth(width);
					}
				});
			}
		},


		/**
		* Обновляет позицию диалогового окна, в котором находится форма
		*/
		updateDialogPos: function () {
			// Обновляем позицию Dialog, когда форма в окошке
			this.html.closest('.js-dialog').trigger('updatePos');
		},


		/**
		 * Закрывает диалоговое окно, в котором находится
		 */
		closeDialog: function () {
			this.html.closest('.js-dialog').trigger('close');
		},


		/**
		 * Приостанавливает обычную отправку и отправляет в callback данные формы
		 *
		 * @param [callback] {function} Каллбэк, принимающий данные формы
		 */
		ajax: function (callback) {
			this._ajax = true;

			if (callback)
			{
				this.on('submit', callback);
			}
		},


		/**
		 * Показать на форме состояние загрузки
		 */
		loading: function () {
			//this.jForm && this.jForm.find('.JS_submit').addClass('loading');
			this.jForm && this.jForm.find('.JS_submit').loading();
		},


		/**
		 * Убрать с формы состояние загрузки
		 */
		loadingStop: function () {
			this.jForm && this.jForm.find('.JS_submit').loadingStop();
		}
	});

	/**
	 * Наследование
	 *
	 * @param methods
	 * @returns {*|Class}
	 */
	wdpro.forms.extend = function (methods) {
		return Form.extend(methods);
	};


	/**
	 * Группа элементов
	 */
	//export class Group
	var Group = wdpro.forms.Group = wdpro.Class({

		/**
		 * Конструктор
		 *
		 * @param data {{}} Данные
		 */
		init: function (data) {
			var self = this;

			// Список элементов
			this.elements = {};

			/**
			 * Список элементов по порядку
			 *
			 * Чтобы потом можно было получить элемент группы по номеру
			 */
			this.elementsByN = [];


			// Перебираем элементы
			data && wdpro.each(data.elements, function (element) {
				// Добавляем элемент в группу
				self.add(element);
			});
		},


		/**
		 * Добавление элемента в группу
		 *
		 * @param data {{}} Элемент или данные элемента
		 */
		add: function (data) {
			
			// Данные это данные элемента
			if (typeof data == 'object' && !(data instanceof BaseElement))
			{
				// Данные по-умолчанию
				var data = wdpro.extend({
					'type': 'String'
				}, data);

				if (data['type'] == 'checkbox')
				{
					data['type'] = 'check';
				}

				// Первую букву типа поля (имени класса) в верхний регистр
				data['type'] =
					data['type'].charAt(0).toUpperCase() + data['type'].substr(1);

				// Создаем объект
				//element = eval('new '+elementData['type']+'()');
				if (!ElementsClasses[data['type']])
				{
					console.log('Для типа поля ' + data['type'] +
					            ' не указан класс в списке ElementsClasses');
				}
				
				if (!ElementsClasses[data['type']])
				{
					throw new Error('Нет поля формы с классом '+data['type']);
				}
				var element = new ElementsClasses[data['type']](data);
			}

			// Данные это сам элемент
			else
			{
				var element = data;
			}


			// Устанавливаем в элемент набор шаблонов
			element.setTemplates(this.templates);

			// Отправляем в элемент ссылку на группу, чтобы элемент имел доступ к объекту группы
			element.setGroup(this);


			// Добавляем элемент в список элементов
			this.elements[element.getKey()] = element;

			// Добавляем элемент в список элементов по порядку
			this.elementsByN.push(element);
		},


		/**
		 * Возвращает объект элемента по его номеру в группе
		 *
		 * @param n {Number} номер в группе (начиная с нуля)
		 * @returns {BaseElement}
		 */
		getElementByN: function (n) {
			return this.elementsByN[n];
		},


		/**
		 * Возвращает jQuery блок группы
		 *
		 * @param callback {function} Каллбэк, принимающий html блок группы
		 */
		getHtml: function (callback) {
			var self = this;

			// Если блок уже создан
			if (this.html)
			{
				// Возвращаем его
				callback(this.html);
			}

			// Если jQuery блок еще не создан
			else
			{
				// Данные шаблона
				var data = {
					elements: []
				};

				// Ожидаемые функции
				var waiter = new wdpro.Waiter();

				// Добавляем в ожидаемые функции функции, которые получают html коды элементов
				// Перебираем элементы
				this.eachElements(function (element) {
					// Добавляем ожидаемую функцию, которая получаем html код элемента
					waiter.wait(function (Complete) {
						// Получаем html код элемента
						element.getHtml(function (html) {
							// Добавляем html код элемента в данные шаблона
							data.elements.push(
								wdpro.jQueryToHtml($(html), 'form')
							);

							// Завершаем ожидание функции
							Complete();
						});
					});
				});


				// Запускаем ожидаемые функции
				waiter.onCompletion(function () {
					// Получаем html код группы
					self.html = $(self.templates.group(data));

					// Возвращаем html код группы
					callback(self.html);
				});
			}
		},


		/**
		 * Перебор элементов группы
		 *
		 * @param callback {function} callback(BaseElement)
		 */
		eachElements: function (callback) {
			wdpro.each(this.elements, callback);
		},


		/**
		 * Установка набора шаблонов
		 *
		 * @param templates {{}} Набор шаблонов
		 */
		setTemplates: function (templates) {
			// Запоминаем набор шаблонов у себя
			this.templates = templates;

			// Отправляем набор шаблонов во все элементы
			this.eachElements(function (element) {
				element.setTemplates(templates);
			});
		},


		/**
		 * Запоминает объект формы
		 *
		 * @param form {FormAuth} Объект формы
		 */
		setForm: function (form) {
			this.form = form;

			this.eachElements(function (element) {
				element.setForm(form);
			});
		}

	});


	// Label ID
	var labelId = 0;


	/**
	 * Базовый класс элемента формы
	 */
	// export class BaseElement
	var BaseElement = wdpro.forms.BaseElement = wdpro.Event.extend({

		/**
		 * Конструктор
		 *
		 * @param params {ElementData} Данные элемента
		 */
		init: function (params) {
			
			// Шаблоны по-умолчанию
			this.setTemplates(wdpro.templates.forms);

			// Css классы по-умолчанию
			this.classArr = ['JS_field', 'input', 'wdpro-form-input'];

			if (params) {
				this.initParams(params);
			}
		},


		/**
		 * Инициализация элемента
		 *
		 * @param params {ElementData} Данные элемента
		 */
		initParams: function (params) {
			var self = this;
			
			if (params['*']) params['required'] = true;
			
			if (params['width']) {
				if (typeof params['width'] === 'number')
				params['width'] = params['width'] + 'px';
				params['style'] = params['style'] || '';
				params['style'] += 'width: '+params['width']+';';
			}

			// Запоминаем данные
			this.params = $.extend({

				'autoLeft':  true, // Выравинвать ширину колонки левого текста
				'autoWidth': true // Выравнивать ширину поля field
				//templates: wdpro.templates.forms

			}, params);
			
			// Звездочка "Обязательно для заполнения" установлена
			var requireStarSetted = false;

			// Label
			var labelIdText = '';

			// Иконка
			this.params['icon'] && (function () {

				var icon = self.templates.icon({
					data: self.params
				});

				if (self.params['top']) {
					self.params['top'] += ' ' + icon;
					return true;
				}

				if (self.params['left']) {
					self.params['left'] += ' ' + icon;
					return true;
				}

				if (self.params['right']) {
					self.params['right'] += ' ' + icon;
					return true;
				}

				if (self.params['bottom']) {
					self.params['bottom'] += ' ' + icon;
					return true;
				}

				self.params['left'] += ' ' + icon;

			})();

			// Обработка данных текстовых полей
			var normalizeTextData = function (textData) {
				if (textData)
				{
					if (typeof textData == 'string')
					{
						textData = {
							'text': textData,
							'nowrap': true
						};
					}

					// Если поле обязательно для заполнения
					// и если звездочка еще не установлена
					if (!requireStarSetted && params['required'])
					{
						// Устанавливаем звездочку
						textData['requiredStar'] = wdpro.templates.forms.requiredStar();

						requireStarSetted = true;
					}

					// Label
					if (!labelIdText)
					{
						labelId++;
						labelIdText = 'label_' + labelId;
						textData['labelId'] = labelIdText;
					}

					return textData;
				}
			};

			this.params['left'] = normalizeTextData(this.params['left']);
			this.params['top'] = normalizeTextData(this.params['top']);
			this.params['right'] = normalizeTextData(this.params['right']);
			this.params['bottom'] = normalizeTextData(this.params['bottom']);
			this.params['center'] = normalizeTextData(this.params['center']);

			// Label ID
			if (labelIdText)
			{
				this.params.labelId = labelIdText;
			}

			// Инициализация ключа, по которому элемент находится в списке элементов в форме
			this.initKey();

			// Обязательное поле по звездочке
			wdpro.each(this.params, function (dataValue, dataKey) {
				if (dataValue == '*')
				{
					self.params['required'] = true;
				}
			});

			// Если поле задано в настройках, используем его
			if (this.params['field'])
			{
				this.setFieldBlock(this.params['field']);
			}
		},


		/**
		 * Обновляет параметры запуска формы
		 *
		 * @param params {{}} Параметры запуска формы
		 * @param complete {function} Каллбэк, срабатывающий при завершении проверки
		 */
		submitPermit: function (params, complete) {
			
			var self = this;
			
			var value = this.getValue();
			
			// Основная проверка
			var valid = !(this.params['required'] && !value);
			
			// Все ок
			var ok = function () {
				// Убираем выделение поля как ошибочного
				self.errorViewOff();

				complete(true);
			};
			
			// Ошибка
			var err = function (errorText) {

				// Удаляем html код
				errorText = wdpro.htmlspecialcharsDecode(errorText);
				errorText = wdpro.stripTags(errorText);

				// Останавливаем запуск формы
				params.submit = false;

				// Добавляем ошибку в список ошибок
				if (errorText) {
					params.errorsList.push(errorText);
					params.errorsExists = true;
				}

				// Выделяем поле как ошибочное
				self.errorViewOn();

				complete();
			};
			
			// Все ок
			if (valid) {
				// Дополнительная проверка
				self.valid(value, function (valid) {
					
					if (valid) {
						ok();
					}
					
					else {
						err();
					}
				});
			}
			
			// Ошибка
			else {
				err(self.getLabel());
			}
		},


		/**
		 * Проверяет, правильно ли заполнено поле (дополнительная проверка)
		 * 
		 * @param value {*} Значение
		 * @param Return {function} Каллбэк, получающий результат
		 */
		valid: function (value, Return) {
			
			Return(true);
		},


		/**
		 * Вид поля с ошибкой
		 */
		errorViewOn: function () {
			!this.html || this.html.addClass('form-element-error');
		},


		/**
		 * Вид поля без ошибки
		 */
		errorViewOff: function () {
			!this.html || this.html.removeClass('form-element-error');
		},


		/**
		 * Возвращает описание поля
		 *
		 * @returns {*}
		 */
		getLabel: function () {
			// Если описание поля еще не известно
			if (!this.params['label'])
			{
				// Описание еще не найдено
				var setted = false;

				// Перебираем данные поля
				for (var key in this.params)
				{
					// Если это поле label и поле label езе не установлено
					if (!setted
					    && (
						    key == 'left'
						    || key == 'top'
						    || key == 'right'
						    || key == 'bottom'
						    || key == 'center'
					    )
					)
					{
						// Запоминаем что поле label уже установлено
						setted = true;

						// Устанавливаем поле label
						if (this.params[key]) {
							this.params['label'] = this.params[key].text;
						}
						else {
							this.params['label'] = '';
						}
					}
				}

				// Если label так и не был установлен
				if (!setted)
				{
					// Устанавливаем в качестве label имя поля
					this.params['label'] = this.params['name'];
				}
			}

			return this.params['label'];
		},


		/**
		 * Возвращает html код элемента (поле + label)
		 *
		 * @param callback {function} Каллбэк, принимающий html код элемента
		 */
		getHtml: function (callback) {
			var self = this;

			// Если html блок уже был создан
			if (this.html)
			{
				// Возвращаем его
				callback(this.html);
			}

			// Html блок еще не создавался
			else
			{
				// Группа ожидаемых функций
				var waiter = new wdpro.Waiter();

				// Html код самого поля
				waiter.wait(function (Complete) {
					
					// Если поле уже есть
					if (self.fieldBlock !== undefined)
					{
						// Завершаем ожидание функции
						Complete();
					}

					// Поля еще нету
					else
					{
						// Создаем его
						self.createField(function (fieldHtml) {
							
							// Создаем jQuery объект поля
							var field = $(fieldHtml);

							// Запоминаем
							//self.params['input'] = field;
							self.setFieldBlock(field);

							// Обработка поля
							self.onField(field);

							// Завершаем ожидание функции
							Complete();
						});
					}
				});


				// Html метка самого поля
				waiter.wait(function (Complete) {
					// Метка еще не создавалась
					if (!self.params.inputjQueryMarker)
					{
						// Создаем ее
						self.params.inputjQueryMarker =
							wdpro.jQueryToHtml(self.fieldBlock, 'form');
					}

					// Завершаем ожидание функции
					Complete();
				});


				// При завершении ожидаемых функций
				waiter.onCompletion(function () {
					
					// Создаем html блок
					self.html = $(self.templates.element({
						data:  self.getParams(),
						attrs: self.getAttrs()
					}));
					
					if (self.params['elementClass']) {
						self.html.addClass(self.params['elementClass']);
					}

					// Если ранее устанавливалось значение
					if (self.settedValue)
					{
						// Устанавливаем это значение
						self.setValue(self.settedValue);
					}
					
					self.initCenter();

					// Возвращаем html блок
					callback(self.html);
				});
			}
		},


		/**
		 * Инифиирование описания поля в самом поле 
		 * 
		 * (которое исчезает, когда вводиться текст)
		 */
		initCenter: function () {

			var self = this;
			
			if (this.params['center']) {
				var focused = false;
				
				var update = function () {
					if (self.getValue() != '' || focused) {
						self.center.hide();
					}
					else {
						self.center.show();
						if (self.field.is('.seobit-input-focus')) {
							self.center.addClass('_focused');
						}
						else {
							self.center.removeClass('_focused');
						}
					}
				};

				this.center = this.html.find('.js-field-center');
				this.center.on('mousedown click', function () {
					self.field.focus();
				});
				this.field.on('focus change keyup', update);
				update();
				
				// Фокус
				this.field.on('focus', function () {
						focused = true;
						update();
					})
					.on('blur', function () {
						focused = false;
						update();
					});
			}
		},


		/**
		 * Установка поля
		 *
		 * @param fieldBlock
		 */
		setFieldBlock: function (fieldBlock) {
			// Сохраняем сам блок
			this.fieldBlock = fieldBlock;

			// Сохраняем чисто только поле
			this.field = this.fieldBlock;
			if (!this.field.is('.JS_field'))
			{
				this.field = this.fieldBlock.find('.JS_field');
			}
		},


		/**
		 * Обработка поля
		 *
		 * @param field
		 */
		onField: function (field) {
			var self = this;

			$(field)
				.focus(function () {
					$(this).addClass('seobit-input-focus');
				})
				.blur(function () {
					$(this).removeClass('seobit-input-focus');
				})

				.on('keyup change', function () {
					self.trigger('change');
				})
			;
			
			field.on('focus', function () {
				
			});
		},


		/**
		 * Создает html код самого поля
		 *
		 * @param callback {function} Каллбэк, получающий поле
		 */
		createField: function (callback) {
			callback(null);
		},


		/**
		 * Возвращает данные формы
		 *
		 * @returns {*}
		 */
		getParams: function () {
			var data = wdpro.extend({}, this.params);

			// Инфа о том, что у формы есть поля в первой колонке с левым текстом, 
			// чтобы, например, у кнопки Отправить потом сделать отступ слева
			data.leftExists = this.form ? this.form.leftExists : false;

			return data;
		},


		/**
		 * Создает при необходимости ключ элемента в списке элементов формы
		 */
		initKey: function () {
			// Если ключ не указан
			if (!this.params['key'])
			{
				// Если указано имя
				if (this.params['name'])
				{
					// Имя в виде массива
					if (typeof this.params['name'] == 'object')
					{
						this.params['key'] = this.params['name'].join('_');
					}

					// Имя в виде строки
					else
					{
						this.params['key'] = this.params['name'];
					}
				}
				else
				{
					unnamedKeyN++;
					this.params['key'] = unnamedKeyN;
				}
			}
		},


		/**
		 * Возвращает данные поля из данных всей формы
		 *
		 * @param formData {{}} Данныеформы
		 * @returns {*}
		 */
		getElementValueFromFormData: function (formData) {
			if (!formData)
			{
				return '';
			}

			// Имя в виде массива
			if (typeof this.params['name'] == 'object')
			{
				var elementData = formData;

				wdpro.each(this.params['name'], function (name) {
					if (elementData && elementData[name]) {
						elementData = elementData[name];
					}
					else {
						elementData = null;
					}
				});

				return elementData;
			}

			// Обычное имя
			else
			{
				return formData[this.params['name']];
			}
		},


		/**
		 * Возвращает ключ поля
		 *
		 * @returns {*}
		 */
		getKey: function () {

			return this.getParams()['key'];
		},


		/**
		 * Устанавливает набор шаблонов
		 *
		 * @param templates {{}}
		 */
		setTemplates: function (templates) {
			this.templates = templates;
		},


		/**
		 * Возвращает имя поля, вставляемое в html код
		 *
		 * @returns {string}
		 */
		getName: function () {
			// Список ключей массива, значение которого отображает имя
			var keys = [];

			// Добавляем имя формы
			if (this.form)
			{
				var formName = this.form.getName();
				if (formName)
				{
					keys.push(formName);
				}
			}

			// Добавляем имя, которое указано в поле
			// Имя в виде массива ключей
			if (typeof this.params['name'] == 'object')
			{
				wdpro.each(this.params['name'], function (key) {
					keys.push(key);
				});
			}

			// Имя в виде строки
			else if (typeof this.params['name'] == 'string')
			{
				keys.push(this.params['name']);
			}

			// Нету имени
			else
			{
				return null;
			}


			// Формируем строку имени
			var name = '';

			// Перебираем ключи
			wdpro.each(keys, function (key, i) {
				// Первый ключ
				if (i == 0)
				{
					name += key;
				}

				// Последующие ключи
				else
				{
					name += '[' + key + ']';
				}
			});

			return name;
		},


		/**
		 * Возвращает список атрибутов
		 *
		 * @returns {{}}
		 */
		getAttrs: function () {
			var attrs = {};

			// Стили
			attrs['style'] = this.getStyle();

			// Значение поля
			attrs['value'] = this.getDataValue();

			// Имя поля
			attrs['name'] = this.getName();

			// Классы поля
			attrs['class'] = this.getClass();

			// ID для label
			if (this.params.labelId)
			{
				attrs['id'] = this.params.labelId;
			}

			/**
			 * Атрибуты, которые указаны непосредственно в атрибутах
			 */
			wdpro.each(this.params.attrs, function (attrVaulue, attrKey) {
				attrs[attrKey] = attrVaulue;
			});


			return attrs;
		},


		/**
		 * Возвращает стили поля
		 *
		 * @returns {*}
		 */
		getStyle: function () {
			return this.params['style'];
		},


		/**
		 * Возвращает значение поля из данных
		 *
		 * @returns {*}
		 */
		getDataValue: function () {
			if (typeof this.params['value'] != 'undefined')
			{
				return this.params['value'];
			}

			else
			{
				return '';
			}
		},


		/**
		 * Возвращает значение поля
		 *
		 * @returns {*}
		 */
		getValue: function () {
			
			if (this.field) {
				return this.field.val();
			}
		},


		/**
		 * Установка значения
		 *
		 * @param value
		 */
		setValue: function (value) {
			if (this.field)
			{
				this.field.val(value);
			}

			this.settedValue = value;
		},


		/**
		 * Установка значения по всем значениям формы
		 *
		 * @param value {{}} Значения формы
		 */
		setFormValue: function (value) {
			
			var value = this.getElementValueFromFormData(value);
			
			if (typeof value !== undefined) {
				this.setValue(value);
			}
		},


		/**
		 * Возвращает классы поля
		 *
		 * @returns {string}
		 */
		getClass: function () {
			var classArr = this.classArr.slice();

			if (this.params['class'])
			{
				classArr.push(this.params['class']);
			}

			this.params.attrs = this.params.attrs || {};
			if (this.params['containerClass']) {
				this.params.attrs['JS_class_for_container'] = this.params['containerClass'];
			}

			// Класс указывающий, что у элемента есть класс для контейнера
			if (this.params && this.params.attrs &&
			    this.params.attrs['JS_class_for_container'])
			{
				classArr.push('JS_class_for_container');
			}

			return classArr.join(' ');
		},


		/**
		 * Запоминает объект группы
		 *
		 * @param group {Group} Ссылка на объект группы
		 */
		setGroup: function (group) {
			this.group = group;
		},


		/**
		 * Запоминает объект формы
		 *
		 * @param form {FormAuth} Объект формы
		 */
		setForm: function (form) {
			this.form = form;
		},


		/**
		 * Возвращает true, если у поля есть левый текст
		 *
		 * Нужно для того, чтобы форма знала, есть ли у нее поля в первой колонке с левым текстом,
		 * чтобы потом поставить отвтуп у кнопки "Отправить"
		 *
		 * @returns {boolean}
		 */
		leftExists: function () {
			return this.params['left'] !== null && this.params['left'] !== undefined;
		}
	});


	/**
	 * Строка
	 */
	// export class StringElement extends BaseElement
	var StringElement = wdpro.forms.StringElement = BaseElement.extend({

		/**
		 * Создает HTML код поля
		 *
		 * @param callback {function}
		 */
		createField: function (callback) {
			callback(this.templates.stringField({
				data:  this.getParams(),
				attrs: this.getAttrs()
			}));
		}
	});


	/**
	 * E-mail
	 */
	wdpro.forms.EmailElement = BaseElement.extend({

		/**
		 * Создает HTML код поля
		 *
		 * @param callback {function}
		 */
		createField: function (callback) {
			callback(this.templates.emailField({
				data:  this.getParams(),
				attrs: this.getAttrs()
			}));
		},

		/**
		 * Проверка заполненности поля на корректность
		 *
		 * @param value {*} Значение поля
		 * @param Return {function} Каллбэк, принимающий результат
		 */
		valid: function (value, Return) {
			//var r = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
			var r = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

			if (r.test(value)) {
				Return(true);
			}

			else {
				alert('Пожалуйста, укажите верный e-mail');
				Return(false);
			}
		}
	});

	

	/**
	 * Дата
	 */
	wdpro.forms.DateElement = BaseElement.extend({
		
		
		init: function (data) {
			
			var self = this;
			
			this._super(data);
			
			this.classArr = ['js-date'];
			
			this.on('addedToPage', function () {

				$(self.html.find('.js-date')).datepicker({
					dateFormat : 'yy.mm.dd'
				});
			});
		},
		
		
		/**
		 * Создает HTML код поля
		 *
		 * @param callback {function}
		 */
		createField: function (callback) {
			var params = this.getParams();

			var attrs = this.getAttrs();
			params['fieldName'] = this.getName();

			delete attrs.name;

			callback(this.templates.dateField({
				data:  params,
				attrs: attrs
			}));
		},


		/**
		 * Обработка поля
		 *
		 * @param field
		 */
		onField: function (field) {
			var self = this;
			
			this._super(field);
			
			this.fieldDate = field.find('.js-date');
			
			this.fieldDate.on('change', function () {
				self.valueFronVisibleToHidden();
			});
			setTimeout(function () {
				self.valueFronVisibleToHidden();
			}, 500);
		},


		valueFronVisibleToHidden: function () {

			var stringVal = this.fieldDate.val();
			
			if (stringVal) {
				this.field.val( moment(stringVal+', 12:00:00').unix() );
			}
			
			else {
				this.field.val(0);
			}
			
		},


		/**
		 * Возвращает значение поля
		 *
		 * @returns {*}
		 */
		getValue: function () {

			if (this.field) {
				
				var value = this.fieldDate.val();
				
				if (value) {
					return moment(value+', 12:00:00').unix();
				}
				return 0;
			}
		},


		/**
		 * Установка значения
		 *
		 * @param value
		 */
		setValue: function (value) {
			
			if (this.field)
			{
				if (value > 0) {
					
					var dateString = moment(value * 1000).format('YYYY.MM.DD');

					this.fieldDate.val(dateString);
				}
				this.field.val(value);
			}

			this.settedValue = value;
		}
		
		
	});


	/**
	 * Поле для указания № п.п. (Сортировка)
	 * 
	 * Отличается от StringElement тем, что короче, что у него есть стандартная
	 * приписка слева "№ п.п." и у него имя по-умолчанию "sorting"
	 */
	wdpro.forms.SortingElement = wdpro.forms.StringElement.extend({
		
		init: function (params) {
			
			params = wdpro.extend({
				'left': {
					'text': '№ п.п',
					'nowrap': true
				},
				'name': 'sorting',
				'class': 'wdpro-form-sorting'
			}, params);
			
			if (params['top'] === true) {
				params['top'] = params['left'];
				delete params['left'];
			}
			
			this._super(params);
		},
		
		
		getAttrs: function () {
			
			var attrs = this._super();
			
			attrs['size'] = 5;
			
			return attrs;
		}
	});


	/**
	 * Пароль
	 */
	// export class StringElement extends BaseElement
	var PassElement = wdpro.forms.PassElement = BaseElement.extend({

		/**
		 * Создает HTML код поля
		 *
		 * @param callback {function}
		 */
		createField: function (callback) {
			callback(this.templates.passField({
				data:  this.getParams(),
				attrs: this.getAttrs()
			}));
		}
	});


	/**
	 * Текстовое поле в несколько строк
	 */
	//export class TextElement extends BaseElement
	var TextElement = wdpro.forms.TextElement = BaseElement.extend({

		init: function (data) {
			this._super(data);
			
			this.classArr = ['wdpro-form-textarea', 'wdpro-form-input', 'input', 'JS_field'];
		},
		
		/**
		 * Создает HTML код поля
		 *
		 * @param callback
		 */
		createField: function (callback) {
			callback(this.templates.textField({
				data:  this.getParams(),
				attrs: this.getAttrs()
			}));
		}
	});

	
	var ckEditorI = 1;

	/**
	 * Ckeditor
	 */
	wdpro.forms.CkeditorElement = wdpro.forms.TextElement.extend({
		
		init: function (data) {
			
			var self = this;
			
			data = $.extend({
				'config': 'standart',
				'autoWidth': false,
				'autoLeft': false
			}, data);
			
			this._super(data);
			
			this.classArr = ['js-sckeditor', 'wdpro-form-ckeditor', 'JS_field'];
			
			if (this.params['config'])
			{
				this.config = CKEDITOR.wdproConfigs[this.params['config']];
			}
			if (this.params['width']) {
				this.config.width = this.params['width'];
			}
			if (this.params['configParams'])
			{
				this.config = wdpro.extend(this.config, this.params['configParams']);
			}

			console.log('this.config', this.config);
			
			this.on('addedToPage', function () {
				
				self.html.addClass('wdpro-form-element-ckeditor');
				if (!CKEDITOR.instances[self.htmlId]) {
					var editor = CKEDITOR.replace(self.htmlId, self.config);

					self.on('prepareToGetData', function () {
						self.field.val(editor.getData());
					});
				}
			});
		},
		
		
		onField: function (field) {
			this.ckEditorI = ckEditorI;
			ckEditorI++;
			
			this.htmlId = 'js-ckeditor-'+this.ckEditorI;

			field.attr('id', this.htmlId);
		},
		
		
		creatField: function (callback) {
			
			this._super(callback);
		}
	});


	/**
	 * Чекбокс
	 */
	var CheckElement = BaseElement.extend({

		
		init: function (data) {

			var self = this;

			this._super(data);
			this.classArr = ['wdpro-form-checkbox', 'wdpro-input', 'JS_field'];

			this.on('addedToPage', function () {

				self.fieldBlock.closest('.wdpro-form-element')
					.addClass('wdpro-form-check-container');
			});
		},

		
		/**
		 * Инициализация параметров
		 *
		 * @param params
		 */
		initParams: function (params) {
			params = wdpro.extend({
				'value':                  1,
				'JS_class_for_container': 'checkbox_container',
				'autoWidth': false
			}, params);

			this._super(params);

			// Css классы по-умолчанию
			this.classArr = ['JS_field', 'checkbox'];
		},


		/**
		 * Создает HTML код поля
		 *
		 * @param callback
		 */
		createField: function (callback) {
			var params = this.getParams();
			params['fieldName'] = this.getName();
			
			callback(this.templates.checkField({
				data:  params,
				attrs: this.getAttrs()
			}));
		},


		/**
		 * Возвращает атрибуты
		 *
		 * @returns {*}
		 */
		getAttrs: function () {
			var attrs = this._super();

			attrs['checked'] = this.params['checked'];

			// Это чтобы в параметрах можно было указывать checked как true или 1
			if (attrs['checked']) attrs['checked'] = 'checked';

			return attrs;
		},


		/**
		 * Возвращает значение
		 *
		 * @returns {string}
		 */
		getValue: function () {
			return this.field.is(':checked') ? this.field.val() : '';
		},


		/**
		 * Установка значения
		 *
		 * @param value
		 */
		setValue: function (value) {
			this.field && this.field.prop('checked', value == this.field.val());

			this.settedValue = value;
		}

	});

	/**
	 * @type {CheckElement}
	 */
	wdpro.forms.CheckElement = CheckElement;


	/**
	 * Скрытое поле
	 */
	var HiddenElement = wdpro.forms.HiddenElement = BaseElement.extend({

		/**
		 * Возвращает html код элемента
		 *
		 * @param Return {Function} Каллбэк, принимающий код элемента
		 */
		getHtml: function (Return) {
			Return(this.templates.hiddenField({
				data:  this.getParams(),
				attrs: this.getAttrs()
			}));
		},


		/**
		 * Возвращает CSS классы
		 */
		getClass: function () {

		}
	});


	/**
	 * Обычная кнопка
	 */
	var ButtonElement = wdpro.forms.ButtonElement = BaseElement.extend({

		init: function (data) {
			// Делаем аттрибут value как параметр text
			if (!data['value'] && data['text'])
			{
				data['value'] = data['text'];
			}

			// Добавление в данные дополнительных атрибутов
			data = wdpro.extend({
				attrs: {

					// Css класс для контейнера кнопки
					'JS_class_for_container': 'JS_submit_container',

					// Инфа для Loading
					'data-loading': 'loading_submit'
				}
			}, data);

			this._super(data);

			// Css классы по-умолчанию (класс .submit лучше не добавлять, т.к. он
			// используется в Wordpress)
			this.classArr = ['wdpro-form-button'];
		},


		/**
		 * Создает HTML код поля
		 *
		 * @param callback {function}
		 */
		createField: function (callback) {
			
			if (this.params['tag'] == 'button') {
				callback(this.templates.buttonFieldButtonTag({
					data:  this.getParams(),
					attrs: this.getAttrs()
				}));
			}
			
			else {
				callback(this.templates.buttonField({
					data:  this.getParams(),
					attrs: this.getAttrs()
				}));
			}
		},


		/**
		 * Возвращает данные для шаблона
		 *
		 * @returns {{}}
		 */
		getData_DELETE: function () {
			var data = this._super();

			// Добавляем к элементу кнопки класс, 
			// который добавляет такой же левый отступ, как у предыдущего или следующего элемента
			if (!data['elementClass']) data['elementClass'] = '';
			if (data['elementClass']) data['elementClass']+=' ';
			data['elementClass'] += 'JS_element_add_left_if_need';

			return data;
		}
	});


	/**
	 * Кнопка отправки формы
	 */
	var SubmitElement = wdpro.forms.SubmitElement = ButtonElement.extend({

		init: function (data) {
			this._super(data);

			// Классы кнопки (класс .submit лучше не добавлять, т.к. он
			// используется в Wordpress)
			this.classArr = ['JS_submit', 'wdpro-form-submit'];
		},

		/**
		 * Создает HTML код поля
		 *
		 * @param callback {function}
		 */
		createField: function (callback) {
			callback(this.templates.submitField({
				data:  this.getParams(),
				attrs: this.getAttrs()
			}));
		}
	});


	/**
	 * Кнопка отправки формы с текстом "Сохранить"
	 */
	wdpro.forms.SubmitSaveElement = wdpro.forms.SubmitElement.extend({
		
		init: function (params) {
			
			params = wdpro.extend({
				'text': 'Сохранить',
				'class': 'button button-primary button-large'
			}, params);
			
			this._super(params);
		}
	});


	/**
	 * Поле для загрузки файла
	 */
	wdpro.forms.FileElement = BaseElement.extend({

		/**
		 * Создает HTML код поля
		 *
		 * @param callback
		 */
		createField: function (callback) {
			
			callback(this.templates.fileField({
				data:  this.getParams(),
				attrs: this.getAttrs()
			}));
		},


		/**
		 * Возвращает атрибуты поля
		 * 
		 * @returns {*}
		 */
		getAttrs: function () {
			
			var attrs = this._super();
			
			if (this.params['multiple'])
			{
				attrs['multiple'] = 'multiple';
			}
			
			return attrs;
		},


		/**
		 * Разрешение на отправку формы
		 * 
		 * @param sendParams {{}} Параметры проверки
		 * @param complete {function} Каллбэк, срабатывающий при завершении проверки
		 */
		submitPermit: function (params, complete) {
			
			if (this.uploadInProcess)
			{
				this.submitOnUpload = complete;
			}
			
			else
			{
				complete(true);
			}
		},


		/**
		 * Возвращает значение поля
		 * 
		 * @returns {string}
		 */
		getValue: function () {
			
			return this.fileNameField.val();
		},


		/**
		 * Установка значения
		 *
		 * @param value
		 */
		setValue: function (value) {
			
			if (typeof value === 'string')
			{
				value = [value];
			}
			if (!value) value = [];
			this.settedValue = value;
			this.updateFieldValue();
		},


		/**
		 * Обновляет json строку в поле, которое отправляет значение
		 */
		updateFieldValue: function () {

			if (this.fileNameField)
			{
				this.fileNameField.val(
					wdpro.urlencode(
						JSON.stringify(this.settedValue)
					)
				);
			}
		},


		/**
		 * Обработка поля
		 * 
		 * @param fieldBlock {jQuery} Блок поля
		 */
		onField: function (fieldBlock) {
			
			var self = this;
			
			// Поле, в котором содержиться имя файла, которое и сохраняется в базе
			this.fileNameField = fieldBlock.find('.js-file-name');
			
			this.fileBlockContainer = fieldBlock.find('.js-file_list');
			
			// Загруженный файл
			this.showCurrentFiles();
			
			
			// Change
			this.field.on('change', function (e)
			{
				self.field.loading();
				self.uploadInProcess = true;

				// Данные для отправки
				var files = e.target.files;
				console.log('files', files);

				var data = {
					'action': 'form_file_upload',
					'files': {}
				};
				var formData = new FormData();
				var waiter = new wdpro.Waiter();

				// Добавление файлов
				$.each( files, function( key, file ){
					waiter.wait(function (complete) {
						formData.append('file_' + key, file, file.name);
						complete();
					});

					/*waiter.wait(function (complete) {
						var fr = new FileReader;
						fr.onloadend = function (str) {

							data['files'][key] = {
								'image': fr.result,
								'fileName': file.name
							};
							complete();
						};
						fr.readAsDataURL(file);
					});

					waiter.wait(function (complete) {
						var fr = new FileReader;
						fr.onloadend = function (binary) {

							//formData.append('file_'+key, file, file.name);
							complete();
						};
						fr.readAsBinaryString(file);
					});*/
				});

				// При завершении добавления в данные всех файлов
				waiter.run(function () {

					// Отправляем данные на сервер
					var ajax = {
						url: wdpro.ajaxUrl({
							'action': 'form_file_upload',
						}),
						type: 'POST',
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						//contentType: 'multipart/form-data',

						// Ответ сервера
						success: function (json) {

							var response = wdpro.parseJSON(json);
							self.field.loadingStop();

							// Все верно
							if (typeof response.error == 'undefined')
							{
								// Удаляем все файлы, если поле не multiple
								if (!self.params['multiple'])
								{
									self.settedValue = [];
								}

								// Запоминаем, что поле готово к отправке формы
								self.uploadInProcess = false;

								// Запоминаем имя файла
								wdpro.each(response['files'], function (file) {
									self.addFile('ZIP: ' + file);
								});
								//self.setValue('ZIP: ' + response['fileName']);

								self.submitOnUpload && self.submitOnUpload();
								self.submitOnUpload = null;

								self.updateFieldValue();
								self.showCurrentFiles();
							}

							// Ошибка
							else
							{
								self.field.loadingStop();
								self.fileBlockContainer.append('<p>При загрузке файла произошла ошибка: ' + response.error+'</p>');
							}
						},

						error: function (jqXHR, testStatus, errorThrow) {
							self.field.loadingStop();
							console.error('При загрузке файла произошла ошибка', jqXHR.getAllResponseHeaders());
						}
					};
					console.log('ajax', ajax);
					$.ajax(ajax);
				});


			});
		},


		/**
		 * Добавление файла
		 * 
		 * @param file {string} Имя файла
		 */
		addFile: function (file) {

			this.settedValue = this.settedValue || [];

			this.settedValue.push(file);
		},


		/**
		 * Отображает текущий файл
		 */
		showCurrentFiles: function () {
			
			var self = this;
			
			this.fileBlockContainer.empty();

			wdpro.each(this.settedValue, function (fileName)
			{
				self.showFileAdd(fileName);
			});
			
			this.fileBlockContainer.sortable({
				containment: 'parent',
				stop: function () {
					self.settedValue = [];
					self.fileBlockContainer.children().wdpro_each(function (fileElement)
					{
						self.settedValue.push(fileElement.attr('data-file'));
					});
					self.updateFieldValue();
				}
			});
		},


		/**
		 * Добавить файл в отображаемую зону под полем для загрузки файлов
		 * 
		 * @param fileName {string} Имя файла
		 */
		showFileAdd: function (fileName) {

			var self = this;
			
			if (fileName)
			{
				var url;
				var originalFileName = fileName;

				if (fileName.indexOf('ZIP: ') != -1)
				{
					fileName = fileName.replace('ZIP: ', '');
				}
				else
				{
					url = this.params['dirUrl'] + fileName;
				}

				var fileBlock = $(wdpro.templates.forms.fileLoaded({
					name: fileName,
					url: url
				}));

				fileBlock.find('.js-del').click(function ()
				{
					self.fileDel(originalFileName);
				});

				fileBlock.attr('data-file', originalFileName);
				self.fileBlockContainer.append(fileBlock);
			}
		},


		/**
		 * Удаление файла
		 */
		fileDel: function (fileName) {

			this.settedValue = this.settedValue || [];
			wdpro.arrayRemoveValue(this.settedValue, fileName);
			this.showCurrentFiles();
			this.updateFieldValue();
		}
	});
	

	/**
	 * Поле для загрузки изображения
	 */
	wdpro.forms.ImageElement = wdpro.forms.FileElement.extend({


		showFileAdd: function (fileName) {

			var self = this;
			
			if (fileName)
			{
				var url;
				var originalFileName = fileName;

				if (fileName.indexOf('ZIP: ') != -1)
				{
					fileName = fileName.replace('ZIP: ', '');
					this._super(originalFileName);
					return false;
				}
				else
				{
					if (fileName.search(/^http:\/\//) != -1) {
						url = fileName;
					}
					else {
						url = this.params['dirUrl'] + fileName;
					}
				}

				var fileBlock = $(wdpro.templates.forms.imageLoaded({
					name: fileName,
					url: url
				}));

				fileBlock.find('.js-del').click(function ()
				{
					self.fileDel(originalFileName);
				});

				// Загрузка фото
				var img = new Image;
				img.onload = function ()
				{
					wdpro.sizeToWidth(img, 170);
					fileBlock.find('a').append(img);
				};
				img.src = url;

				fileBlock.attr('data-file', originalFileName);
				self.fileBlockContainer.append(fileBlock);
			}
		}


	});


	/**
	 * Select
	 */
	wdpro.forms.SelectElement = wdpro.forms.BaseElement.extend({
		
		init: function (data) {
			
			var self = this;
			
			this._super(data);
			if (data['value']) {
				this.setValue(data['value']);
			}
			this.classArr = ['wdpro-form-select', 'JS_field'];
			
			this.on('addedToPage', function () {
				
				self.fieldBlock.parent().addClass('wdpro-form-select-container');
			});
			
			// Преобразование options ''=>'' в ''=>[key, value]
			if (this.params['options']) {
				var options = [];
				wdpro.each(this.params['options'], function (value, key) {
					
					// 'key'=>'value'
					if (typeof value != 'object') {
						options.push([key, value]);
					}
					
					// 1=>[key, value]
					else {
						options.push(value);
					}
				});
				this.params['options'] = options;
			}
		},
		
		createField: function (callback) {
			
			callback(this.templates.selectField({
				data:  this.getParams(),
				attrs: this.getAttrs()
			}));
		},
		
		
		getAttrs: function () {
			var attrs = this._super();
			delete attrs['value'];
			
			if (this.params['multiple']) {
				attrs['multiple'] = 'multiple';
			}
			
			return attrs;
		},
		
		
		getName: function () {
			
			var name = this._super();
			
			if (this.params['multiple']) name += '[]';
			
			return name;
		},
		
		
		onField: function (field) {
			
			var self = this;

			field.val(this.settedValue);
			
			field.on('change', function () {
				self.trigger('change');
			});
		},


		/**
		 * Возвращает данные поля из данных всей формы
		 *
		 * @param formData {{}} Данныеформы
		 * @returns {*}
		 */
		getElementValueFromFormData: function (formData) {
			if (!formData)
			{
				return '';
			}
			
			var ret = null;

			// Имя в виде массива
			if (typeof this.params['name'] == 'object')
			{
				var elementData = formData;
				
				wdpro.each(this.params['name'], function (name) {

					if (elementData)
					{
						elementData = elementData[name];
					}
					else
					{
						elementData = undefined;
					}
				});

				ret = elementData;
			}

			// Обычное имя
			else
			{
				ret = formData[this.params['name']];
			}
			
			if (ret === undefined) 
			ret = this.params['value'];
			
			return ret;
		}

	});


	/**
	 * Checks
	 *
	 * Это примерно то же самое, что и select multyple, только несколько элементов можно выделять
	 * чекбоксами
	 */
	wdpro.forms.ChecksElement = wdpro.forms.BaseElement.extend({

		init: function (data) {
			var self = this;

			if (!data['disabled']) {
				data['disabled'] = [];
			}
			if (data['disabled']) {
				var disabled = {};
				wdpro.each(data['disabled'], function (id) {
					disabled[id] = true;
				});
				data['disabled'] = disabled;
			}

			this._super(data);

			if (data['value']) {
				this.setValue(data['value']);
			}

			this.on('prepareToGetData', function () {
				self.updateHiddenValue();
			});
		},



		createField: function (callback) {

			console.log({
				data:  this.getParams(),
				attrs: this.getAttrs()
			});

			callback(this.templates.checksField({
				data:  this.getParams(),
				attrs: this.getAttrs()
			}));
		},


		/**
		 * Обработка уже jquery полей
		 *
		 * @param field
		 */
		onField: function (field) {
			var self = this;

			this.checks = field.find('.js-checks-check').on('change', function () {
				self.updateHiddenValue();
			});

			this.hiddens = field.find('.js-checks-hiddens');

			this.updateHiddenValue();

			if (this.savedValues) {
				this.setValue(this.savedValues);
				this.savedValues = null;
			}
		},


		/**
		 * Установка значения
		 *
		 * @param values {array} Значения
		 */
		setValue: function (values) {
			var self = this;

			if (this.checks) {
				this.checks.prop('checked', false);

				wdpro.each(values, function (value) {
					self.checks.filter('[data-value="'+value+'"]').prop('checked', true);
				});

				this.updateHiddenValue();
			}

			else {
				this.savedValues = values;
			}

		},


		/**
		 * Обновление отправляемых данных исходя их отмеченных чекбоксов
		 */
		updateHiddenValue: function () {
			var self = this;

			this.hiddens.empty();
			var i = 0;

			this.checks.each(function () {
				var check = $(this);

				if (check.is(':checked')) {
					var hidden = $('<input name="'+self.getName()+'['+i+']" type="hidden" />');
					self.hiddens.append(hidden);
					hidden.attr('value', check.attr('data-value'));
					i ++;
				}
			});


			// Ничего не выбрано
			if (!i) {
				var hidden = $('<input name="'+self.getName()+'" type="hidden" value="null" />');
				self.hiddens.append(hidden);
			}

		}

	});



	/**
	 * Просто html блок в форме
	 */
	wdpro.forms.HtmlElement = wdpro.forms.BaseElement.extend({

		getHtml: function (callback) {
			
			callback(this.params['html']);
		}
	});


	/**
	 * Текстовое поле с кнопками + и -
	 */
	wdpro.forms.SpinnerElement = wdpro.forms.StringElement.extend({

		init: function (params) {
			
			params = wdpro.extend({
				'min': 0,
				'max': null
			}, params);
			
			this._super(params);
		},
		
		createField: function (callback) {

			callback(this.templates.spinnerField({
				data:  this.getParams(),
				attrs: this.getAttrs()
			}));
		},
		
		
		onField: function (field) {
			
			var self = this;
			
			this._super(field);
			
			this.fieldBlock.find('.js-spinner-button').click(function () {
				
				var delta = Number($(this).attr('data-delta'));
				
				var currentVal = Number(self.getValue());
				currentVal += delta;

				if (self.params['min'] !== null) {
					currentVal = Math.max(self.params['min'], currentVal);
				}
				if (self.params['max'] !== null) {
					currentVal = Math.min(self.params['max'], currentVal);
				}
				
				self.setValue(currentVal);
				self.trigger('change');
			});
		}
	});
	

	// Набор классов элементов, по этому списку их можно будет найти через скрипты
	var ElementsClasses = {
		'Base':   BaseElement,
		'String': StringElement,
		'Sorting': wdpro.forms.SortingElement,
		'SortingTop': wdpro.forms.SortingElement,
		'Pass':   PassElement,
		'Text':   TextElement,
		'Hidden': HiddenElement,
		'Button': ButtonElement,
		'Submit': SubmitElement,
		'SubmitSave': wdpro.forms.SubmitSaveElement,
		'Check':  CheckElement,
		'Checks':  wdpro.forms.ChecksElement,
		'File':   wdpro.forms.FileElement,
		'Image':  wdpro.forms.ImageElement,
		'Ckeditor': wdpro.forms.CkeditorElement,
		'CkeditorSmall': wdpro.forms.CkeditorElement,
		'Select': wdpro.forms.SelectElement,
		'Html': wdpro.forms.HtmlElement,
		'Spinner': wdpro.forms.SpinnerElement,
		'Email': wdpro.forms.EmailElement,
		'Date': wdpro.forms.DateElement
	};


})(jQuery);
			


















