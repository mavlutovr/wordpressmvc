<?php
namespace Wdpro\Console;

use Wdpro\BaseRoll;
use Wdpro\Exception;

/**
 * Список объектов админки
 *
 * @package Wdpro\Console
 */
class Roll extends BaseRoll
{

	/**
	 * Список адресов с их количеством
	 * 
	 * Этот список нужен для того, чтобы когда есть одинаковые адреса списков, сделать 
	 * их разными, добавив в них разные номера
	 * 
	 * @var array
	 */
	protected static $urls = array();
	
	protected $_inited = false;
	protected $_params;


	/**
	 * Инициализация
	 *
	 * @return bool
	 * @throws RollException
	 * @throws \Exception
	 */
	public function init()
	{
		if (!$this->_inited)
		{
			$this->_inited = true;

			// Параметры списка
			$params = static::params();
			$params = wdpro_extend(
				array(
					'labels'       => array(
						'name'               => $params['labels']['label'],
						'singular_name'      => $params['labels']['label'],
						'menu_name'          => $params['labels']['label'],
					),
					/*'orderby'      => 'id',
					'order'        => 'ASC',*/
					'capability'=>WDPRO_ADMINISTRATOR,
				),
				$params
			);
			
			$this->_params = $params;


			// Регистрируем страницу
			add_action('admin_menu', function () {

				global $_registered_pages;

				$rollParams = $this->getParams();
				//$pageName= wdpro_text_to_file_name($rollParams['labels']['label']);
				$pageName = $this->getPageName();
				
				$pageName = plugin_basename( $pageName );
				$hookName = get_plugin_page_hookname($pageName, '');
				$_registered_pages[$hookName] = true;

				$admin_page_hooks[$pageName] = sanitize_title( $pageName );

				// Убрал это, т.к. получается двойное срабатывание
				if ($callback = $this->getPageCallback())
				add_action( $hookName, $this->getPageCallback() );
			});



			return true;
		}
		
		return false;
	}


	/**
	 * Возвращает количество элементов для родительского объекта
	 * 
	 * @param \Wdpro\BaseEntity $section Родительский объект
	 * @return null|number
	 */
	public function getCountForSection($section) {
		
		$table = static::sqlTable();
		
		if ($table::isField('section_id') && $table::isField('section_type')) {
			return $table::count(
				['WHERE `section_id`=%d AND `section_type`=%s',
					[$section->id(), $section::getType()]]
			);
		}
		
		if ($table::isField('post_parent')) {
			return $table::count(
				['WHERE `post_parent`=%d',
					[$section->id()]]
			);
		}
	}

	protected $callback;

	/**
	 * Возвращает каллбэк, который генерит страницу
	 *
	 * @return \Closure
	 */
	public function getPageCallback()
	{
		$runned = false;
		
		if (!$this->callback) {
			$this->callback  = function () use (&$runned)
			{
				if ($runned) return false;
				$runned = true;

				$this->viewPage();
			};
		}
		
		return $this->callback;
	}


	/**
	 * Отображает страницу модуля
	 * 
	 * @throws Exception
	 * @throws \Exception
	 */
	public function viewPage() {

		$params = $this->getParams();

		// Хлебные крошки
		$breadcrumbs = new \Wdpro\Breadcrumbs\ConsoleBreadcrumbs();

		// Родительские крошки
		if (isset($_GET['sectionId']) && $_GET['sectionId']) {

			$post = wdpro_get_post_by_id($_GET['sectionId']);

			$breadcrumbs->makeFrom($post);
			//$breadcrumbs->unremoveLink();

			// Когда мы в дочерних простых элементах, например, в фотогалерее
			if ($_GET['childsType'] || $_GET['breadParentOpenEdit']) {
				// Добавляем "Фотогалерея" в конец хлебных крошек
				$breadcrumbs->append($params['labels']['label']);

				$breadcrumbs->getPrepend( 0 )->setUri($post->getEditUrl())
					->setComment( 'Редактирование' );
			}

			else {
				$breadcrumbs->getPrepend( 0 )
					//->setComment( $params['labels']['label'] )
					->setUri(wdpro_current_url(array(
						'action' => null,
					)))
				;
			}

		}

		// Сами по себе, не прикрепленные к странице
		else {
			$breadcrumbs->append(array(
				'uri'=>wdpro_current_url( array(
					'action' => null,
				)),
				'text'=>$params['labels']['label'],
			));
		}

		// Заголовок и описание
		$text = '<h2>'.$params['labels']['label'].'</h2>';
		if ($about = $this->about())
		{
			$text .= '<div class="wdpro-about">'.$about.'</div>';
		}

		$entityClass = static::getEntityClass();
		$formIsset = !!$entityClass::getConsoleFormClass();


		// Просто информация
		if (isset($params['info'])) {
			$text .= '<div class="wdpro-info">'.$params['info'].'</div>';
		}


		// Добавление / Редактирование
		if (isset($_GET['action']) && $_GET['action'] == 'form')
		{
			// Форма добавления элемента
			$entity = static::getEntity(
				isset($_GET['id']) ? $_GET['id'] : true
			);
			$form = $entity->getConsoleForm();
			if (!$form)
				throw new Exception('Нету класса '.$entity::getNamespace()
					.'\\ConsoleForm');

			// Хлебные крошки
			if (isset($_GET['id'])) {
				$breadcrumbs->append('Редактирование');
			}
			else {
				$breadcrumbs->append($params['labels']['add_new']);
			}

			// Сохранение
			$form->onSubmit(function ($data) use (&$form, &$entity)
			{
				// Родительская страница / Объект
				if (isset($_GET['sectionId']) && $_GET['sectionId']) {
					$data['post_parent'] = $_GET['sectionId'];
					$data['section_id'] = $_GET['sectionId'];

					if (isset($_GET['sectionType']))
						$data['section_type'] = $_GET['sectionType'];
				}

				// Установка даты
				// Следующий порядковый номер
				// Сохранение
				$entity->mergeData($data)
					->nextSortingToData($this->getWhere())
					->save();

				// Переход к предыдущей странице
				wdpro_location(
					wdpro_current_url(array(
						'action'=>null,
						'id'=>null,
						'mes'=>'added',
					))
				);
			});

			$text .= $form->getHtml();
		}


		// Список элементов
		else
		{
			// Удаление
			if (isset($_GET['action']) && $_GET['action'] == 'remove')
			{
				static::getEntity($_GET['id'])->remove();

				wdpro_location(
					wdpro_current_url(array(
						'action'=>null,
						'id'=>null,
						'mes'=>'removed',
					))
				);
			}

			// Кнопка "Добавить"
			if ($addBut = $this->getAddBut())
			{
				$text .= $addBut;
			}

			// Список
			if ($where = $this->getWhere())
			{
				// Постраничность
				$paginationHtml = '';
				if (isset($params['pagination'])) {
					if (is_numeric($params['pagination']))
						$params['pagination'] = array(
							'pageSize'=>$params['pagination'],
						);
					$pagination = new \Wdpro\Tools\Pagination($params['pagination']);
					$pagination->initByWhere($where, static::sqlTable());
					$where .= $pagination->getLimit();
					$paginationHtml = $pagination->getHtml();
				}

				// Для определения есть ли новые записи
				$lastId = (int)get_option(get_called_class().':lastId:'
					.get_current_user_id());

				if ($sel = static::sqlTable()->select($where))
				{
					$table = '<table 
class="wdpro-console-roll-table wp-list-table widefat fixed striped pages js-roll" 
id="wdpro-console-roll-'.static::getType().'">';

					// Заголовки
					if ($headers = $this->templateHeaders())
					{
						$table .= '<thead><tr>';

						foreach($headers as $header)
						{
							$table .= '<td>'.$header
								.'</td>';
						}

						$table .= '<td></td></tr></thead>';
					}


					// Содержание таблицы
					$table .= '<tbody>';
					foreach($sel as $row)
					{
						$lastId = max($row['id'], $lastId);

						/*$templateData[] = static::getEntity($row)
							->getConsoleTemplateData();*/
						$entity = static::getEntity($row);
						$row = $entity->getConsoleTemplateData();

						// Кнопки редактирования
						$buts = '';

						// ID
						if (isset($row['ID'])) $row['id'] = $row['ID'];

						// Редактировать
						if ($formIsset) {
							$buts .= '<a href="'
								.wdpro_current_url(array(
									'action'=>'form',
									'id'=>$row['id'],
								))
								.'" class="g-ml10"><span 
								class="dashicons dashicons-edit"></span> Изменить</a>';
						}

						// Удалить
						$buts .= '<span class="trash g-ml10 g-nowrap"><a href="'
							.wdpro_current_url(array(
								'action'=>'remove',
								'id'=>$row['id'],
							))
							.'" 
class="dashicons dashicons-trash js-del" 
title="Удалить"></a>
								</span>';


						$contentColls = $this->template($row, $entity);
						$tds = '';
						foreach($contentColls as $coll)
						{
							$tds .= '<td class="wdpro-console-roll-td">'.$coll.'</td>';
						}

						$table .= '
							<tr class="js-row" 
							data-key="'.$entity->getKey().'" 
							data-id="'.$row['id'].'">
								'.$tds.'
								<td class="g-align-right wdpro-row">'.$buts.'</td>
							</tr>';
					}

					update_option(
						get_called_class().':lastId:'
						.get_current_user_id(),
						$lastId
					);

					$table .= '</tbody></table>';
					$text .= $table;

					$text .= $paginationHtml;
				}
				else
				{
					$text .= '<p>Пусто</p>';
				}
			}
		}

		echo $breadcrumbs->getHtml();

		echo $text;
	}


	/**
	 * Возвращает количетсво новых элементов
	 * 
	 * @return null|number
	 * @throws \Exception
	 */
	public function getNewCount() {

		$lastId = (int)get_option(get_called_class().':lastId:'
			.get_current_user_id());
		
		

		
		$count = static::sqlTable()->count([
			'WHERE id>%d',
			$lastId
		]);
		
		if ($count) {
			
			return $count;
		}
	}


	/**
	 * Возвращает параметры списка (необходимо переопределить в дочернем классе для
	 * установки настроек)
	 * 
	 * <pre>
	 * return array(
	 *  'labels'=>array(
	 *   'name'=>'Разделы каталога',
	 *   'label'=>'Каталог',
	 *   'add_new'=>'Добавить раздел...',
	 *  ),
	 *  'order'=>'ASC',
	 *  'orderby'=>'menu_order',
	 *  'icon'=>WDPRO_ICONS_PRODUCTS,
	 *      // https://developer.wordpress.org/resource/dashicons/#lock
	 * 		// http://fontawesome.io/icons/
	 * 
	 *  'subsections'=>false,
	 *  'where'=>["WHERE ... %d, %d", [1, 2]],
	 *  'pagination'=>10, //  Количество элементов на странице
	 *  'info'=>'<p>Всякая информация над списком элементов</p>'
	 *
	 *  // В страницах
	 *  'order' => 'DESC',
	 *  'orderby' => 'menu_order',
	 * );
	 * </pre>
	 *
	 * @return array
	 */
	public static function params()
	{

	}


	/**
	 * Возвращает uri адрес для кнопки меню в админке
	 *
	 * @param null|array $params Параметры
	 * @return string
	 * @throws RollException
	 */
	public function getRollUri($params=null)
	{
		// wp-admin/admin.php?page=Fajli&sectionId=143
		// wp-admin/admin.php?page=admin.php%3Fpage%3DFajli

		// admin.php?page=Fajli
		// admin.php?page=admin.php%3Fpage%3DFajli
		
		
		// Начальный адрес
		//$uri = 'admin.php?page=';
		$uri = '';
		
		// Получаем адрес
		$rollParams = $this->getParams();
		//$pageName= wdpro_text_to_file_name($rollParams['labels']['label']);
		$pageName = $this->getPageName();
		$uri .= $pageName;
		

		// Параметры query_string
		if (isset($params['query']) && $params['query']) {
			
			$uri .= '&'.$params['query'];
		}
		
		//return 'admin.php?page='.$uri;
		return $uri;
	}


	/**
	 * Возвращает имя страницы в адресе
	 *
	 * @return string
	 */
	public function getPageName() {

		/*$params = $this->getParams();

		if (isset($params['urlName']) && $params['urlName']) {
			$pageName = $params['urlName'];
		}
		else {
			$pageName = $params['labels']['label'];
		}*/

		$pageName = get_called_class();
		$pageName = str_replace('\\', '.', $pageName);

		return wdpro_text_to_file_name($pageName);
	}

	
	/**
	 * Возвращает класс сущностей данного списка
	 *
	 * @return \Wdpro\BaseEntity
	 */
	public static function getEntityClass()
	{
		return static::getNamespace().'\\Entity';
	}


	/**
	 * Возвращает тип сущностей этого списка
	 * 
	 * @return string
	 */
	public static function getType() {
		$class = static::getEntityClass();
		return $class::getType();
	}


	/**
	 * Возвращает объект таблицы сущностей данного списка
	 *
	 * @return \Wdpro\BaseSqlTable
	 * @throws \Exception
	 */
	public function sqlTable()
	{
		if ($objectClass = static::getEntityClass())
		{
			return $objectClass::sqlTable();
		}

		else
		{
			throw new \Exception('Нет класса сущностей для списка '.get_called_class());
		}
	}


	/**
	 * Возвращает объект сущности по данным сущности
	 *
	 * @param array $data Данные
	 * @returns \Wdpro\BaseEntity
	 * @throws \Exception
	 */
	public function getObject($data)
	{
		if ($class = static::getEntityClass())
		{
			return wdpro_object($class, $data);
		}

		else
		{
			throw new RollException('Нет класса сущностей для списка '.get_called_class());
		}
	}


	/**
	 * Возвращает объект сущности списка
	 *
	 * @param int|array|null|bool $entityIdOrData IШвили данные объекта
	 * @return \Wdpro\BasePage|void
	 * @throws \Exception
	 */
	public static function getEntity($entityIdOrData=null)
	{
		if ($entityIdOrData)
		{
			if ($entityIdOrData === true)
			{
				$entityIdOrData = null;
			}
			return wdpro_object(static::getEntityClass(), $entityIdOrData);
		}
	}


	/**
	 * Возвращает адрес страницы для открытия этого списка
	 *
	 * @return string
	 * @throws RollException
	 */
	public function getConsoleListUri()
	{
		return $this->getRollUri();
	}


	/**
	 * Возвращает текст кнопки, которая открывает этот список
	 *
	 * @return string
	 */
	public function getButtonText()
	{
		$params = $this->getParams();

		return $params['labels']['label'];
	}


	/**
	 * Возвращает параметры списка
	 *
	 * @return mixed
	 * @throws RollException
	 */
	public function getParams()
	{
		$this->init();
		return $this->_params;
	}


	/**
	 * Текст, который отображается вверху страницы
	 * 
	 * @return string
	 */
	public function about() {
		
	}


	/**
	 * Возвращает колонки таблицы
	 *
	 * @param array $data Данные строки
	 * @param \Wdpro\BaseEntity $entity Объект списка
	 * @return array
	 */
	public function template($data, $entity)
	{
		return array('Переопределите '.get_called_class().'->template($data) {}');
	}


	/**
	 * Возвращает заголовки таблицы в виде массива
	 * 
	 * @return array
	 */
	public function templateHeaders()
	{
		
	}


	/**
	 * Возвращает кнопку "Добавить"
	 * 
	 * @return string
	 */
	public function getAddBut()
	{
		$params = $this->getParams();
		
		if (isset($params['labels']['add_new']))
		{
			//$href = 'admin.php?page='.$_GET['page'].'&action=form';
			
			$href = wdpro_current_url(array(
				'action'=>'form',
				'id'=>null,
			));
			
			return '<p><a href="'.$href.'" class="'.WDPRO_BUTTON_CSS_CLASS.'">'
				.$params['labels']['add_new']
			.'</a></p>';
		}
	}


	/**
	 * Возвращает Where запрос
	 * 
	 * @return string|void
	 */
	protected function getWhere()
	{
		if (!($where = $this->where()))
		{
			$params = $this->getParams();
			if (isset($params['where']))
			{
				$where = $params['where'];
			}
		}
		
		if ($where) {
			
			if (is_string($where)) $where = [$where];
			
			if (is_array($where)) {
				
				global $wpdb;
				if (isset($where[1])) {
					$where = $wpdb->prepare($where[0], $where[1]);
				}
				else {
					$where = $where[0];
				}
				
				
			}
			
			return $where;
		}
	}


	/**
	 * Where запрос для получения элементов списка (переопределяется)
	 * 
	 * @return void|string
	 */
	public function where()
	{
		
	}
}

