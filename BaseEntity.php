<?php
namespace Wdpro;

$consoleRollClasses = array();
//$sqlTableClasses = array();

/**
 * Базовый класс сущностей
 * 
 * @package Wdpro
 */
abstract class BaseEntity
{
	use Tools;
	
	/** @var \Wdpro\Form\Form */
	protected $_consoleForm;
	protected $_loaded = false;
	protected $_loadedFromTable = false;
	protected $_removed = false;
	protected $_id;
	protected $_created = false;
	protected static $consoleRollClass;
	protected $childsType;


	/**
	 * Данные сущности
	 * 
	 * @var array
	 */
	public $data;


	/**
	 * Возвращает экземпляр объекта
	 *
	 * @param int|string|array $dataOrId ID или данные объекта
	 *
	 * @return $this
	 */
	public static function instance($dataOrId) {
		return static::getEntity($dataOrId);
	}


	/**
	 * Возвращает экземпляр объекта
	 *
	 * @param int|string|array $dataOrId ID или данные объекта
	 *
	 * @return $this
	 */
	public static function getEntity($dataOrId) {
		return wdpro_object(get_called_class(), $dataOrId);
	}


	/**
	 * Инициализация сущности
	 * 
	 * Чтобы ее таблица инициировалась и обновилась струкрута и подготовилась к работе
	 */
	public static function init() {
		
		/** @var \Wdpro\BaseSqlTable $table */
		if ($table = static::getSqlTableClass()) {
			
			$table::init();
		}
	}


	/**
	 * @param array|int|null $idOrData ID или данные сущности
	 */
	public function __construct($idOrData=null)
	{
		if ($idOrData)
		{
			if (is_array($idOrData))
			{
				$this->setData($idOrData);
				$this->_loaded = true;
				if (isset($idOrData['_from_db']))
				{
					$this->_loadedFromTable = true;
				}
			}
			else
			{
				$this->loadData($idOrData);
			}
		}
	}


	/**
	 * Загрузка данных из базы
	 * 
	 * @param int|null $id ID сущности
	 * @return bool true, если данные загрузились
	 * @throws EntityException
	 */
	public function loadData($id=null)
	{
		if ($id) {
			$this->_id = $id;
		}
		else {
			$id = $this->id();
		}

		if ($data = static::sqlTable()->getRow(
			array('WHERE id=%d', $id)
		))
		{
			$this->setData($data);
			$this->_loaded = true;
			$this->_loadedFromTable = true;

			return true;
		}
	}


	/**
	 * True, если данная сущность уже находится в базе (не новая)
	 * 
	 * @return bool
	 */
	public function existsInDb()
	{
		return isset($this->data['_from_db']);
	}


	/**
	 * Установка новых данных сущности
	 *
	 * @param array $data Данные
	 * @return $this
	 */
	public function setData($data)
	{
		$this->data = static::prepareDataAfterLoad($data);

		return $this;
	}


	/**
	 * Обновление данных сущности
	 *
	 * @param array $data Данные
	 * @return $this
	 */
	public function mergeData($data)
	{
		$this->data = wdpro_extend($this->data, $data);
		
		return $this;
	}


	/**
	 * Установка данных формы из админки
	 *
	 * Для того, чтобы поменять эти данные, переопределите метод prepareDataFromConsoleForm
	 *
	 * @param array $data Данные формы
	 * @return $this
	 */
	public function consoleMergeDataFromForm($data) {
		$data = $this->prepareDataFromConsoleForm($data);
		$this->mergeData($data);
		return $this;
	}


	/**
	 * Обработка данных, которые пришли из формы админки
	 *
	 * @param array $data Данные из формы админки
	 * @return array
	 */
	public function prepareDataFromConsoleForm($data) {

		return $data;
	}


	/**
	 * Возвращает данные для формы админки
	 *
	 * Для изменения этих данных переопределите метод prepareDataForConsoleForm
	 *
	 * @return array
	 */
	public function consoleGetDataForForm() {

		$data = $this->getData();
		$data = $this->prepareDataForConsoleForm($data);

		return $data;
	}


	/**
	 * Обработка данных перед отправкой их в форму админки
	 *
	 * @param array $data Данные
	 * @return array
	 */
	public function prepareDataForConsoleForm($data) {

		return $data;
	}


	/**
	 * Возвращает данные сущности
	 *
	 * @param null|string $key Ключ данных, которые необходимо получить.
	 * Если ключ не указывать, то будут возвращены все данные
	 * @return array|mixed
	 */
	public function getData($key=null)
	{
		if ($key === null)
		{
			return $this->data;
		}
		else
		{
			$keys = func_get_args();
			$value = $this->data;

			foreach ($keys as $key) {
				if (strstr($key, '[lang]')) {
					$key = \Wdpro\Lang\Data::replaceLangShortcode($key);
				}

				if (isset($value[$key])) {
					$value = $value[$key];
				}
				else {
					return null;
				}
			}

			return $value;

			/*if (strstr($key, '[lang]')) {
				$key = \Wdpro\Lang\Data::replaceLangShortcode($key);
			}

			if (isset($this->data[$key])) return $this->data[$key];*/
		}
	}


	/**
	 * В BaseEntity и BasePage различается механизм запуска методов для обработки данных
	 * перед первым сохранением (созданием) сущности в базу
	 *
	 * Именно за счет этого метода и реализован разный механизм
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	protected function _prepareData($data) {
		$data = $this->prepareDataForCreate($data);

		return $data;
	}


	/**
	 * Сохранение
	 * 
	 * @returns bool|array (false или сохраненные данные)
	 * @throws EntityException
	 */
	public function save()
	{
		$data = $this->data;
		if (!$data) {
			$data = [];
		}
		
		if (is_array($data))
		{
			$created = false;

			// Сначала Запускаем обработку данных перед созданием
			if (!$this->existsInDb())
			{
				$data = $this->_prepareData($data);
				$created = true;
			}

			// А потом делаем обработку данных при сохранении
			// Чтобы во время этой обработки уже были известны результаты обработки перед созданием
			$data = $this->prepareDataForSave($data);
			$this->_created = $created;
			
			// Если данные это true или false, значит сохранение было прервано
			if (is_bool($data)) {
				return $data;
			}
			
			$this->data = $data;

			// Чтобы не было ошибки из-за лишнего поля
			/*foreach($data as $key=>$value)
			{
				if (!static::sq)
			}*/

			// Данные загружены из таблицы
			if ($this->_loadedFromTable 
				|| ($this->id() && static::sqlTable()->count(
					array('WHERE id=%d', $this->id())
				) > 0)
			)
			{
				static::sqlTable()->update(
					$data, 
					array(static::idField()=>$this->id()),
					null,
					array('%d')
				);
			}
			
			
			// Данные не были загружены из таблицы
			else
			{
				// Если это новая сущность, удаляем ее из списка объектов, как новую сущность
				// Чтобы потом при обращении к пустой сущности не загружалась эта не пустая сущность
				$new = false;
				// А создавалась новая пустая сущность
				if (true || !$this->id()) {
					$new = true;
					wdpro_object_remove_from_cache($this);
				}

				if (!isset($data[static::idField()]) && $this->id())
				{
					$data[static::idField()] =  $this->id();
				}
				
				$this->_id = static::sqlTable()->insert($data);
				$this->data[static::idField()] = $this->_id;

				// Если это новая сущность, добавляе ее в кэш
				if ($new) {
					wdpro_object_add_to_cache($this);
				}
			}
			
			$this->onChange();
			$this->trigger('change', $this->getData());

			return $this->data;
		}
		
		else
		{
			throw new EntityException(
				'Не удалось сохранить сущность, т.к. нет данных для сохранения'
			);
		}
	}


	/**
	 * Возвращает название поля ID
	 * 
	 * @return string
	 * @throws EntityException
	 */
	public function idField() {
		
		return static::sqlTable()->isField('ID') ? 'ID' : 'id';
	}


	/**
	 * Подготавливает данные для сохранения
	 *
	 * @param array $data Исходные данные
	 * @return array
	 */
	protected function prepareDataForSave($data)
	{
		return $data;
	}


	/**
	 * Обработка данных после загрузки из базы
	 *
	 * @param array $data Данные
	 *
	 * @return array
	 */
	protected function prepareDataAfterLoad($data) {
		return $data;
	}


	/**
	 * Срабатывает после сохранения
	 */
	protected function onChange() {
		
	}


	/**
	 * Проверяет, был ли этот объект только что создан при сохранении или нет
	 * 
	 * @return bool
	 */
	protected function created() {
		
		return $this->_created;
	}


	/**
	 * Срабатывает после удаления
	 */
	protected function onRemove() {
		
	}


	/**
	 * Подготавливает данные для сохранения перед первым сохранением в базе
	 *
	 * В вордпресс страницы сохраняются сразу, как только была открыта форма создания.
	 * То есть еще до того, как заполнили форму создания.
	 *
	 * Этот метод обрабатывает данные как бы перед нормальным созданием. Когда уже
	 * заполнили форму. И это обработка данных первого сохранение формы.
	 * 
	 * @param array $data Исходные данные
	 *
	 * @return array
	 */
	protected function prepareDataForCreate($data)
	{
		return $data;
	}


	/**
	 * Берет следующий порядковый номер и добавляет его в данные
	 *
	 * @param string $where WHERE запрос, по которому строиться список этих сущностей, в
	 * котором идет сортировка
	 * @return $this
	 * @throws EntityException
	 */
	public function nextSortingToData($where)
	{
		if (static::sqlTable()->isField('sorting'))
		{
			if (!$this->data['sorting'])
			{
				$where = preg_replace('~(ORDER BY [.`\s\S]+)~', '', $where);

				$where .= ' ORDER BY `sorting` DESC';

				if ($row = static::sqlTable()->getRow($where, 'sorting'))
				{
					$this->data['sorting'] = ceil(($row['sorting'] + 10)/10)*10;
				}
				else
				{
					$this->data['sorting'] = 10;
				}
			}
		}
		
		return $this;
	}

	/**
	 * Аналог $this->sqlTable();
	 * 
	 * @return BaseSqlTable
	 * @throws EntityException
	 */
	public function getSqlTable()
	{
		return $this->sqlTable();
	}


	/**
	 * Возвращает имя таблицы
	 *
	 * @return string
	 */
	public static function getSqlTableName()
	{
		/*global $sqlTableClasses;
		if (!$sqlTableClasses[get_called_class()])
		{
			$sqlTableClasses[get_called_class()] = 
		}*/
		
		/** @var \Wdpro\BaseSqlTable $table */
		if($table = static::getSqlTableClass())
		{
			return $table::getName();
		}
	}


	/**
	 * Возвращает форму
	 *
	 * @param array $params Параметры 
	 * @return Form\Form
	 */
	public function getConsoleForm($params=null)
	{
		if (!$this->_consoleForm)
		{
			if ($formClass = static::getConsoleFormClass())
			{
				/** @var \Wdpro\Form\Form _consoleForm */
				$this->_consoleForm = new $formClass(array(
					'entity'=>$this,
					'params'=>$params,
				));
				
				if ($this->loaded())
				{
					$this->_consoleForm->setData($this->consoleGetDataForForm());
				}
			}
			else
			{
				new EntityException(
					'У сущности '.get_class($this).' не указано класс формы админки
					в методе getConsoleFormClass'
				);
			}
		}

		return $this->_consoleForm;
	}


	/**
	 * Возвращает имя класса формы админки сущности
	 * 
	 * @return string
	 */
	public static function getConsoleFormClass()
	{
		if (\Wdpro\Autoload::isClass(static::getNamespace().'\\ConsoleForm'))
		{
			return static::getNamespace().'\\ConsoleForm';
		}
	}


	/**
	 * True, если сущность загружена из базы
	 * 
	 * @return bool
	 */
	public function loaded()
	{
		return $this->_loaded;
	}


	/**
	 * Возвращает тот ID, который был указан при создании объекта или при загрузке 
	 * данных с помощью метода loadData($id)
	 * 
	 * ID возвращается независимо от того, были ли загружены данные
	 * 
	 * @return int
	 */
	public function id()
	{
		if (isset($this->data[static::idField()])) {
			return $this->data[static::idField()];
		}
		return $this->_id;
	}
	

	/**
	 * Возвращает тип поста
	 *
	 * @return string
	 */
	public static function getType()
	{
		if (isset(static::$post_type) && static::$post_type) {
			return static::$post_type;
		}
		return static::getSqlTableName();
	}


	/**
	 * Добавляет к действиям страниц (удалить, изменить, свойства...)
	 *
	 * @param $actions
	 *
	 * @return mixed
	 */
	public function addChildsToActions($actions)
	{
		if ($childsParams = static::childs())
		{
			foreach($childsParams as $childParams)
			{
				// wp-admin/edit.php?post_type=app_course_lesson&sectionId=141
				// wp-admin/Fajli&sectionId=143
				
				if (is_string($childParams))
					$childParams = ['roll'=>$childParams];
				
				
				// Это список дочерних объектов
				/** @var \Wdpro\Console\PagesRoll $roll */
				$roll = wdpro_object($childParams['roll']);
				
				$rollParams = $roll::params();
				
				// Если здесь убрать "admin.php?page=", то не будет работать ссылка на
				// дочерние объекты, когда дочерние объекты обычные списки типа Roll
				// Как здесь если нажать на Файлы http://localhost/club.tridodo.ru/secure_wp/wp-admin/edit.php?post_type=app_course_lesson&sectionId=141
				$link = $roll->getRollUri(array(
					'query'=>'sectionId='.$this->id()
						.'&sectionType='.static::getType()
						.'&childsType='.$roll::getType(),
				));
				if (!strstr($link, '.php')) {
					$link = 'admin.php?page='.$link;
				}
				$entityClass = $roll->getEntityClass();
				$type = $entityClass::getType();

				// Иконка
				$icon = '';
				$iconClasses = '';
				if (!isset($childParams['icon'])) {
					$childParams['icon'] = $rollParams['icon'];
				}

				if (isset($childParams['icon']) && $childParams['icon'])
				{
					// Awesome 5
					if (strstr($childParams['icon'], 'fas')
					|| strstr($childParams['icon'], 'far')
					|| strstr($childParams['icon'], 'fal')
					|| strstr($childParams['icon'], 'fab')) {
						
						$iconClasses = 'fa5 '.$childParams['icon'];
					}

					// Awesome 4
					else if (strstr($childParams['icon'], 'fa-')) {
						$iconClasses = 'fa '.$childParams['icon'];
					}

					else {
						$iconClasses = 'dashicons '.$childParams['icon'];
					}
				}

				$icon = '<i class="'.$iconClasses.'"></i> ';

				// Текст кнопкии
				if (!isset($childParams['label'])) {
					$childParams['label'] = $rollParams['labels']['label'];
				}
				
				//$count = $roll->getChildsCount($this->id(), $type);
				$count = $this->getChildsCount($roll);
				$countPrint = '';
				if ($count)
				{
					$countPrint = '<span class="js-children-count wdpro-rounded-count">'
						.$count.'</span>';
				}
				
				$actions['wdpro_'.$type] = 
					'<a href="'.$link.'" 
					class="js-children-button"
					data-icon="'.$iconClasses.'"
					data-label="'.$childParams['label'].'">'
					.$icon.$childParams['label'].$countPrint
					.'</a>';
			}
		}
		
		return $actions;
	}


	/**
	 * Добавляет к действиям страниц подразделы
	 *
	 * @param $actions
	 *
	 * @return mixed
	 */
	public function addSubsectionsToActions($actions) {
		// Количество подразделов
		$subsectionsCount = $this->getSubsectionsCount();
		if ($subsectionsCount) {
			$subsectionsCount = '<span 
										class="js-subsections-count">
										<span class="js-children-count wdpro-rounded-count">'.$subsectionsCount.'</span></span>';
		}
		else {
			$subsectionsCount = '';
		}

		// Список подразделов
		$actions['wdpro_subsections'] =
			'<a href="edit.php?post_type=' .
			static::getType()
			. '&sectionId=' .
			$this->id() . '" class="js-subsections"><span 
									class="fa fa-folder"></span> Подразделы'.$subsectionsCount.'</a>';

		return $actions;
	}


	/**
	 * Возвращает количество дочерних элементов
	 *
	 * @param \Wdpro\Console\Roll|string $childsRollOrType Дочерний список или тип дочерних
	 * @return null|number
	 */
	public function getChildsCount($childsRollOrType)
	{
		if (is_object($childsRollOrType))
		return $childsRollOrType->getCountForSection($this);
		
		// Пока что это сделано только для страниц
		return 0;
	}


	/**
	 * Список дочерних объектов
	 * 
	 * <pre>
	 * return array(
	 *  array(
	 *      'roll'=>\App\Good\GoodConsoleRoll::class,
	 *      'label'=>'Товары',
	 *
	 *      // https://developer.wordpress.org/resource/dashicons/#products
	 *      // https://fontawesome.com/icons
	 *      'icon'=>'dashicons-products',
	 *  )
	 * );
	 * </pre>
	 * 
	 * @return array|void
	 */
	protected static function childs()
	{

	}


	/**
	 * Установка класса списка
	 * 
	 * @param \Wdpro\BaseRoll $class
	 */
	public static function setConsoleRollClass($class)
	{
		global $consoleRollClasses;
		$consoleRollClasses[get_called_class()] = $class;
		//static::$consoleRollClass = $class;
	}


	/**
	 * Возвращает класс списка
	 * 
	 * @return \Wdpro\BaseRoll
	 */
	public static function getConsoleRollClass()
	{
		global $consoleRollClasses;
		return $consoleRollClasses[get_called_class()];
		//return static::$consoleRollClass;
	}


	/**
	 * Возвращает список этой сущности для админки
	 * 
	 * @return \Wdpro\Console\PagesRoll|void
	 * @throws \Exception
	 */
	public static function getConsoleRoll()
	{
		if ($class = static::getConsoleRollClass())
		{
			$roll = wdpro_object($class);
			$roll->init();
			return $roll;
		}
	}


	/**
	 * Установка типа списка дочерних элементов
	 * 
	 * @param string $type
	 */
	public function setChildsType($type)
	{
		$this->childsType = $type;
	}


	/**
	 * Обновление порядкового номера страницы
	 */
	public function getMenuOrder($menuOrder)
	{
		//$menu_order = get_post_field('menu_order', $this->id());

		if ($menuOrder === 0 || $menuOrder === '0')
		{
			if ($row = \Wdpro\Page\SqlTable::getRow(
				array(
					'WHERE post_type=%s 
					AND post_status!="auto-draft" AND post_status!="trash" 
					AND post_parent=%d
					ORDER BY menu_order DESC LIMIT 1', 
					$this->getType(),
					get_post_field('post_parent', $this->id())
				),
				'id, menu_order'
			))
			{
				$menuOrder = ceil(($row['menu_order'] + 10)/10)*10;
			}

			else
			{
				$menuOrder = 10;
			}

			/*wp_update_post(array(
				static::idField()=>$this->id(),
				'menu_order'=>$menuOrder
			));*/
		}

		return $menuOrder;
	}


	/**
	 * Возвращает данные для шаблона админки
	 * 
	 * @return array
	 */
	public function getConsoleTemplateData()
	{
		return $this->getData();
	}


	/**
	 * Удаление сущности
	 *
	 * @throws \Exception
	 */
	public function remove()
	{
		if ($id = $this->id())
		{
			$table = static::getSqlTable();
			
			// Удаление дочерних элементов
			if ($table::isColl('post_parent')) {
				if ($selChilds = $table::select([
					'WHERE post_parent=%d',
					[$id]
				])) {
					foreach ($selChilds as $childRow) {
						$child = wdpro_object(get_class($this), $childRow);
						/** @var \Wdpro\BaseEntity $child */
						$child->remove();
					}
				}
			}


			$table->delete(array(static::idField()=>$id), array('%d'));
			
			$this->_removed = true;
			
			$this->onChange();
			$this->trigger('change', $this->getData());
			
			$this->onRemove();
			$this->trigger('remove', $this->id());


			// Раньше это было выше в этом методе
			// Переместил это вниз, чтобы при onChange еще был доступ к этому объектв
			wdpro_object_remove_from_cache($this);
		}
	}


	public function addToObjectsCache() {
		global $_wdproObjects;

	}


	public function removeFromObjectsCache() {

	}


	/**
	 * True, когда объект удален из базы данных
	 * 
	 * Например, это можно использовать в методе onChange, который срабатывает после 
	 * какого-либо изменения.
	 * Тогда в этом методе можно проверить, удален этот объект или просто изменен, 
	 * и выполнить в зависимости от этого соответствующий код.
	 * 
	 * @return bool
	 */
	public function removed() {
		
		return $this->_removed;
	}


	/**
	 * Преобразует данные таки образо, чтобы post_title, post_content и другие
	 * переводимые на языки поля были равны текущему языку
	 */
	public function dataToCurrentLang() {
		$table = static::sqlTable();

		foreach ($table::getLangsFields() as $coll) {
			$this->data[$coll] = $this->data[$coll.\Wdpro\Lang\Data::getCurrentSuffix()];
		}
	}


	/**
	* Возвращает true, когда это новая сущность и ее нет в базе
	*/
	public function isNew() {

		return !isset($this->data['_from_db']) || !$this->data['_from_db'];


		return $this->id();
	}

}



class EntityException extends \Exception
{
	
}