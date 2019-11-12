/**
 * Плагин для простой вставки фотки в текст
 */
CKEDITOR.plugins.add(
	'simple_uploader', 
	{
		// Инициализация
		init: function (editor)
		{
			// Путь до папки плагина
			var mypath = this.path;
			
			
			// Картинка
			// Добавляем кнопку загрузки изображений
			editor.ui.addButton(
				'simple_uploader_photo',
				{
					label: 'Вставить изображение',
					command: 'simple_uploader_insert_photo',
					icon: mypath+'photo.png'
				}
			);

			// Добавляем кнопку загрузки файла
			editor.ui.addButton(
				'simple_uploader_file',
				{
					label: 'Вставить файл',
					command: 'simple_uploader_insert_file',
					icon: mypath+'file.png'
				}
			);
			
			// Обработка комманды
			editor.addCommand( 'simple_uploader_insert_photo', new CKEDITOR.dialogCommand( 'simple_uploader_insert_photo' ) );
			
			// Окошко
			CKEDITOR.dialog.add( 'simple_uploader_insert_photo', this.path + 'dialog.js' );
			
			
			// Файл
			// Кнопка
			editor.ui.addButton(
				'simple_uploader_file',
				{
					label: 'Вставить файл',
					command: 'simple_uploader_insert_file',
					icon: mypath+'file.png'
				}
			);

			// Обработка комманды
			editor.addCommand( 'simple_uploader_insert_file', new CKEDITOR.dialogCommand( 'simple_uploader_insert_file' ) );

			// Окошко
			CKEDITOR.dialog.add( 'simple_uploader_insert_file', this.path + 'dialog.js' );
		}
	}
);
