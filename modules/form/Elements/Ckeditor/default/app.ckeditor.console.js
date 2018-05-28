// wdpro/modules/form/Elements/Ckeditor/default/app.ckeditor.console.js
window.ckeditorConfigEditor = function (config) {

	// Подключение дополнительных плагинов
	// config['standart'].extraPlugins += ',insertpre';
	CKEDITOR.dtd.$removeEmpty['i'] = false;

	// Стили
	/*wdpro.ckeditor.styles.push({
		name: 'Синий заголовок',
		element: 'h2',
		attributes: { 'class': 'blue' }
	});*/

	return config;
};