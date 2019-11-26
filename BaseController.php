<?php
namespace Wdpro;


abstract class BaseController {

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
	 * https://fontawesome.com/v4.7.0/icons/
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


	/**
	 * Возвращает сущность по данным или id
	 *
	 * @param null|int|string|array $dataOrId ID или данные объекта
	 *
	 * @return \Wdpro\BaseEntity
	 * @throws \Exception
	 */
	public static function entity($dataOrId) {
		return wdpro_object(static::getModuleClass('Entity'), $dataOrId);
	}


	/**
	 * Возвращает класс сущности
	 *
	 * @return string|\Wdpro\BaseEntity|\Wdpro\BasePage
	 */
	public static function entityClass() {
		return static::getModuleClass('Entity');
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
	 * Запуск выполнения скриптов на сайте после инициализации всех модулей
	 */
	public static function runSiteStart() {

		wdpro_get_current_page(function ($page) {
			/** @var \Wdpro\BasePage $page */

			$entityClass = static::getModuleClass('Entity');

			// Это текущая страница
			if ($page && $entityClass && '\\'.get_class($page) === $entityClass) {

				// Если есть метод, который возвращает файл шаблона
				if (method_exists($entityClass, 'getTemplateFile') && $templateFile = $entityClass::getTemplateFile()) {

					// Устанавливаем файл шаблона
					\Wdpro\Templates::setCurrentTemplate($templateFile);
				}
			}
		});

		static::runSite();

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

		$params = $paramsOrWhere;

		if (is_string($params)) {
			$params = [
				'where'=>$params,
			];
		}

		$sqlTable = static::sqlTable();
		$tree = $sqlTable::isColl('parent_id');

		if ($tree) {

			// Начиная с одного родительского элемента
			if (isset($params['from_id']) && $params['from_id']) {
				$params = wdpro_extend([
					'where'=>[
						'WHERE id=%d',
						[
							$params['from_id']
						]
					],
				], $params);
			}

			// Начиная со списка
			else {
				$params = wdpro_extend([
					'where'=>[
						'WHERE parent_id=%d ORDER BY menu_order',
						[
							isset($params['parent_id']) ? $params['parent_id'] : 0,
						]
					],
				], $params);
			}
		}

		else {
			$params = wdpro_extend([
				'where'=>'ORDER BY menu_order',
			], $params);
		}


		if (!isset($params['checks'])) {
			$params['checks'] = false;
		}

		if (!isset($params['options'])) {
			$params['options'] = [];
			if (!$params['checks']) {
				$params['options'][''] = '';
			}
		}

		if (isset($params['field']) && $params['field']) {
			$nameField = $params['field'];
		}

		else {
			$nameField = $sqlTable::isColl('post_title') ? 'post_title' : 'name';
		}

		if ($sel = $sqlTable::select($params['where'], 'id, '.$nameField)) {

			$prefix = isset($params['prefix']) ? $params['prefix'] : '';

			foreach($sel as $row) {

				$name = $row[$nameField];


				// Для Checks
				if ($params['checks']) {
					$option = [
						'value'=>$row['id'],
						'text'=>$name,
					];
				}

				// Для Select
				else {
					$name = $prefix . $name;
					$params['options'][] = [ $row['id'], $name ];
				}

				if ($tree) {

					if ($subOptions = static::getOptions([
						'options'=>[],
						'checks'=>$params['checks'],
						'parent_id'=>$row['id'],
						'prefix'=>'- '.$prefix,
					])) {

						// Checks
						if ($params['checks']) {
							$option['options'] = $subOptions;
						}

						// Select
						else {
							foreach ($subOptions as $subOption) {
								$params['options'][] = $subOption;
							}
						}
					}
				}

				// Checks
				if ($params['checks']) {
					$params['options'][] = $option;
				}
			}

			return $params['options'];
		}
	}


	/**
	 * Возвращает данные для колонки сортировки (Простые элементы)
	 *
	 * @param array $data Данные
	 *
	 * @return string
	 */
	public static function getOrderColumnRowElement ($data) {

		$sortingField = static::getSortingSqlField();

		return '<div class="js-wdpro-sorting-number wdpro-sorting-number"
data-id="' . $data['id'] . '">'
			. $data[$sortingField] . '</div>';
	}


	/**
	 * Возвращает sql поле сортировки
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function getSortingSqlField() {
		$table = static::getSqlTableClass();

		if ($table::isField('menu_order')) {
			return 'menu_order';
		}

		if ($table::isField('sorting')) {
			return 'sorting';
		}

		throw new \Exception('В таблице '.$table::getName().' нет поля `menu_order`, которое необходимо для сортировки.');
	}

}
