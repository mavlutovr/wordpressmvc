<?php
namespace Wdpro\Lang;

class Controller extends \Wdpro\BaseController {

	protected static $currentLang;


	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite () {


		// Current language define
		$uri = $_SERVER['REQUEST_URI'];

		$_SERVER['REQUEST_URI_ORIGINAL'] = $uri;
		$setted = false;

		// Each lang uri
		foreach(Data::getUris() as $langUri) {

			// It is uri of lang
			if (static::isUriOfLang($uri, $langUri)) {

				$langSuffix = $langUri.wdpro_url_slash_at_end();
				if (!$langSuffix) $langSuffix = '/';

				// Uri without lang uri
				// Front page
				$uri = preg_replace(
					'~^'.preg_quote(wdpro_home_uri()).'/'.$langSuffix.'$~',
					wdpro_home_uri().wdpro_url_slash_at_end(),
					$uri
				);

				// Inner page
				if ($uri === '') $uri = '/';
				$uri = str_replace(
					'/'.$langUri.'/',
					'/',
					$uri
				);

				if ($uri === wdpro_home_uri())
				$uri.= '/';

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
	 * Check, is specified uri related to specified lang
	 *
	 * @param string $uri
	 * @param string $lang ('', 'en', 'ru'...)
	 * @return bool
	 */
	public static function isUriOfLang($uri, $lang) {

		// Remove root folder
		$regRoot = '~^'.preg_quote(wdpro_home_uri()).'~';
		$uri = preg_replace($regRoot, '', $uri);

		$langSuffix = '/';
		if ($lang) {
			$langSuffix .= $lang.wdpro_url_slash_at_end();
		}

		// Front page
		if ($uri === $langSuffix)
			return true;

		// Another page
		if (preg_match('~^/'.$lang.'/~', $uri))
			return true;

		return false;
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
					echo '<link rel="alternate" hreflang="'.$lang['code'].'" href="'.$lang['url'].'"/>';
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