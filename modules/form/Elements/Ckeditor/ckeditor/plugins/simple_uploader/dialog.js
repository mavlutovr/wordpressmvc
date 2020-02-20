(function ()
{
	/**
	 * Папка, куда загружаются все файлы
	 * 
	 * @type {string}
	 */
	//var Dir = '../wp-content/uploads/ckeditor/';
	var Dir = wdpro.WDPRO_UPLOAD_CKEDITOR_URL;
	console.log('Dir', Dir);
	
	// Определение папки, в которой находится скрипт
	(function () {
		var scripts = document.getElementsByTagName('script');
		
		for (var n in scripts) {
			//console.log(scripts[n].src);
			var thisScript = scripts[n];
			if (thisScript.src) {
				var path = thisScript.src.replace(/\/script\.js$/, '/');
				if (path.indexOf('wp-content/plugins/wdpro/modules/form/Elements/Ckeditor/ckeditor/plugins/simple_uploader/dialog.js') != -1) {

					path = path.replace(/\?.*/, '');
					path = path.replace('wp-content/plugins/wdpro/modules/form/Elements/Ckeditor/ckeditor/plugins/simple_uploader/dialog.js', '');

					Dir = path+'wp-content/uploads/ckeditor/';
				}
			}
		}

	})();
	console.log('Dir', Dir);



	// Объект текущего диалогового окна
	var currentDialog;

	/**
	 * ID диалогового окна, чтобы сохранять их в списке диалоговых окон
	 * 
	 * @type {number}
	 */
	var dialogId = 10;

	// Текущая менюшка выравнивания
	var currentAlignSelect;
	
	
	// Текущее поле текста ссылки для скачивания файла
	var currentFileTextInput;
	
	
	/**
	 * Методы вставки файла в зависимости то типа
	 * @type {{image: Function, file: Function}}
	 */
	var fileInsertByType = {
		
		// Картинка
		image: function (params)
		{
			// Получаем объект редактора по ID
			var editor = editorById[params.editorId];
			
			// Создаем объект изображения
			var img = editor.document.createElement('img');
			img.setAttribute('src', Dir+params.file);
			img.setAttribute('width', params.width);
			img.setAttribute('height', params.height);
			img.setAttribute('border', 0);
			img.className = 'border1';
			
			// Если есть менюшка выравнивания
			if (currentAlignSelect)
			{
				// Получаем текущее выравнивание
				var align = currentAlignSelect.getValue();
				
				// Устанавливаем выравнивание
				if (align)
				{
					img.setAttribute('align', align);
				}
			}
			
			// Вставляем изображение в редактор
			editor.insertElement(img);

			// Закрываем диалог
			CKEDITOR.dialog.getCurrent().hide();
		},
		
		
		// Файл
		file: function (params)
		{
			// Получаем объект редактора по ID
			var editor = editorById[params.editorId];
			
			// Selection
			var selection = editor.getSelection();
			var text = selection.getSelectedText();
			var element = selection.getSelectedElement();
			
			// Если есть выделенный элемент
			if (element)
			{
				var linkHtml = element.getOuterHtml();
			}
			
			// Если есть выделенный текст
			else if (text)
			{
				var linkHtml = text;
			}
			
			// Ничего не выделено
			else
			{
				// Текст ссылки из поля
				var linkHtml = currentFileTextInput.getValue();
			}

			// Создаем объект ссылки
			var a = CKEDITOR.dom.element.createFromHtml( 
				'<a href="'+(Dir+params.file)+'">'+linkHtml+'</a>', 
				editor.document 
			);
			
			// Вставляем ссылку в редактор
			editor.insertElement(a);
			
			// Закрываем диалог
			CKEDITOR.dialog.getCurrent().hide();
		}
		
	};


	/**
	 * Список редакторов по их id, чтобы хранить редакторы, которые запроили данный плагин
	 * 
	 * @type {{}}
	 */
	var editorById = {};


	/**
	 * Функция, которая запускается при завершении загрузки файла или изображения
	 * 
	 * @param params {{}} Данные о загруженном файле
	 */
	window.uploaded = function (params)
	{
		console.log('uploaded', params);

		// Если есть имя файла
		if (params.file && fileInsertByType[params.type])
		{
			fileInsertByType[params.type](params);
		}
	};
	
	
	// Фотка
	CKEDITOR.dialog.add( 'simple_uploader_insert_photo', function( editor ) {
		
		// Сохраняем редактор в списке редакторов, чтобы по завершении загрузки файла получить к нему доступ по id
		editorById[editor.id] = editor;
		
		// Путь к папке плагина
		var pluginPath = editor.plugins.simple_uploader.path;

		// Путь к скрипту загрузки
		var uploaderScript = pluginPath + 'uploader.php?type=image&editorId='+editor.id;

		// Имя загружаемого файла
		var uploaderFileName = '';

		return {

			// Заголовок окна
			title: 'Выберите изображение',
			
			resizable: false,

			width: 300,
			height: 120,
			
			// При загрузке
			onLoad: function ()
			{
				// Скрываем страницу Loading
				this.hidePage('loadingContent');
			},

			// Форма
			contents: [
				{
					id: 'formContent',
					elements: [
						{
							type: 'vbox',
							padding: 10,
							children: [
									  
								// File Input
								{
									type: 'file',
									id: 'uploaderInput',
									action: uploaderScript,
									//label: 'Выберите изображение',
									onChange: function (e)
									{
										uploaderFileName = e.data.value;
									}
								},
									  
								// Align Select
								{
									type: 'select',
									id: 'alignSelect',
									items: [
										['Без выравнивания', ''],
										['Слева (текст справа)', 'left'],
										['Справа (текст слева)', 'right'],
										['Центр', 'middle'],
										['Совсем посередине', 'absmiddle']
									]
								}
							]
						}
					]
				},
				{
					id: 'loadingContent',
					elements: [
						{
							type: 'html',
							html: '<div style="font-size: 26px;">Загрузка...</div>'
						}
					]
				}
			],


			// При клике на OK
			onOk: function ()
			{
				// Если файл выбран
				if (uploaderFileName)
				{
					// Отправляем файл на сервер
					// Получаем объект поля файла
					var fileInput = this.getContentElement('formContent', 'uploaderInput');

					// Запускаем отправку файлов
					fileInput.submit();
					
					// Показываем страницу Loading, убираем форму
					this.showPage('loadingContent');
					this.hidePage('formContent');

					// Сохраняем менюшку выравнивания
					currentAlignSelect = this.getContentElement('formContent', 'alignSelect');
				}

				// Файл не выбран
				else
				{
					// Показываем сообщение об этом
					alert('Выберите файл');
				}

				return false;
			}

		};
	});



	// Файл
	CKEDITOR.dialog.add( 'simple_uploader_insert_file', function( editor ) {

		// Сохраняем редактор в списке редакторов, чтобы по завершении загрузки файла получить к нему доступ по id
		editorById[editor.id] = editor;

		// Путь к папке плагина
		var pluginPath = editor.plugins.simple_uploader.path;

		// Путь к скрипту загрузки
		var uploaderScript = pluginPath + 'uploader.php?type=file&editorId='+editor.id;
		console.log(uploaderScript);

		// Имя загружаемого файла
		var uploaderFileName = '';
		
		//editor


		return {

			// Заголовок окна
			title: 'Выберите файл',

			width: 300,
			height: 80,

			// При загрузке
			onLoad: function ()
			{
				// Скрываем страницу Loading
				this.hidePage('loadingContent');
				//this.showPage('formContent');
			},
			
			// При открытии
			onShow: function ()
			{
				// Скрываем страницу Loading
				this.hidePage('loadingContent');
				//this.showPage('formContent');
			},

			// Форма
			contents: [
				{
					id: 'formContent',
					elements: [
						{
							type: 'vbox',
							padding: 10,
							children: [

								// Файл
								{
									type: 'file',
									id: 'uploaderInput',
									action: uploaderScript,
									//label: 'Выберите изображение',
									onChange: function (e)
									{
										uploaderFileName = e.data.value;
									}
								},
									  
									  
								// Текст ссылки
								{
									type: 'text',
									id: 'fileText',
									label: 'Текст ссылки для скачивания файла',
									// 'default': 'Скачать файл',
									onShow: function (e)
									{
										// Устанавливаем либо выделенный текст, либо текст по-умолчанию
										var selection = editor.getSelection();
										var text = selection.getSelectedText();
										var element = selection.getSelectedElement();
										/*console.log(element.getOuterHtml());*/
										
										// Если ничего не выделенно
										if (!text && !element)
										{
											// Показываем текст "Скачать файл"
											//this.setValue(text);
											this.setValue('Скачать файл');
											
											// Показываем поле текста
											this.getElement().show();
											
											// Скрываем страницу Loading
											this.selectParentTab();
										}
										
										// Если что-нибудь выделено
										else
										{
											// Скрываем поле текста
											this.getElement().hide();
										}
									}
								}
							]
						} 
					]
				},
				{
					id: 'loadingContent',
					elements: [
						{
							type: 'html',
							html: '<div style="font-size: 26px;">Загрузка...</div>'
						}
					]
				}
			],


			// При клике на OK
			onOk: function ()
			{
				// Если файл выбран
				if (uploaderFileName)
				{
					// Отправляем файл на сервер
					// Получаем объект поля файла
					var fileInput = this.getContentElement('formContent', 'uploaderInput');

					// Запускаем отправку файлов
					fileInput.submit();

					// Показываем страницу Loading, убираем форму
					this.showPage('loadingContent');
					this.hidePage('formContent');

					// Сохраняем менюшку выравнивания
					currentFileTextInput = this.getContentElement('formContent', 'fileText');
				}

				// Файл не выбран
				else
				{
					// Показываем сообщение об этом
					alert('Выберите файл');
				}

				return false;
			}

		};
	});












})();