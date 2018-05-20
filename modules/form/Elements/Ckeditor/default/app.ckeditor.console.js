// wdpro/modules/form/Elements/Ckeditor/default/app.ckeditor.console.js
window.ckeditorConfigEditor = function (config) {

	// Подключение дополнительных плагинов
	// config['standart'].extraPlugins += ',insertpre';
	CKEDITOR.dtd.$removeEmpty['i'] = false;

	return config;
};