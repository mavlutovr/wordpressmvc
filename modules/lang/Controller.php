<?php
namespace Wdpro\Lang;

class Controller extends \Wdpro\BaseController {

	protected static $currentLang;


	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite () {


		$uri = $_SERVER['REQUEST_URI'];

		foreach(Data::getUris() as $langUri) {
			if (strstr($uri, '/'.$langUri.'/')) {
				$uri = str_replace('/'.$langUri.'/', '/', $uri);
				$_SERVER['REQUEST_URI'] = $uri;
				static::setCurrentLang($langUri);
			}
		}
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