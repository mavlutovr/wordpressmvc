<?php
namespace Wdpro;


class BaseController {

	use Tools;

	protected $dir;
	protected static $lang = false;



	/**
	 * Инициализация модуля
	 */
	public static function init() {

		// Таблица
		/** @var \Wdpro\BaseSqlTable $tableClass */
		if ($tableClass = static::getModuleClass('SqlTable')) {
			$tableClass::init();
		}

	}


	/**
	 * Запускает init()
	 */
	public static function initStart() {



		static::init();
	}


	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки:
	 * https://developer.wordpress.org/resource/dashicons/#forms
	 * http://fontawesome.io/icon/file-o/
	 */
	public static function initConsole() {

	}


	/**
	 * Запускает инициализацию консольной части модуля
	 */
	public static function initConsoleStart() {

		// Список
		if ($rollClass = static::getModuleClass('ConsoleRoll')) {
			wdpro_init_roll($rollClass);
		}

		static::initConsole();
	}


	/**
	 * Возвращает класс основной таблицы модуля
	 *
	 * @return \Wdpro\BaseSqlTable
	 */
	public static function sqlTable() {

		return static::getModuleClass('SqlTable');
	}


	public static function initSiteStart() {

		// Список (Это здесь, чтобы инициировались типы страниц)
		if ($rollClass = static::getModuleClass('ConsoleRoll')) {
			wdpro_init_roll($rollClass);
		}

		static::initSite();
	}


	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite() {
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (общая часть)
	 */
	public static function run() {

	}


	/**
	 * Выполняется при кроне раз в минуту
	 */
	public static function cron() {

	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole() {

	}


	/**
	 * Запуск выполнения скриптов после инициализации всех модулей
	 */
	public static function runConsoleStart() {
		static::runConsole();
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {

	}


	/**
	 * Возвращает полное имя класса (вместе с пространством имен)
	 * если есть файл класса
	 *
	 * @param string $className Имя класса
	 * @return string
	 */
	public static function getModuleClass($className) {

		if (static::isClass($className)) {
			return static::getNamespace() . '\\' . $className;
		}
	}


	/**
	 * Возвращает папку модуля (та папка, в которой находиться контроллер)
	 *
	 * @return string
	 */
	public static function dir() {

		return static::getStatic('dir');
		//return $this->dir;
	}


	/**
	 * Проверяет, есть ли в модуле указанный класс
	 *
	 * @param string $className Имя класса (без пространства имен)
	 * @return bool
	 */
	public static function isClass($className) {

		return is_file(static::dir() . '/' . $className . '.php');
	}


	/**
	 * Устанавливает папку модуля (ту папку, в которой находиться контроллер)
	 *
	 * @param string $dir
	 */
	public static function setDir($dir) {

		static::setStatic('dir', $dir);
		//$this->dir = $dir;
	}


	/**
	 * Возвращает true, если в этом модуле включены языковые версии
	 *
	 * @return bool
	 */
	public static function isLang() {
		return static::$lang;
	}


	/**
	 * Включает / выключает языковость в модуле
	 *
	 * @param bool $enable true - включить
	 */
	public static function setLang($enable) {
		static::$lang = $enable;
	}


	/**
	 * Возвращает options для select
	 *
	 * @param null|array|string $paramsOrWhere Параметры
	 *
	 * @return array
	 */
	public static function getOptions($paramsOrWhere=null) {

		if (is_string($paramsOrWhere)) {
			$paramsOrWhere = [
				'where'=>'ORDER BY menu_order DESC',
			];
		}

		$paramsOrWhere = wdpro_extend([
			'where'=>'ORDER BY menu_order DESC',
		], $paramsOrWhere);

		if (!isset($paramsOrWhere['options'])) {
			$paramsOrWhere['options'] = [ '' =>''];
		}

		$sqlTable = static::sqlTable();
		if ($sel = $sqlTable::select($paramsOrWhere['where'], 'id, post_title')) {
			foreach($sel as $row) {
				$paramsOrWhere['options'][] = [$row['id'], $row['post_title']];
			}

			return $paramsOrWhere['options'];
		}
	}

}
