// wdpro/modules/form/Elements/Ckeditor/default/app.ckeditor.console.js
window.ckeditorConfigEditor = function (config) {

	// Подключение дополнительных плагинов
	// config['standart'].extraPlugins += ',insertpre';
	CKEDITOR.dtd.$removeEmpty['i'] = false;

	/*CKEDITOR.stylesSet.add( 'my_styles', [
		// Block-level styles.
		{ name: 'Blue Title', element: 'h2', styles: { color: 'Blue' } },
		{ name: 'Red Title',  element: 'h3', styles: { color: 'Red' } },

		// Inline styles.
		{ name: 'CSS Style', element: 'span', attributes: { 'class': 'my_style' } },
		{ name: 'Marker: Yellow', element: 'span', styles: { 'background-color': 'Yellow' } }
	]);*/

	return config;
};