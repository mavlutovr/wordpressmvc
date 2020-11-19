<?php

// Редирект с index.php на корень сайта /
if (wdpro_current_uri() === '/index.php') {
	wdpro_location(home_url() . '/');
	exit();
}


// Шорткоды
require __DIR__ . '/inc/shortcodes.php';

do_action('wdpro-ready');
Wdpro\Modules::run('initSiteStart');
Wdpro\Modules::run('run');
add_action('wp', function () {
	
	wdpro_data('wp_inited', true);
	Wdpro\Modules::run('runSiteStart');
	do_action('app-ready');
});


/**
 * Удаление лишнего адреса у страниц
 *
 * Это чтобы адреса в шапке сайта не содержали типа страниц.
 * Это было на сайте book.tridodo.ru, вот так было не правильно
 * <link rel='prev' title='Что сделает отдых выгодным?' href='http://book.tridodo.ru/app_video/kakaya-veshhj-sdelaet-otdih-vigodnim/' />
 *
 * Надо вот так
 * <link rel='prev' title='Что сделает отдых выгодным?' href='http://book.tridodo.ru/kakaya-veshhj-sdelaet-otdih-vigodnim/' />
 *
 *
 * @param string $post_link Текущий адрес страницы
 * @param WP_Post $post Страница
 * @param $leavename
 * @return string
 */
function na_remove_slug($post_link, $post, $leavename)
{

	if (!wdpro_is_post_type($post->post_type) && 'publish' != $post->post_status) {
		return $post_link;
	}

	$post_link = str_replace('/' . $post->post_type . '/', '/', $post_link);

	return $post_link;
}

add_filter('post_type_link', 'na_remove_slug', 10, 3);


// Less
if (is_file(WDPRO_TEMPLATE_PATH . 'style.less')) {
	wdpro_less_compile_try(
		WDPRO_TEMPLATE_PATH . 'style.less',
		WDPRO_TEMPLATE_PATH . 'style.less.css'
	);
	wdpro_add_css_to_site(WDPRO_TEMPLATE_PATH . 'style.less.css');

	// Удаляем из темы стандартный style.css
	add_action('init', function () {
		wp_dequeue_style('style.css');
		wp_deregister_style('style.css');
	});
}


// Soy
function themeSoyCompileAndAddToPage()
{

	$soyDir = WDPRO_TEMPLATE_PATH . 'soy/';
	$jsDir = WDPRO_TEMPLATE_PATH . 'soy_compiled/';

	if (is_dir($soyDir) && $files = scandir($soyDir)) {
		foreach ($files as $file) {
			if (strstr($file, '.soy')) {
				wdpro_closure_compile_try(
					$soyDir . $file,
					$jsDir . $file . '.js'
				);
			}
		}
	}

	if (is_dir($jsDir) && $files = scandir($jsDir)) {
		foreach ($files as $file) {
			if (strstr($file, '.soy.js')) {
				wdpro_add_script_to_site($jsDir . $file);
			}
		}
	}
}

themeSoyCompileAndAddToPage();


// Javascript
wdpro_add_script_to_site(__DIR__ . '/js/ready.all.js');
wdpro_add_script_to_site(__DIR__ . '/js/ready.site.js');

// Webpack
// Скомпилированный
if (is_file(WDPRO_TEMPLATE_PATH . 'dist/main.js')) {
	wdpro_add_script_to_site(WDPRO_TEMPLATE_PATH . 'dist/main.js', null, true, 100000);
} // Нескомпилированный
else if (is_file(WDPRO_TEMPLATE_PATH . 'index.js')) {
	wdpro_add_script_to_site(WDPRO_TEMPLATE_PATH . 'index.js', null, true, 100000);
}

if (is_file(WDPRO_TEMPLATE_PATH . 'script.js')) {

	wdpro_add_script_to_site(WDPRO_TEMPLATE_PATH . 'script.js', null, true, 100000);
}
if (is_file(WDPRO_TEMPLATE_PATH . '/js/script.js')) {

	wdpro_add_script_to_site(WDPRO_TEMPLATE_PATH . '/js/script.js', null, true, 100000);
}

// app/site.js
// По-умолчанию
//wdpro_default_file(__DIR__.'/js/default/app.site.js', __DIR__.'/../app/site.js');
// Подключение
if (is_file(__DIR__ . '/../app/site.js')) {
	wdpro_add_script_to_site(__DIR__ . '/../app/site.js');
}


/**
 * Инициализация страницы сайта
 */
function appInitPage()
{
	/*global $breadcrumbs, $post;
	$breadcrumbs = new \Wdpro\Breadcrumbs\Breadcrumbs();
	$breadcrumbs->makeFrom(wdpro_object_by_post_id($post->ID));*/
}


/**
 * Возвращает объект хлебных крошек
 *
 * @return \Wdpro\Breadcrumbs\Breadcrumbs
 */
function breadcrumbs()
{
	global $breadcrumbs;
	return $breadcrumbs;
}


// Хлебные крошки
add_action('wdpro_breadcrumbs_init', '_wdpro_breadcrumbs_init', 10);

/**
 * Дополнительная иницциализация хлебных крошек, которую если что можно отключить
 *
 * Чтобы отключить, используйте
 * remove_action('wdpro_breadcrumbs_init', 'wdpro_breadcrumbs_init');
 *
 * @param \Wdpro\Breadcrumbs\Breadcrumbs $breadcrumbs Объект хлебных крошек
 */
function _wdpro_breadcrumbs_init($breadcrumbs)
{

	if (!is_front_page()) {
		$breadcrumbs->prependFrontPage();
	}

	$breadcrumbs->removeLast(true);
	$breadcrumbs->unremoveLastLink();
}

// Получаем пост и инициализируем хлебные крошки
wdpro_get_post(function ($post) {

	if ($post) {
		global $breadcrumbs;
		$breadcrumbs = new \Wdpro\Breadcrumbs\Breadcrumbs();
		$post = wdpro_object_by_post_id($post->ID);
		$breadcrumbs->makeFrom($post);

		// Дополнительная обработка хлебных крошек
		do_action('wdpro_breadcrumbs_init', $breadcrumbs);

		if (method_exists($post, 'initSite'))
			$post->initSite();
	}
});


/**
 * Дополнительная обработка хлебных крошек
 *
 * @param callback $callback Каллбэк, в который отправляется объект хлебных крошек
 */
function wdpro_breadcrumbs_init($callback)
{
	add_action('wdpro_breadcrumbs_init', $callback, 20);
}


/**
 * Отображает хлебные крошки
 *
 * @return string
 */
function wdpro_the_breadcrumbs()
{
	return wdpro_breadcrumbs()->getHtml();
}


/**
 * Выводит настройку в браузер
 *
 * @param string $optionName Имя настройки
 */
function wdpro_opt($optionName)
{
	echo(get_option($optionName));
}


/**
 * Возвращает объект текущей страницы
 *
 * @return \Wdpro\BasePage
 */
function wdpro_page()
{

	global $wdproPage;
	if (!isset($wdproPage)) {

		$post = get_post();
		if (!$post || !$post->ID || !($wdproPage = wdpro_object_by_post_id($post->ID)))
			$wdproPage = new \Wdpro\Page\BlankPage();
	}

	return $wdproPage;
}

/**
 * Возвращает объект текущей страницы
 *
 * @return \Wdpro\BasePage
 */
function wdpro_current_page()
{

	return wdpro_page();
}


/**
 * Возвращает объект текущей открытой страницы
 *
 * @param callback $callback Каллбэк, принимающий страницу
 */
function wdpro_get_current_page($callback)
{

	wdpro_get_post(function ($post) use (&$callback) {

		if ($post && $post->ID) {
			$callback(wdpro_object_by_post_id($post->ID));
		}
	});
}


// Отправка данных страницы в шаблон
wdpro_get_current_page(function ($page) {
	global $data;

	if (!is_array($data)) {
		$data = [];
	}

	if (method_exists($page, 'getDataWithPost')) {
		$data = array_merge($data, $page->getDataWithPost());
	}
});


/**
 * Обработка открытия страницы определенного типа
 *
 * @param string $type Тип страницы
 * @param callback $callback Каллбек
 */
function wdpro_on_page_type($type, $callback)
{
	wdpro_get_current_page(function ($page) use (&$type, &$callback) {
		/** @var \Wdpro\BasePage $page */

		if ($page && $page::getType() == $type) {
			$callback($page);
		}
	});
}


/**
 * Запускает каллбэк при появлении контента и отправляет в каллбэк объект страницы
 * \Wdpro\BasePage
 *
 * @param string $pageType Тип страницы \Wdpro\BasePage::getType()
 * @param callback $callback Каллбэк
 * @param int $priority Приоритет
 */
function wdpro_on_content_type($pageType, $callback, $priority = 10)
{
	wdpro_on_content_of_page_type($pageType, $callback, $priority);
}


/**
 * Возвращает текст старницы
 *
 * @param bool $echo Сразу отправить в браузер
 * @return string
 */
function wdpro_the_content($echo = true)
{

	if (!$echo) {
		ob_start();
	}

	if (have_posts()) {
		while (have_posts()) {
			the_post();
			the_content();
		}
	} else if ($GLOBALS['post']) {
		echo $GLOBALS['post']->post_content;
	}

	if (!$echo) {
		$content = ob_get_contents();
		ob_clean();
		ob_end_flush();
		return $content;
	}
}


// Ajax
add_action('wp_enqueue_scripts', function () {

	$post = get_post();

	wp_localize_script('wdpro', 'wdproData', array(
		'ajaxUrl' => wdpro_ajax_url(),
		'homeUrl' => home_url() . '/',
		'imagesUrl' => WDPRO_UPLOAD_IMAGES_URL,
		'lang' => \Wdpro\Lang\Data::getCurrentLangUri(),
		'langNotEmpty'=>\Wdpro\Lang\Data::getCurrentLangUriNotEmpty(),
		'currentPostId' => !empty($post->ID) ? $post->ID : '',
		'currentPostName' => !empty($post->post_name) ? $post->post_name : '',
	));
});


if (get_option('wdpro_remove_redirect_canonical') == 1) {
	remove_filter('template_redirect', 'redirect_canonical');
}


// Правка адресов страниц
// canonical
add_filter('get_page_uri', function ($url, $post) {

	return $post->post_name;
}, 10, 2);
// Убираем слэш с конца адреса в cannonical, prev, next
add_filter('post_type_link', function ($post_link) {

	$post_link = preg_replace('~/$~', '', $post_link);
	return $post_link;
});

/*wdpro_content(function ($content) {

	$content .= '<p>gggggggggggggggg</p>';

	return $content;
}, -10);*/


// Отключение админ-панели
/*add_action('wp', function () {
	show_admin_bar(false);
});*/

// Чтобы кнопка Редактировать была у всех постов
function my_admin_bar_link()
{
	global $wp_admin_bar;
	global $post;
	if (!is_super_admin() || !is_admin_bar_showing())
		return;

	if (is_single() || is_page()) {
		$wp_admin_bar->add_menu(array(
			'id' => 'edit',
			'parent' => false,
			'title' => __('Edit Page'),
			'href' => get_edit_post_link($post->id),
		));
	}
}

add_action('wp_before_admin_bar_render', 'my_admin_bar_link');

// Удаление лишних мета-тегов
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
function at_remove_dup_canonical_link()
{
	return false;
}

//( 'wp_head',             'rel_canonical'                          );
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');


// Отключение версий скриптов
add_filter('script_loader_src', 'remove_src_version');
add_filter('style_loader_src', 'remove_src_version');
function remove_src_version($src)
{

	global $wp_version;

	$version_str = '?ver=' . $wp_version;

	// Меняем версию для безопасности
	// Версия нужна только чтобы файлы новых версий закачались заново в браузер
	$versions = explode('.', $wp_version);
	if (isset($versions[0])) $versions[0]--;
	if (isset($versions[2])) $versions[2]++;

	return str_replace($version_str, '?u=' . implode('-', $versions), $src);
}


// Ловим вывод и обрабатываем его
// Например, помещаем скрипты в <noindex>
ob_start();
add_action('wp_footer', function () {

	$html = ob_get_contents();

	$html = apply_filters('wdpro_html', $html);

	ob_clean();
	echo $html;
}, 1000);


// Подключение reCAPTCHA3
$reCaptcha3SiteKey = get_option('wdpro_recaptcha3_site');
if ($reCaptcha3SiteKey) {
	wdpro_add_script_to_site_external(
		'https://www.google.com/recaptcha/api.js?render=' . $reCaptcha3SiteKey,
		true
	);
	wdpro_js_data('reCaptcha3SiteKey', $reCaptcha3SiteKey);

	wdpro_add_script_to_site(__DIR__ . '/modules/form/recaptcha3.site.js', null, true);
}


// Правка <link rel=canonical
// Чтобы не было дублей страниц
add_filter('get_canonical_url', function ($canonical_url, $post) {

	if (get_query_var('cpage', 0) && $post->ID === get_queried_object_id()) {
		$canonical_url = get_permalink($post);
	}
	// Добавляем в конец слеш, когда адреса со слешем в конце
	if (!preg_match('~/$~', $canonical_url))
		$canonical_url .= wdpro_url_slash_at_end();

	// Еще раз убираем двойной слеш
	$canonical_url = preg_replace('~//$~', '/', $canonical_url);

	if (wdpro_get_option('wdpro_add_query_string_to_canonical') && !empty($_SERVER['QUERY_STRING'])) {
		$canonical_url .= '?'.$_SERVER['QUERY_STRING'];
	}

	return $canonical_url;
}, 10, 2);


// Remove link rel=’prev’ and link rel=’next’
if (get_option('wdpro_remove_link_rel_prev_and_next')) {
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
}
