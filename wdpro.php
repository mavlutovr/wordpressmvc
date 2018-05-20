<?php
/*
 * Plugin Name: WebDeveloper.pro Базовый
 * Description: Базовый плагин для разработки сайтов от <a href="http://webdeveloper.pro/" target="_blank">webdeveloper.pro</a>. Системные требования: PHP5.5
 * Version: 0.1.2
 * Author: Roman Mavlutov
 * Author URI: http://webdeveloper.pro/
 */

// Standart Functions
require(__DIR__ . '/inc/functions.php');

if (!wdpro_is_session_started())
	session_start();

// Constants
require(__DIR__ . '/inc/constants.php');

// AutoLoad
require(__DIR__ . '/inc/autoload.php');

// Templates
require(__DIR__ . '/Templates.php');

// Обработка ошибок
require(__DIR__.'/inc/errors.php');

// JavaScript
add_action('wp_enqueue_scripts', function ()
{
	wp_enqueue_script("jquery");
	wp_register_script('wdpro', WDPRO_URL.'js/core.all.js');
	wp_enqueue_script('wdpro');
});
wdpro_add_script_to_console(__DIR__.'/js/core.all.js');
//wdpro_add_script_to_site(__DIR__.'/js/core.all.js');
wdpro_add_script_to_console(__DIR__.'/js/ready.all.js');
wdpro_add_script_to_site(__DIR__.'/js/ready.all.js');
wdpro_add_script_to_console(__DIR__.'/js/moment.js');
//wdpro_add_script_to_console(__DIR__.'/js/jquery.serializeObject.min.js');

// ClosureCcompiler(Soy)
require(__DIR__ . '/inc/soy.php');

// ClosureCcompiler(Soy)
require(__DIR__ . '/inc/less.php');

// Additional fields for pages (title, keywords, description, h1, перелинковка)
require(__DIR__ . '/inc/additionalFields.php');

// Ajax
if (defined('DOING_AJAX') && DOING_AJAX)
{
	$ajaxCallback = function () {
		
		if (isset($_GET['action']) && $_GET['action'] === 'wdpro')
		{
			$wdproAction = $_GET['wdproAction'] ? $_GET['wdproAction'] : '';
			do_action('wdpro-ajax-'.$wdproAction, $_POST);
			
			define('WDPRO_AJAX', $wdproAction);
		}
	};

	add_action('wp_ajax_nopriv_wdpro', $ajaxCallback);
	add_action('wp_ajax_wdpro', $ajaxCallback);
}



// Modules
require(__DIR__ . '/Modules.php');

// Добавление модулей
Wdpro\Modules::addWdpro('form');
Wdpro\Modules::addWdpro('tools');
Wdpro\Modules::addWdpro('page');
Wdpro\Modules::addWdpro('breadcrumbs');
Wdpro\Modules::addWdpro('adminNotice');
Wdpro\Modules::addWdpro('sender');
Wdpro\Modules::addWdpro('counters');
Wdpro\Modules::addWdpro('extra/downloadFile');
Wdpro\Modules::addWdpro('extra/consoleWidget');
Wdpro\Modules::addWdpro('extra/scriptsToFooter');

// Админка
if (is_admin())
{
	require('console.php');
}


// Сайт
else
{
	require('site.php');
}

// Это можно удалить
/*add_action('init', function () {

	//do_action('wdpro-ready');
	//do_action('app-ready');
}, 90);*/


// Выключение объединения скриптов
if (get_option('wdpro_uncatenate_scripts') == 1) {
	define('CONCATENATE_SCRIPTS', false);
}


/**
 * Возвращает хлебные крошки после инициализации
 * 
 * Если ее убрать в site.php, то при вызове этой функции из темы functions.php будет 
 * ошибка
 *
 * @param callback $callback Каллбэк, получающий объект хлебных крошек
 */
function breadcrumbsInit($callback) {

	add_action('wdpro_breadcrumbs_init', $callback);
}


// Cron
if (defined('DOING_CRON') && DOING_CRON) {

	\Wdpro\Modules::run('cron');
}

