<?php
namespace Wdpro\Lang;

class Controller extends \Wdpro\BaseController {

	protected static $currentLang;


	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite () {


		// Current language define
		if (!isset($_SERVER['REQUEST_URI_ORIGINAL']))
			$_SERVER['REQUEST_URI_ORIGINAL'] = $_SERVER['REQUEST_URI'];

		$uri = $_SERVER['REQUEST_URI'];
		$uri = str_replace('?'.$_SERVER['QUERY_STRING'], '', $uri);

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



		// Add language pages to sitemap.xml (Plugin "Google XML Sitemaps")
		$sitemapGeneratorLastPostId = 0;
		add_action('sm_addurl', function ($page) use (&$sitemapGeneratorLastPostId) {
			/** @var \GoogleSitemapGeneratorPage $page */

			$postId = $page->GetPostID();
			if ($postId) {

				if ($sitemapGeneratorLastPostId === $postId) return false;
				$sitemapGeneratorLastPostId = $postId;

				$post = wdpro_get_post_by_id($postId);
				$langs = Data::getDataForMenu($post);

				foreach ($langs as $lang) {
					if ($lang['uri'] && $lang['isLang'] && $lang['pageUrl']) {
						$generator = \GoogleSitemapGenerator::GetInstance();
						$generator->AddUrl(
							$lang['pageUrl'],
							$page->GetLastMod(),
							$page->GetChangeFreq(),
							$page->GetPriority(),
							$postId
						);
					}
				}
			}
		});
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
				if (!$lang['active'] && isset($lang['code']) && $lang['code']) {
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
	 * Return current lang, when it's core languane, it's return it code
	 *
	 * @return string
	 */
	public static function getCurrentLangUriNotEmpty() {
		return Data::getCurrentLangUriNotEmpty();
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
	 * Return piece of file name of current language
	 *
	 * @return string
	 */
	public static function getCurrentLangFileSuffix() {
		$currentLangUrl = static::getCurrentLangUri();

		if ($currentLangUrl) {
			return $currentLangUrl.'.';
		}

		return '';
	}


	/**
	 * Return filename with current lang suffix (posts.php -> posts.en.php)
	 *
	 * @param  string $originalFilename
	 * @return string
	 */
	public static function getFilenameForCurrentLang($originalFilename) {

		$suffix = static::getCurrentLangFileSuffix();

		if (!$suffix) return $originalFilename;

		$arr = explode('.', $originalFilename);

		$last = count($arr) - 1;
		$arr[$last] = $suffix.$arr[$last];

		$lanfFilename = implode('.', $arr);
		if (is_file($lanfFilename)) {
			return $lanfFilename;
		}

		return $originalFilename;
	}


	/**
	 * Обновление данных о языка
	 */
	public static function updateData() {
		if ($sel = SqlTable::select('ORDER BY sorting')) {
			Data::setData($sel);
		}
	}


	/**
	 * Return text of current lang from texts array
	 *
	 * @param  array $texts array('ru'=>'ВордПресс', 'en'=>'WordPress')
	 * @return string
	 */
	public static function getText($texts) {
		$currentLang = static::getCurrentLangUriNotEmpty();

		if (isset($texts[$currentLang])) {
			return $texts[$currentLang];
		}

		foreach ($texts as $text) {
			return $text;
		}
	}

}


return __NAMESPACE__;
