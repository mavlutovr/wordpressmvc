CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

	config.resize_dir = 'both';
	config.resize_maxWidth = 800;
	config.resize_maxHeight = 600;
};

(function ($) {
	
	wdpro.ckeditor = {

		/**
		 * Стили по-умолчанию
		 */
		styles: [
			{
				'name': 'Выключение обтекания',
				'element': 'div',
				'attributes': { 'class': 'g-clear' }
			}
		],

		/**
		 * Добавляет стиль в редактор
		 * 
		 * @param style {{ name: string, element: string, attributes: {}|null }}
		 */
		addStyle: function (style) {

			wdpro.ckeditor.styles.push(style);

			wdpro.each(CKEDITOR.wdproConfigs, function (data) {

				//data['stylesSet'] && data['stylesSet'].push(style);
			});
		},

		/**
		 * Добавляем плагин
		 * 
		 * @param pluginName {string} Имя плагина (имя папки)
		 * @param [configName] {string} Редактор "standart" или "small"
		 */
		addPlugin: function (pluginName, configName) {
			configName = configName || 'standart';
			CKEDITOR.wdproConfigs[configName].extraPlugins += ','+pluginName;
		},

		//contentsCss:
		// CKEDITOR.basePath+'../../../../../../../plugins/app/ckeditor.less.css'

		contentsCss: CKEDITOR.getUrl('../../../../../../../plugins/app/ckeditor.less.css')
	};
	
	
	CKEDITOR.wdproConfigs = {

		// Стандартный редактор
		'standart': {
			
			// Отключить отключалку орфографии
			'disableNativeSpellChecker': false,

			// Стили
			'stylesSet': wdpro.ckeditor.styles,

			// Доп Плагины
			'extraPlugins': 'image,sourcedialog,simple_uploader,tabletools,tableresize,youtube,wpmore,resize',

			// Панель
			'toolbar': [
				[ 'Format',
					'FontSize',
					'Bold',
					'Italic',
					'TextColor',
					'Subscript',
					'Superscript',
					'FontSize',
					'Font',
					'Styles'
				],
				'/',
				[ 'simple_uploader_file', 'simple_uploader_photo', 'Image'],['EmojiPanel'],['Table'],['WPMore'], ['Link', 'Unlink', 'Anchor' ], [ 'Youtube', 'InsertPre' ],
				[ 'PasteText', 'PasteFromWord', 'RemoveFormat'],
				[ 'JustifyLeft', 'JustifyCenter','JustifyRight','JustifyBlock' ],
				[ 'NumberedList', 'BulletedList' ],
				[ 'Sourcedialog' ]
			],
			
			width: '100%',
			resize_maxWidth: 800,
			resize_enabled: true,

			contentsCss: wdpro.ckeditor.contentsCss,

			allowedContent: true,

			// Это чтобы кавычки не превращались в &quot;
			entities: false

			//uiColor: '#F5F5F5',

			//width: '100%'
		},


		// Маленький
		'small': {

			// Стили
			'stylesSet': wdpro.ckeditor.styles,

			// Доп Плагины
			'extraPlugins': 'image,sourcedialog,simple_uploader,tabletools,tableresize,youtube,resize',

			// Панель
			'toolbar': [
				[ 'Format',
					'Bold',
					'Italic'
				],
				[ 'simple_uploader_photo', 'Image'], ['Link', 'Unlink' ],
				[ 'NumberedList', 'BulletedList' ],
				[ 'Sourcedialog' ]
			],

			contentsCss: wdpro.ckeditor.contentsCss,

			//uiColor: '#F5F5F5',

			//width: '100%',
			resize_maxWidth: 800,
			resize_enabled: true,
			resize_dir: 'both',
			
			height: '100px',

			removePlugins : 'elementspath',

			toolbarLocation: 'bottom',

			allowedContent: true,

			// Это чтобы кавычки не превращались в &quot;
			entities: false
		}
	};
	
	if (window.ckeditorConfigEditor) {

		CKEDITOR.wdproConfigs = window.ckeditorConfigEditor(CKEDITOR.wdproConfigs);
	}
	
})(jQuery);