<?php
namespace Wdpro\Page\PostNamePrefix;

class Controller extends \Wdpro\BaseController {


	protected static $prefixes;
	protected static $originalUri;


	/**
	 * Collect all post_names prefixes
	 */
	public static function collectAllPostsNamesPrefixes() {

		if (static::$prefixes) return;
		static::$prefixes = [];

		if ($sel = SqlTable::select('GROUP BY prefix')) {
			foreach ($sel as $item) {
				if ($item['prefix'])
					static::$prefixes[$item['prefix']] = $item['prefix'];
			}
		}
	}


	/**
	 * Инициализация модуля
	 */
	public static function initSite()
	{
		parent::initSite();

		add_filter( 'post_link', array(Controller::class, 'changePermalinks'), 10, 3);
		add_filter( 'page_link', array(Controller::class, 'changePermalinks'), 10, 3);
		add_filter( 'post_type_link', array(Controller::class, 'changePermalinks'), 10, 3);
		add_filter( 'category_link', array(Controller::class, 'changePermalinks'), 11, 3);
		add_filter( 'tag_link', array(Controller::class, 'changePermalinks'), 10, 3);
		add_filter( 'author_link', array(Controller::class, 'changePermalinks'), 11, 3);
		add_filter( 'day_link', array(Controller::class, 'changePermalinks'), 11, 3);
		add_filter( 'month_link', array(Controller::class, 'changePermalinks'), 11, 3);
		add_filter( 'year_link', array(Controller::class, 'changePermalinks'), 11, 3);
		add_filter( 'pre_post_link', array(Controller::class, 'changePermalinks'), 11, 3);

		static::collectAllPostsNamesPrefixes();


		// Redirect to url with prefix
		wdpro_on_page_init(function ($page) {
			/** @var $page \App\BasePage */
			static::redirectToPrefixUriIfNeed($page);
		}, 9);


		// Open pages by prefixed uris
		if (empty(static::$originalUri)) {
			static::setOriginalUri(
				$_SERVER['REQUEST_URI_ORIGINAL'] ? $_SERVER['REQUEST_URI_ORIGINAL'] : $_SERVER['REQUEST_URI']
			);
		}

		$homeUri = wdpro_home_uri(true);

		// Each prefixes
		foreach (static::$prefixes as $prefix) {
			$reg = '~^('.preg_quote($homeUri.$prefix).')~';

			$requestUrl = $_SERVER['REQUEST_URI'];
			$queryReg = '~(\?[\S]*)$~';
			$query = '';
			$queryArr = [];
			if (\preg_match($queryReg, $requestUrl, $queryArr)) {
				$query = $queryArr[1];
				$requestUrl = \preg_replace($queryReg, '', $requestUrl);
			}

			// if uri start with prefix
			if (preg_match($reg, $requestUrl)) {
				$uri = preg_replace($reg, $homeUri, $requestUrl);

				$wdpro_home_uri = wdpro_home_uri();
				if (!\preg_match('~/$~', $wdpro_home_uri))
					$wdpro_home_uri .= '/';

				if ($uri !== $wdpro_home_uri) {

					$_SERVER['REQUEST_URI'] = $uri.$query;
				}
			}
		}
	}


	/**
	 * Change standard link to link with prefix for add to page
	 * @param string $permalink
	 * @param WP_Post $post
	 */
	public static function changePermalinks($permalink, $post) {

//		echo PHP_EOL.'LINK: '.$permalink.PHP_EOL.PHP_EOL;

		if ($post && !empty($post->ID)) {
			$page = wdpro_get_post_by_id($post->ID);
			$permalink = $page->getUrl();
		}
//		echo PHP_EOL.'LINK2: '.$permalink.PHP_EOL.PHP_EOL;

		return $permalink;
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite()
	{
		

	}


	/**
	 * Remove prefix
	 *
	 * @param \Wdpro\BasePage $page
	 */
	public static function remove($page) {
		SqlTable::delete(['post_id'=>$page->id()]);
	}


	/**
	 * Save prefix
	 *
	 * @param \Wdpro\BasePage $page
	 * @throws \Exception
	 */
	public static function set($page) {

		$postId = $page->id();
		$postName = $page->getData('post_name');
		$prefix = $page->getPostNamePrefix();

		$finalPostName = $prefix.$postName;

		if ($current = SqlTable::getRow(['WHERE post_id=%d LIMIT 1', [$postId]])) {

			if ($prefix) {
				SqlTable::update([
					'post_name'=>$postName,
					'prefix'=>$prefix,
					'final_post_name'=>$finalPostName,
				], ['id'=>$current['id']]);
			}
			else static::remove($page);
		}

		else {
			if ($prefix) {
				SqlTable::insert([
					'post_id' => $postId,
					'prefix' => $prefix,
					'final_post_name' => $finalPostName,
					'post_name' => $postName,
				]);
			}
		}
	}


	/**
	 * Redirect from non prefix url to prefix url if it is actual
	 *
	 * @param \Wdpro\BasePage $page
	 */
	public static function redirectToPrefixUriIfNeed($page) {

		if ($page) {
			$uri = $page->getUriWithPrefix();
			if (!strstr($uri, '?') && $_SERVER['QUERY_STRING']) {
				$uri .= '?'.$_SERVER['QUERY_STRING'];
			}

			// echo static::$originalUri.' !== '.$uri.PHP_EOL.PHP_EOL;
			if (static::$originalUri !== $uri)
				wdpro_location($uri);
		}
	}


	public static function setOriginalUri($uri) {
		static::$originalUri = $uri;
	}

}

return __NAMESPACE__;
