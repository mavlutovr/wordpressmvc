<?php
/*
 * Plugin Name: Wordpress MVC - Основа
 * Description: MVC разработка на Wordpress
 * Version: 0.1.3
 * Author: Roman Mavlutov
 * Author URI: https://github.com/mavlutovr/wordpressmvc
 */

 if (isset($_SERVER['HTTP_X_ORIGINAL_REQUEST'])) {
	$uri = $_SERVER['HTTP_X_ORIGINAL_REQUEST'];
	$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_REQUEST'];
}

if (!isset($_SERVER['REQUEST_URI_ORIGINAL']))
	$_SERVER['REQUEST_URI_ORIGINAL'] = $_SERVER['REQUEST_URI'];



// Фикс для хостинга sweb.ru
if (isset($_SERVER['HTTP_HTTPS']) && $_SERVER['HTTP_HTTPS'] === 'on')
	$_SERVER['HTTPS'] = 'on';

// Дата
if (defined('TIMEZONE') && TIMEZONE) {
	date_default_timezone_set(TIMEZONE);
}
else {
	date_default_timezone_set(ini_get('date.timezone'));
}


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

// При смене домена
// Выключаем модуль разработчика и Soy компиляцию, потому что они не нужны на боевом сервере
$lastDomainMd5 = get_option('wdpro_current_domain_md5');
$currentDomainMd5 = md5($_SERVER['HTTP_HOST']);
$currentDomainMd5 = str_replace('www.', '', $currentDomainMd5);
if ($currentDomainMd5 !== $lastDomainMd5) {
	update_option('wdpro_current_domain_md5', $currentDomainMd5);
	update_option('wdpro_compile_soy', 0);
	update_option('wdpro_dev_mode', 0);
}

// JavaScripts
add_action('wp_enqueue_scripts', function ()
{
	wp_enqueue_script("jquery");
	wp_register_script('wdpro', WDPRO_URL.'js/core.all.js');
	wp_enqueue_script('wdpro');
});
wdpro_add_script_to_console(__DIR__.'/js/core.all.js', 'wdpro.core.all');
wdpro_add_script_to_console(__DIR__.'/js/ready.all.js');
wdpro_add_script_to_site(__DIR__.'/js/ready.all.js');
wdpro_add_script_to_console(__DIR__.'/js/moment.js');

// Closure Compiler (Soy)
require(__DIR__ . '/inc/soy.php');

// Less Compiler
require(__DIR__ . '/inc/less.php');

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

// Путь к языкам
Wdpro\Autoload::add('Wdpro\Lang', __DIR__.'/modules/lang');

// Modules
require(__DIR__ . '/Modules.php');

// Add modules
Wdpro\Modules::addWdpro('adminNotice');
Wdpro\Modules::addWdpro('secure');
Wdpro\Modules::addWdpro('form');
Wdpro\Modules::addWdpro('tools');
Wdpro\Modules::addWdpro('page');
Wdpro\Modules::addWdpro('breadcrumbs');
Wdpro\Modules::addWdpro('sender');
Wdpro\Modules::addWdpro('counters');
Wdpro\Modules::addWdpro('extra/downloadFile');
Wdpro\Modules::addWdpro('extra/consoleWidget');
Wdpro\Modules::addWdpro('extra/seo/scripts');

// Additional fields for pages (title, keywords, description, h1, перелинковка)
require(__DIR__ . '/inc/additionalFields.php');


// When it is Console
if (is_admin())
{
	require('console.php');
}


// When it is Site
else
{
	require('site.php');
}



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




function _wdpro_print_js_data () {
	global $wdproJsData;

	$data = '';

	foreach ($wdproJsData as $key => $value) {
		if ($value === '') {
			$value = 'null';
		}
		$data .= PHP_EOL
			. 'wdpro.'.$key.' = '.json_encode($value, JSON_UNESCAPED_UNICODE).';';
	}

	$wdproJsData = [];

	echo '<script>
		if (window.wdpro) {
			wdpro.WDPRO_TEMPLATE_URL = "'.WDPRO_TEMPLATE_URL.'";
			wdpro.WDPRO_UPLOAD_IMAGES_URL = "'.WDPRO_UPLOAD_IMAGES_URL.'";
			wdpro.WDPRO_HOME_URL = "'.home_url().'/";
			'.$data.'
		}
		</script>';
}

/**
 * Скрипты
 */
add_action('wp_footer', '_wdpro_print_js_data');
add_action('admin_footer', '_wdpro_print_js_data');


// Отключаем смайлики в админке, чтобы они не портились в редакторе (не превращались в теги <img> там где не нужно
if (wdpro_is_admin()) {
	add_action( "init", "wdpro_disable_emojis" );
}


/**
 * Возвращает объект хлебных крошек
 *
 * Это не нужно убирать в site.php, потому что используется в ajax запросах
 *
 * @return \Wdpro\Breadcrumbs\Breadcrumbs
 */
function wdpro_breadcrumbs()
{
	global $breadcrumbs;
	if (!$breadcrumbs) {
		$breadcrumbs = new \Wdpro\Breadcrumbs\Breadcrumbs();
	}
	return $breadcrumbs;
}


if (defined('WDPRO_CUSTOM_HOME_PAGE')) {
	add_filter('wdpro_html', function ($html) {

		$html = replace_home_page_to_custom($html);
		// $html = preg_replace(
		// 	'~'.preg_quote('http://localhost/').'~',
		// 	WDPRO_CUSTOM_HOME_PAGE,
		// 	$html
		// );
		
		return $html;
	});
}