<?php
// Console Menus
require(__DIR__ . '/adminMenu.php');

// JS
wdpro_add_script_to_console(__DIR__ . '/js/ready.console.js');
add_action('admin_enqueue_scripts', function () {
	wp_enqueue_script("jquery-ui-core");
	wp_enqueue_script("jquery-ui-sortable");
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
});

// app/console.js
// По-умолчанию
wdpro_default_file(__DIR__ . '/js/default/app.console.js', __DIR__ . '/../app/console.js');
// Подключение
if (is_file(__DIR__ . '/../app/console.js')) {
	wdpro_add_script_to_console(__DIR__ . '/../app/console.js');
}

// Console Css
wdpro_less_compile_try(__DIR__ . '/css/console.less', __DIR__ . '/css/console.less.css');
wdpro_add_css_to_console(__DIR__ . '/css/console.less.css');

// Font Avesome
// 4
if (!wdpro_get_option('wdpro_font_awesome_5_console'))
	wdpro_add_css_to_console(__DIR__ . '/css/font-awesome.min.css');
// 5
// modules/extra/fontAwesome5/Controller.php

$appConsoleCssFile = APP_PATH . 'console.less';
if (is_file($appConsoleCssFile)) {
	wdpro_less_compile_try($appConsoleCssFile, $appConsoleCssFile . '.css');
	wdpro_add_css_to_console($appConsoleCssFile . '.css');
}

/**
 * Дополнительная обработка хлебных крошек
 *
 * @param callback $callback Каллбэк, в который отправляется объект хлебных крошек
 */
function wdpro_breadcrumbs_init($callback)
{
	// Это просто заглушка, это нужно на сайте, но не в админке
	// Но админка заходит в functions.php в теме и там может запускаться эта функция
}

do_action('wdpro-ready');
Wdpro\Modules::run('initConsoleStart');

Wdpro\Modules::run('run');
Wdpro\Modules::run('runConsoleStart');
do_action('app-ready');


// Удаляем лишник кнопки
add_action('admin_menu', function () {

	if (wdpro_get_option('remove_edit')) {
		remove_menu_page('edit.php');
	}

	if (wdpro_get_option('remove_edit-comments')) {
		remove_menu_page('edit-comments.php');
	}

	if (wdpro_get_option('remove_upload')) {
		remove_menu_page('upload.php');
	}

	if (wdpro_get_option('remove_tools')) {
		remove_menu_page('tools.php');
	}
});


register_activation_hook(__FILE__, function () {
	$permalink_structure = get_option('permalink_structure');

	if (!$permalink_structure) {
		update_option('permalink_structure', '/%postname%/');
	}
});


// Стили Ckeditor
if (!is_file(__DIR__ . '/../app/ckeditor.less.css') && is_dir(__DIR__ . '/../app')) {

	file_put_contents(__DIR__ . '/../app/ckeditor.less.css', '');
}
if (is_file(__DIR__ . '/../app/ckeditor.less')) {
	wdpro_less_compile_try(
		__DIR__ . '/../app/ckeditor.less',
		__DIR__ . '/../app/ckeditor.less.css');
}


// Отключение ревизий
if (wdpro_get_option('wdpro_disable_revisions')) {
	function my_revisions_to_keep($revisions)
	{
		return 0;
	}

	add_filter('wp_revisions_to_keep', 'my_revisions_to_keep');
}

// Ajax
add_action('admin_enqueue_scripts', function () {
	wp_localize_script('wdpro.core.all', 'wdproData', array(
		'ajaxUrl' => wdpro_ajax_url(),
		'homeUrl' => wdpro_home_url() . '/',
		'imagesUrl' => WDPRO_UPLOAD_IMAGES_URL,
	));
});


add_filter( 'plugin_action_links', function ($actions, $plugin_file, $plugin_data, $context) {

		if ($plugin_file === 'wordpressmvc/wordpressmvc.php') {

			array_unshift($actions, '<a href="https://github.com/mavlutovr/wordpressmvc/" target="_blank"><img src="https://webdeveloper.pro/wp-content/plugins/wordpressmvc/logo.svg" alt=""></a>');

			array_unshift($actions, '<a href="'.admin_url().'options-general.php?page=wdproOptions">Настройки</a>');

			array_unshift($actions, '<a href="https://github.com/mavlutovr/wordpressmvc/tree/master/Wiki" target="_blank">С чего начать</a>');
	}

	return $actions;
}, 10, 4);