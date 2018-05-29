<?php
namespace Wdpro\Lang;

/**
 * Класс данных о языках, которые доступны еще до инициализации sql таблиц
 *
 * Это нужно, чтобы при инициализации таблиц уже были данные о языках и их можно было
 * использовать для обработки таблиц. Чтобы для каждого языка автоматом добавлялись поля.
 *
 * @package Wdpro\Lang
 */
class Data {

	protected static $jsonFileName = 'langs.json';
	protected static $data=[];
	protected static $inited = false;


	/**
	 * Инициализация
	 *
	 * @return bool
	 */
	protected static function init() {
		if (static::$inited) return false;
		static::$inited = true;

		if (is_file(__DIR__.'/'.static::$jsonFileName)) {
			$json = file_get_contents(__DIR__.'/'.static::$jsonFileName);
			static::$data = json_decode($json, 1);
		}
	}


	/**
	 * Сохранение данных о языках
	 *
	 * Здесь специально сделано так, чтобы из uris не удалялись данные. Чтобы при
	 * случайном удалении языка во время обновления таблиц, колонки удаленного языка не
	 * удалились. Чтобы колонки таблицы мог удалить только программист. Удалив язык из
	 * langs.json
	 *
	 * @param array $data Данные
	 */
	public static function setData($data) {
		static ::init();

		$uris = [];
		if (isset(static::$data['uris'])) {
			$uris = static::$data['uris'];
		}

		foreach ($data as $item) {
			$uris[$item['uri']] = $item['uri'];

			$langs[$item['uri']] = $item;
		}

		static::$data = [
			'langs'=>$langs,
			'time'=>time(),
			'uris'=>$uris,
		];

		$json = json_encode(static::$data, JSON_PRETTY_PRINT);
		file_put_contents(__DIR__.'/'.static::$jsonFileName, $json);
	}


	/**
	 * Возвращает массив адресов языков [ '', 'en', 'de' ]
	 */
	public static function getUris() {
		static::init();

		if (isset(static::$data['uris'])) {
			return static::$data['uris'];
		}

		return [''];
	}


	/**
	 * Возвращает массив суффиксов
	 *
	 * @return array
	 */
	public static function getSuffixes() {
		static::init();

		$ret = [];

		if (isset(static::$data['uris'])) {
			foreach ( static::$data['uris'] as $uri ) {
				$ret[] = static::getSuffix($uri);
			}
		}
		else {
			$ret[] = '';
		}

		return $ret;
	}


	/**
	 * Заменяет [lang] в строке на суффикс текущего языка
	 *
	 * Это нужно, например, при получении данных кнопок меню для текущего языка.
	 * Чтобы за запросе Where подставились нужные поля, а так же сами выбираемые поля
	 * получились теми, которые нужно.
	 *
	 * Например, запрос
	 * WHERE post_title[lang] != ''
	 * на английской версии превратиться в
	 * WHERE post_title_en != ''
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	public static function replaceLangShortcode($string) {
		$suffix = static::getSuffix(Controller::getCurrentLangUri());
		$string = str_replace('[lang]', $suffix, $string);
		return $string;
	}


	/**
	 * Возвращает адрес главной страницы текущего языка
	 *
	 * @return string
	 */
	public static function currentUrl() {
		$url = home_url().'/';
		$lang = Controller::getCurrentLangUri();

		if ($lang) {
			$url .= $lang.'/';
		}

		return $url;
	}


	/**
	 * Возвращает суффикс текущего языка
	 *
	 * Это например, нужно, чтобы выбирать из настроек с помощью wdpro_get_option
	 * настройку текущего языка.
	 *
	 * @return string
	 */
	public static function getCurrentSuffix() {
		return static::getSuffix(Controller::getCurrentLangUri());
	}


	/**
	 * Возвращает текущий язык
	 *
	 * @return string
	 */
	public static function getCurrentLangUri() {
		return Controller::getCurrentLangUri();
	}


	/**
	 * Возвращает данные языков
	 *
	 * @param string $lang Только для языка
	 *
	 * @return array
	 */
	public static function getData($lang=null) {
		static::init();

		if (isset(static::$data['langs'])) {
			if ($lang!==null) {
				if (isset(static::$data['langs'][$lang])) {
					return static::$data['langs'][$lang];
				}
			}
			else {
				return static::$data['langs'];
			}
		}
	}


	/**
	 * Возвращает данные для языкового меню на сайте
	 *
	 * @return array
	 */
	public static function getDataForMenu() {
		$data = static::getData();

		foreach($data as $i=>$datum) {
			if (Controller::getCurrentLangUri() == $datum['uri']) {
				$data[$i]['active'] = true;
			}

			$url = home_url().'/';
			if ($datum['uri']) {
				$url .= $datum['uri'].'/';
			}
			$data[$i]['url'] = $url;
		}

		return $data;
	}


	/**
	 * Возвращает время последлено обновленя языков
	 *
	 * Это нужно, например, для того, чтобы при инициализации таблиц при добавлении языка
	 * добавить новые поля
	 *
	 * @return int
	 */
	public static function getLastUpdateTime() {
		if (isset(static::$data['time'])) {
			return static::$data['time'];
		}
	}


	/**
	 * Возвращает суффикс с языком
	 *
	 * Это нужно, например, для того,
	 * чтобы делать окончания названий полей в таблице в зависимости от зяыка
	 *
	 * @param $uri
	 *
	 * @return string
	 */
	public static function getSuffix($uri) {
		if (!$uri) return '';

		return '_'.$uri;
	}


	/**
	 * Возвращает адрес флага для языка
	 *
	 * @param $lang
	 *
	 * @return string
	 */
	public static function getFlagSrc($lang) {
		if ($data = static::getData($lang)) {
			return WDPRO_UPLOAD_IMAGES_URL.$data['flag'];
		}
	}
}