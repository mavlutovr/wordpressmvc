<?php
namespace Wdpro\Lang;

class Controller extends \Wdpro\BaseController {

	protected static $currentLang;


	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite () {


		$uri = $_SERVER['REQUEST_URI'];
		$_SERVER['REQUEST_URI_ORIGINAL'] = $uri;
		$setted = false;

		foreach(Data::getUris() as $langUri) {
			if (strstr($uri, '/'.$langUri.'/')) {
				$uri = str_replace('/'.$langUri.'/', '/', $uri);
				$_SERVER['REQUEST_URI'] = $uri;
				//$_SERVER['REDIRECT_URL'] = $uri;
				//print_r($_SERVER); exit();
				static::setCurrentLang($langUri);
				$setted = true;
			}
		}

		if (!$setted) {
			static::setCurrentLang('');
		}
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite()
	{
		// Тег, который говорит поисковикам, что у этой страницы есть копия на другом языке
		add_action('wp_head', function () {

			$data = Data::getDataForMenu();
			foreach ($data as $lang) {
				if (!$lang['active'] && $lang['code']) {
					echo '<link rel="alternate" hreflang="'.$lang['code'].'" href="'.$lang['url'].'" />';
				}
			}
		});

		// Filter template files include
		add_filter('template_include', function ($template) {

			$langTemplate = preg_replace(
				'~(\.php)$~',
				'.'.Data::getCurrentLangUriNotEmpty().'$1',
				$template
			);

			if (is_file($langTemplate))
				return $langTemplate;

			return $template;
		});
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole () {
		\Wdpro\Console\Menu::add([
			'position'=>'settings',
			'roll'=>ConsoleRoll::class,
			'n'=>1000,
		]);
	}


	/**
	 * Установка текущего языка
	 *
	 * @param string $lang ru, en, de...
	 */
	public static function setCurrentLang($lang) {
		static::$currentLang = $lang;
	}


	/**
	 * Возвращает текущий язык
	 *
	 * @return string
	 */
	public static function getCurrentLangUri() {
		return static::$currentLang;
	}


	/**
	 * Обновление данных о языка
	 */
	public static function updateData() {
		if ($sel = SqlTable::select('ORDER BY sorting')) {
			Data::setData($sel);
		}
	}

}


return __NAMESPACE__;