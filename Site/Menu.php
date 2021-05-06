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
	 *                             первого не будет, то первый будет создан по второму.
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
	 * @throws \Wdpro\EntityException
	 */
	public static function getHtml($params) {

		if (is_numeric($params)) $params = array('post_parent'=>$params);

		$params = wdpro_extend([
			'template'=>WDPRO_TEMPLATE_PATH.'menu_standart.php',
		], $params);

		// Получаем данные кнопок
		if (!isset($params['list'])) {
			$params['list'] = static::getData($params);
		}
		if ($params['list']) {

			if (!isset($params['template'])) {
				$params['template'] = static::getTemplatePhpFile();
			}

			$template = $params['template'];
			$templateDefault = null;
			if (is_array($template)) {
				$templateDefault = $template[1];
				$template = $template[0];
			}

			return wdpro_render_php($template, [
				'list'=>$params['list'],
				'params'=>$params,
				'pagination'=>'',
			], $templateDefault);
		}
	}


	/**
	 * Возвращает данные меню по параметрам
	 *
	 * @param null|array $params
	 * @param null|array $fields Поля, которые необходимо выбрать из таблицы (это для совместимости с \Wdpro\Site\Roll)
	 * @return array
	 * @throws Exception
	 * @throws \Wdpro\EntityException
	 */
	public static function getData($params=null, $fields=null)
	{
		if (is_string($params)) $params = array('type'=>$params);

		$paramsForSubmenu = wdpro_extend(array(
			'type'=>null,
			'entity'=>null,
		), $params);

		$params = wdpro_extend(array(
			'post_parent'=>0,
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
				'where'=>'WHERE post_type=%s
									AND post_parent=%d
									AND post_status="publish"
									AND post_title[lang]!=""
									'.$inMenuSql.'
									ORDER BY menu_order',
			), $params);

			$where = array(
				$params['where'],
				$params['type'],
				$params['post_parent'],
			);

		}
		else {

			$params = wdpro_extend(array(
				'where'=>'WHERE post_parent=%d
									AND post_status="publish"
									AND post_title[lang]!=""
									'.$inMenuSql.'
									ORDER BY menu_order',
			), $params);


			if (is_array($params['where'])) {
				$where = $params['where'];
			}
			else {
				$where = array(
					$params['where'],
					[],
				);

				if (strstr($where[0], '%d')) {
					$where[1][] = $params['post_parent'];
				}
			}
		}

		$params = static::params($params);

		$params['fields'] = \Wdpro\Lang\Data::replaceLangShortcode($params['fields']);

		if (isset($where[0])) {
			$where[0] = \Wdpro\Lang\Data::replaceLangShortcode($where[0]);
		}
		else if ($where) {
			$where = \Wdpro\Lang\Data::replaceLangShortcode($where);
		}


		if ($sel = $params['sqlTable']::select($where, $params['fields']))
		{

			$data = array();

			foreach($sel as $row)
			{
				if (isset($row['ID']) && $row['ID'])
				{
					$row['id'] = $row['ID'];
				}

				$row['attrs'] = '';
				$row['before'] = '';
				$row['after'] = '';
				$alternativeUrlOriginal = get_post_field(
					'alternative_url'.\Wdpro\Lang\Data::getCurrentSuffix(),
					$row['id']);
				$alternativeUrl = $alternativeUrlOriginal;

				if ($alternativeUrlOriginal)
				{
					if (preg_match('~^https?://~', $alternativeUrl)) {
						$row['attrs'] .= ' target="_blank" rel="nofollow"';
						$row['before'] = '<noindex>';
						$row['after'] = '</noindex>';
					}
					else {
						$alternativeUrl = home_url($alternativeUrl);
					}

					$row['url'] = $alternativeUrl;
				}

				else
				{
					$post = wdpro_get_post_by_id($row['id']);
					$row['url'] = $post->getUrl();
				}
				$row['text'] = $row['post_title'];


				// Прям текущая страница
				if (wdpro_is_current_post_name($row['post_name'])
					|| ($alternativeUrlOriginal && wdpro_is_current_post_name($alternativeUrlOriginal))) {
					$row['current'] = true;
				}
				else {
					$row['current'] = false;
				}

				$row = static::prepareDataForTemplate($row);


				// Выбранность кнопки
				// В хлебных крошках
				if (wdpro_breadcrumbs()->isUri($row['post_name']) 
					|| ($alternativeUrlOriginal && wdpro_breadcrumbs()->isUri($alternativeUrlOriginal))) {

					$row['active'] = true;
					$row['breadcrumbs'] = true;
				}

				else {
					$currentPage = wdpro_breadcrumbs()->getCurrentPage();
					if ($currentPage && $currentPage->isUri($row['post_name'])) {
						$row['active'] = true;
						$row['breadcrumbs'] = false;
					}

					else {
						$row['active'] = false;
						$row['breadcrumbs'] = false;
					}
				}


				// Тип записи
				$row['entity_type'] = $entityClass::getType();


				// Подменюшки
				if (!empty($params['submenu'])) {

					$submenus = $params['submenu'];

					if (!is_array($submenus) || !isset($submenus[0]))
						$submenus = [$submenus];

					$row['submenu'] = '';
					$row['submenu_array'] = [];

					foreach ($submenus as $submenu) {

						// Условие в каллбэке
						if (!empty($submenu['show']) && is_callable($submenu['show'])) {
							$callableShow = $submenu['show']($row);
							if (!$callableShow) {
								$submenu = false;
							}
						}

						// В хлебных крошках
						if (wdpro_breadcrumbs()->isUri($row['post_name'])) {
							// Указано показывать подменю только если кнопка есть в хлебных крошках
							if ($submenu === 'breadcrumbs' || $submenu === 'active'
							|| isset($submenu['show'])
								&& ($submenu['show'] === 'breadcrumbs' || $submenu['show'] === 'active')) {
								$submenu = true;
							}
						}

						// Не в хлебных крошках
						else {
							// Когда у подменю параметры, в которых указано, что отображать кнопку только когда она в хлебных крохах
							if (isset($submenu['show']) && $submenu['show'] === 'breadcrumbs') {
								// Убираем подменю
								$submenu = false;
							}
						}

						// Подменю
						if ($submenu) {

							// Указаны параметры
							if (is_array($submenu)) {

								$submenu = wdpro_extend(array(
									'type'=>$paramsForSubmenu['type'],
									'entity'=>$paramsForSubmenu['entity'],
									'fields'=>$paramsForSubmenu['fields'],
									'submenu'=>$submenus,
									'template'=>$paramsForSubmenu['template'],
								), $submenu);
							}

							if ($submenu === true || $submenu === 1) {

								$submenu = $paramsForSubmenu;
							}

							if (is_array($submenu)) {
								$submenu['post_parent'] = $row['id'];
								$submenuHtml = static::getHtml($submenu);
								$row['submenu'] .= $submenuHtml;
								$row['submenu_array'][] = $submenuHtml;
							}
						}
					}
				}

				else {
					$row['submenu'] = '';
					$row['submenu_array'] = [];
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
	public static function getEntityClass()
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
