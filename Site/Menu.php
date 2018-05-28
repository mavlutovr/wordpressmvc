<?php
namespace Wdpro\Site;

use Wdpro\Exception;

class Menu extends Roll
{

	/**
	 * Возвращает html код меню
	 * 
	 * @param array|string $params Параметры
	 *                             ---
	 *                             'template' - Шаблон. Можно передать массив из 2-х
	 *                             шаблонов. Тогда второй будет по-умолчанию. И если
	 *                             первогоне будет, то первый будет создан по второму.
	 *                             И помещен в папку с темой.
	 *                             ---
	 *                             'post_parent' - ID родительского поста.
	 *                             ---
	 *                             'entity' - Класс сущности страницы к которой относится
	 *                              кнопка.
	 *                             ---
	 *                             'sqlTable' - Таблица, из которой брать кнопки.
	 *                             ---
	 *                             'fields' - Поля, которые необходимо взять из базы для
	 *                             меню.
	 *                             ---
	 *                             'where' - Where запрос для получения кнопок.
	 *                             ---
	 *                             'type' - Тип постов
	 *                             ---
	 *                             'submenu' - Параметры для подменю.
	 *                             Можно указать массив.
	 *                             Или true. Тогда параметры будут скопированы из этих.
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function getHtml($params) {

		if (is_numeric($params)) $params = array('post_parent'=>$params);

		// Получаем данные кнопок
		if ($list = static::getData($params)) {

			if (!isset($params['template'])) {
				$params['template'] = static::getTemplatePhpFile();
			}
			
			$template = $params['template'];
			$templateDefault = null;
			if (is_array($template)) {
				$templateDefault = $template[1];
				$template = $template[0];
			}

			return wdpro_render_php($template, $list, $templateDefault);
		}
	}


	/**
	 * Возвращает данные меню по параметрам
	 *
	 * @param null|array $params
	 * @return array
	 * @throws Exception
	 * @throws \Wdpro\EntityException
	 */
	public static function getData($params=null)
	{
		if (is_string($params)) $params = array('type'=>$params);
		
		$params = wdpro_extend(array(
			'post_parent'=>0,
		), $params);

		$paramsForSubmenu = wdpro_extend(array(
			'type'=>null,
			'entity'=>null,
		), $params);

		// Сущность
		if (!isset($params['entity']))
		{
			$params['entity'] = static::getEntityClass();
		}
		/** @var \Wdpro\BasePage|null $entityClass */
		$entityClass = null;
		if (isset($params['entity']) && $params['entity'])
		{
			$entityClass = $params['entity'];
		}


		// Таблица
		if (!isset($params['sqlTable']) && $entityClass)
		{
			$params['sqlTable'] = $entityClass::sqlTable();
		}
		if (!isset($params['sqlTable'])) {
			$params['sqlTable'] = static::sqlTable();
		}
		/** @var \Wdpro\BaseSqlTable|null $entityClass */
		
		// Тип постов
		if (!isset($params['type']) && $params['sqlTable'])
		{
			$params['type'] = static::getType();
		}
		if (!isset($params['type']) && $params['sqlTable'])
		{
			$params['type'] = $params['sqlTable']::getName();
		}
		
		if (!isset($params['type']) || !$params['type'])
		{
			print_r($params);
			throw new Exception('Для получения меню не был указан тип. Необходимо 
			указать тип меню, таблицу или сущность страниц меню');
		}


		// Fields
		$params = wdpro_extend(array(
			'post_parent'=>$params['post_parent'],
			'fields'=>static::sqlFields(),
		), $params);

		// Заменяем ID на id, когда это не основная таблица
		if ($params['sqlTable'] != '\Wdpro\Page\SqlTable') {
			$params['fields'] = str_replace('ID', 'id', $params['fields']);
		}


		// Where
		// Поле таблицы in_menu
		// Которое обозначает кнопки, которые отображать в меню и которые нет
		$inMenuSql = '';
		$sqlTable = $params['sqlTable'];
		/** @var $sqlTable \Wdpro\BaseSqlTable */
		if ($sqlTable::isField('in_menu')) {
			$inMenuSql = 'AND in_menu=1 ';
		}

		// Основная таблица
		if ($params['sqlTable'] === '\Wdpro\Page\SqlTable') {
			$params = wdpro_extend(array(
				'where'=>array(
					'WHERE post_type=%s
					AND post_parent=%d
					AND post_status="publish"
					AND post_title[lang]!=""
					'.$inMenuSql.'
					ORDER BY menu_order',
					$params['type'],
					$params['post_parent'],
				),
			), $params);
		}
		else {
			$params = wdpro_extend(array(
				'where'=>array(
					'WHERE post_parent=%d
					AND post_status="publish"
					AND post_title[lang]!=""
					'.$inMenuSql.'
					ORDER BY menu_order',
					$params['post_parent'],
				),
			), $params);
		}

		$params = static::params($params);

		$params['where'] = \Wdpro\Lang\Data::replaceLangShortcode($params['where']);
		$params['fields'] = \Wdpro\Lang\Data::replaceLangShortcode($params['fields']);

		if ($sel = $params['sqlTable']::select($params['where'], $params['fields']))
		{
			$data = array();
			
			foreach($sel as $row)
			{
				if ($row['ID'])
				{
					$row['id'] = $row['ID'];
				}
				
				$alternativeUrl = get_post_field('alternative_url', $row['id']);
				if ($alternativeUrl)
				{
					if (!preg_match('~^http://~', $alternativeUrl))
					{
						$alternativeUrl = home_url($alternativeUrl);
					}
					$row['url'] = $alternativeUrl;
				}
				
				else
				{
					//$row['url'] = home_url($row['post_name']);
					$row['url'] = \Wdpro\Lang\Data::currentUrl().$row['post_name'];
				}
				$row['text'] = $row['post_title'];

				// Active
				if (wdpro_breadcrumbs()->isUri($row['post_name'])) {
					$row['active'] = true;
				}

				
				$row = static::prepareDataForTemplate($row);
				
				// Подменю
				if (isset($params['submenu'])) {
					
					if (is_array($params['submenu'])) {
						
						$params['submenu'] = wdpro_extend(array(
							'type'=>$paramsForSubmenu['type'],
							'entity'=>$paramsForSubmenu['entity'],
						), $params['submenu']);
					}
					if ($params['submenu'] === 1 || $params['submenu'] === true) {

						$params['submenu'] = $paramsForSubmenu;
					}
					if ($params['submenu']) {
						
						if (!isset($params['submenu']['post_parent']))
						$params['submenu']['post_parent'] = $row['id'];
						$row['submenu'] = static::getData($params['submenu']);
					}
				}
				
				$data[] = $row;
			}
			
			return $data;
		}
	}


	/**
	 * Дополнительная обработка данных для шаблона
	 *
	 * @param array $row Строка из базы
	 * @return array Строка для шаблона
	 */
	public static function prepareDataForTemplate( $row ) {

		return $row;
	}


	/**
	 * Необходимые для списка поля
	 *
	 * @return string
	 * @example return "ID, post_title";
	 */
	public static function sqlFields() {
		return 'ID, post_title[lang] as post_title, post_name';
	}


	/**
	 * Тип постов
	 * 
	 * @return string
	 */
	public static function getType() {
		
	}


	/**
	 * Возвращает класс сущности страниц меню
	 * 
	 * @return \Wdpro\BasePage
	 */
	protected static function getEntityClass()
	{
		
	}


	/**
	 * Возвращает имя класса контроллера
	 *
	 * @return \Wdpro\BaseController
	 */
	public static function getController() {
		return \Wdpro\Page\Controller::class;
	}


	/**
	 * Обработка параметров запроса 
	 * (можно переопределить, для указания особых параметров)
	 * 
	 * @param array $params Параметры
	 * @return array
	 */
	protected static function params($params)
	{
		return $params;
	}

}