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
	protected static $jsonFileNameProd = 'langs.prod.json';
	protected static $data = [];
	protected static $inited = false;
	protected static $rootLangUri = 'ru';


	/**
	 * Инициализация
	 *
	 * @return bool
	 */
	protected static function init () {
		if ( static::$inited ) {
			return false;
		}
		static::$inited = true;

		$jsonFile = static::getJsonFileName();

		if ( is_file(__DIR__ . '/' . $jsonFile) ) {
			$json         = file_get_contents(__DIR__ . '/' . $jsonFile);
			static::$data = json_decode($json, 1);

			// У первого языка делаем адреса без /ru/
			$n = 0;
			foreach ( static::$data['langs'] as $uri => $datum ) {

				// Первый язык
				if ( ! $n ) {
					unset(static::$data['langs'][ $uri ]);
					$datum['uri']              = '';
					static::$data['langs'][''] = $datum;

					// Запоминаем адрес главного языка, чтобы все-таки его знать
					// Он нужен, например, при получении перевода Dictionary::get('...')
					static::$rootLangUri = $uri;
				}

				// Другие языки
				else {
					// Это чтобы восстановить прежнюю сортировку
					unset(static::$data['langs'][ $uri ]);
					static::$data['langs'][ $uri ] = $datum;
				}

				$n ++;
			}
		}
	}


	/**
	 * Возвращает true, если языки включены
	 *
	 * @return bool
	 */
	public static function enabled() {
		return isset(static::$data['langs']);
	}


	protected static function getJsonFileName() {
		return wdpro_local() ? static::$jsonFileName : static::$jsonFileNameProd;
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
	public static function setData ($data) {
		static::init();

		if ( isset(static::$data['uris']) ) {
			$uris = static::$data['uris'];
		}
		// Адрес первого языка делаем пустым
		$uris[''] = '';

		foreach ( $data as $n=>$item ) {
			// Адрес не первого языка делаем обычный
			if ($n) {
				$uris[ $item['uri'] ] = $item['uri'];
			}

			$langs[ $item['uri'] ] = $item;
		}

		static::$data = [
			'langs' => $langs,
			'time'  => time(),
			'uris'  => $uris,
		];

		$json = json_encode(static::$data, JSON_PRETTY_PRINT);
		$path = __DIR__ . '/' . static::getJsonFileName();
		file_put_contents($path, $json);
	}


	/**
	 * Возвращает массив адресов языков [ '', 'en', 'de' ]
	 */
	public static function getUris () {
		static::init();

		if ( isset(static::$data['uris']) ) {
			return static::$data['uris'];
		}

		return [ '' ];
	}


	/**
	 * Возвращает массив суффиксов
	 *
	 * @return array
	 */
	public static function getSuffixes () {
		static::init();

		$ret = [];

		if ( isset(static::$data['uris']) ) {
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
	public static function replaceLangShortcode ($string) {
		$suffix = static::getSuffix(Controller::getCurrentLangUri());
		$string = str_replace('[lang]', $suffix, $string);

		return $string;
	}


	/**
	 * Возвращает адрес главной страницы текущего языка
	 *
	 * @return string
	 */
	public static function currentUrl () {
		$url  = home_url() . '/';
		$lang = Controller::getCurrentLangUri();

		if ( $lang ) {
			$url .= $lang . '/';
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
	public static function getCurrentSuffix () {
		return static::getSuffix(Controller::getCurrentLangUri());
	}


	/**
	 * Возвращает текущий язык
	 *
	 * @return string
	 */
	public static function getCurrentLangUri () {
		return Controller::getCurrentLangUri();
	}


	/**
	 * Возвращает данные языков
	 *
	 * @param string $lang Только для языка
	 *
	 * @return array
	 */
	public static function getData ($lang = null) {
		static::init();

		if ( isset(static::$data['langs']) ) {
			if ( $lang !== null ) {
				if ( isset(static::$data['langs'][ $lang ]) ) {
					return static::$data['langs'][ $lang ];
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
	public static function getDataForMenu () {
		$data = static::getData();

		$page = wdpro_current_page();

		$return = [];

		foreach ( $data as $datum ) {

			// Если язык включен
			if ($datum['visible'] == 1) {
				// Активный язык
				$datum['active'] = false;
				if ( Controller::getCurrentLangUri() == $datum['uri'] ) {
					$datum['active'] = true;
				}

				// Определяем, есть ли перевод страницы на этот язык
				$datum['isLang'] = $page->isLang($datum['uri']);

				// Главная
				$isHome = $page->isHome();

				// Адрес
				// Главной на текущем языке
				$homeUrl = home_url() . '/';
				if ( $datum['uri'] ) {
					$homeUrl .= $datum['uri'] . '/';
				}

				$datum['homeUrl'] = $homeUrl;

				// Адрес текущей страницы на этом языке
				$datum['pageUrl'] = $homeUrl;
				if ( ! $isHome ) {
					$datum['pageUrl'] .= $page->getUri() . '/';
				}

				// Адрес для кнопки языка в зависимости от того, есть ли перевод или нет
				$datum['url'] = $datum['isLang'] && ! $isHome ?
					$datum['pageUrl'] : $homeUrl;

				$return[] = $datum;
			}
		}

		if (count($return) > 1) {
			return $return;
		}

		return [];
	}


	/**
	 * Возвращает время последлено обновленя языков
	 *
	 * Это нужно, например, для того, чтобы при инициализации таблиц при добавлении языка
	 * добавить новые поля
	 *
	 * @return int
	 */
	public static function getLastUpdateTime () {
		if ( isset(static::$data['time']) ) {
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
	public static function getSuffix ($uri) {
		if ( ! $uri ) {
			return '';
		}

		return '_' . $uri;
	}


	/**
	 * Возвращает адрес флага для языка
	 *
	 * @param $lang
	 *
	 * @return string
	 */
	public static function getFlagSrc ($lang) {
		if ( $data = static::getData($lang) ) {
			return WDPRO_UPLOAD_IMAGES_URL . $data['flag'];
		}
	}


	/**
	 * Возвращает адрес самого главного языка
	 *
	 * @return string
	 */
	public static function getRootLangUri () {
		return static::$rootLangUri;
	}


	/**
	 * Возвращает адрес языка даже если он основной
	 *
	 * Так как адрес основного языка пустой. То есть не /en/, /de/, а /
	 * То этот метод вместо пустоты возвращает ru
	 *
	 * @return string
	 */
	public static function getCurrentLangUriNotEmpty() {
		$uri = static::getCurrentLangUri();
		if (!$uri) $uri = static::getRootLangUri();

		return $uri;
	}
}