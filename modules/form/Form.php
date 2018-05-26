<?php
namespace Wdpro\Form;

use Wdpro\Exception;


/**
 * Форма
 */
class Form
{
	protected $params = [];
	protected $elements = [];
	protected $elementsByName = [];
	protected $elementI = 0;
	protected $groups = [];
	protected $saveErrorsToOptions = false;
	
	const CKEDITOR = 'ckeditor';
	const CKEDITOR_SMALL = 'ckeditorSmall';
	const CHECK = 'check';
	const SORTING = 'sorting';
	const SORTING_TOP = 'sortingTop';
	const TEXT = 'text';
	const PASS = 'pass';
	const HIDDEN = 'hidden';
	const FILE = 'file';
	const IMAGE = 'image';
	const BUTTON = 'button';
	const SUBMIT = 'submit';
	const SUBMIT_SAVE = 'submitSave';
	const SELECT = 'select';
	const HTML = 'html';
	const SPINNER = 'spinner';
	const EMAIL = 'email';
	const DATE = 'date';


	/**
	 * Список классов полей формы
	 * 
	 * @var array
	 */
	protected static $elementsClasses = array(
		'string'=>'\Wdpro\Form\Elements\StringField',
		'sorting'=>'\Wdpro\Form\Elements\Sorting',
		'sortingTop'=>'\Wdpro\Form\Elements\SortingTop',
		'text'=>'\Wdpro\Form\Elements\Text',
		'check'=>'\Wdpro\Form\Elements\Check',
		'checkbox'=>'\Wdpro\Form\Elements\Check',
		'pass'=>'\Wdpro\Form\Elements\Pass',
		'hidden'=>'\Wdpro\Form\Elements\Hidden',
		'file'=>'\Wdpro\Form\Elements\File',
		'image'=>'\Wdpro\Form\Elements\Image',
		'ckeditor'=>'\Wdpro\Form\Elements\Ckeditor\Ckeditor',
		'ckeditorSmall'=>'\Wdpro\Form\Elements\Ckeditor\CkeditorSmall',
		'button'=>'\Wdpro\Form\Elements\Button',
		'submit'=>'\Wdpro\Form\Elements\Submit',
		'submitSave'=>'\Wdpro\Form\Elements\SubmitSave',
		'select'=>'\Wdpro\Form\Elements\Select',
		'html'=>'\Wdpro\Form\Elements\Html',
		'spinner'=>'\Wdpro\Form\Elements\Spinner',
		'email'=>'\Wdpro\Form\Elements\Email',
		'date'=>'\Wdpro\Form\Elements\Date',
	);


	/**
	 * @param null|array|string $params Параметры или имя формы
	 */
	public function __construct($params=null)
	{
		if (is_string($params))
		{
			$params = array(
				'name'=>$params,
			);
		}
		
		$this->params = wdpro_extend(array(
			'action'=>'',
			'method'=>'POST',
			'elements'=>array(),
		), $params);
		
		foreach($this->params['elements'] as $elementParams)
		{
			$this->add($elementParams);
		}
		
		$this->initFields();
	}


	/**
	 * Установка параметра
	 * 
	 * @param string $paramName Имя параметра
	 * @param mixed $paramValue Значение параметра
	 */
	public function setParam($paramName, $paramValue) {
		
		$this->params[$paramName] = $paramValue;
	}


	/**
	 * Установка css класса
	 * 
	 * @param string $cssClassName Css класс
	 */
	public function setClass($cssClassName) {
		
		$this->setParam('class', $cssClassName);
	}


	/**
	 * Инициализация полей
	 * 
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields()
	{
		
	}


	/**
	 * Добавление элемента по параметрам
	 *
	 * @param array $params Параметры элемента
	 * @return Elements\Base
	 * @throws Exception
	 */
	protected function addElementByParams($params)
	{
		$type = isset($params['type']) ? $params['type'] : 'string';
		
		if (static::$elementsClasses[$type])
		{
			/** @var \Wdpro\Form\Elements\Base $element */
			$element = new static::$elementsClasses[$type]($params);

			$this->elements[$this->elementI] = $element;
			
			return $element;
		}
		else
		{
			throw new Exception('Не указан класс для поля для типа '.$type);
		}
	}


	/**
	 * Добавляет поле в форму
	 *
	 * @param array $params Параметры
	 */
	public function add($params)
	{
		$attrs = func_get_args();
		$group = array();

		foreach ($attrs as $params) {
			if (is_string($params)) $params = array('type' => $params);

			// Добавление одного поля
			$add = function ($params) use (&$group) {
				$this->params['elements'][$this->elementI] = $params;

				if ($element = $this->addElementByParams($params)) {
					$group[] = $element;
					$element->setFormI($this->elementI);
				}

				if (isset($params['name']) && $params['name']) {
					$this->elementsByName[$this->getNameKey($params['name'])] = $element;
				}

				$this->elementI++;
			};


			// Языковые дубли
			if (isset($params['name']) && strstr($params['name'], '[lang]')) {
				if ($langs = \Wdpro\Lang\Data::getUris()) {
					foreach ($langs as $lang) {

						if (!isset($params['langs_skip'])
						    || !in_array($lang, $params['langs_skip']))
						{
							$params2 = $params;
							$params2['name'] = str_replace(
								'[lang]',
								\Wdpro\Lang\Data::getPrefix($lang),
								$params2['name']
							);

							$params2['current_lang'] = $lang;
							$params2['lang'] = true;
							$params2['icon'] = \Wdpro\Lang\Data::getFlagSrc($lang);

							$add($params2);
						}
					}
				}
				else {
					throw new Exception('В форме '.get_class($this).' добавлены языковые поля. Однако, языки не настроены.');
				}
			}

			else {
				$add($params);
			}
		}

		$this->groups[] = $group;
	}





	/**
	 * Возвращает ключ имени для массива элементов
	 *
	 * @param $name string|array Имя
	 * @return array|string
	 */
	protected function getNameKey($name) {

		if (is_array($name)) {
			return wdpro_json_encode($name);
		}

		return $name;
	}


	/**
	 * Возвращает объект элемента по имени
	 * 
	 * @param string $elementName Имя элемента
	 * @return Elements\Base
	 */
	public function getElementByName($elementName) {

		$key = $this->getNameKey($elementName);
		if (isset($this->elementsByName[$key])) {
			return $this->elementsByName[$key];
		}
	}


	/**
	 * Технический метод для использования элементами формы
	 * 
	 * Обновляет инфу в самой форме при обновлении ее в элементе
	 * 
	 * @param $i Номер элемента в списке элементов
	 * @param $elementParams
	 */
	public function _mergeElementParamsByI($i, $elementParams) {
		if (isset($this->params['elements'][$i])) {
			$this->params['elements'][$i] = wdpro_extend(
				$this->params['elements'][$i],
				$elementParams
			);
		}
	}


	/**
	 * Добавляет Html блок
	 * 
	 * @param string $html Html блок
	 */
	public function addHtml($html) {
		
		$this->add([
			'type'=>'html',
			'html'=>$html,
		]);
	}


	/**
	 * Возвращает html код формы
	 *
	 * @return string
	 */
	public function getHtml()
	{
		return '<div 
			class="js-wdpro-form"><div class="js-params g-hid" 
			style="display: none">
'
		.htmlspecialchars(json_encode(
			$this->getParams(), 
			JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_QUOT
		))
		.'</div></div>';
	}


	/**
	 * Возвращает параметры формы
	 */
	public function getParams()
	{
		$params = wdpro_extend(array(), $this->params);

		unset($params['elements']);

		if (false)
		{
			$this->eachElements(function ($element) use (&$params)
			{
				/** @var \Wdpro\Form\Elements\Base $element */
				$elementParams = $element->getParams();
				if ($error = $element->getError())
				{
					$elementParams['error'] = $error;
				}
				else
				{
					//$elementParams['error'] = 'test';
				}
				$params['elements'][$element->getFormI()] = $elementParams;
			});
		}
		
		$this->eachGroups(function ($elements) use (&$params)
		{
			$groupParams = array();

			/** @var \Wdpro\Form\Elements\Base $element */
			foreach($elements as $element)
			{
				$elementParams = $element->getParams();
				if ($error = $element->getError())
				{
					$elementParams['error'] = $error;
				}
				else
				{
					//$elementParams['error'] = 'test';
				}
				$groupParams[] = $elementParams;
			}
			
			$params['elements'][] = $groupParams;
		});
		
		return $params;
	}


	/**
	 * Замена параметров
	 *
	 * @param array $params Массив новых параметров
	 */
	public function mergeParams($params)
	{
		$this->params = wdpro_extend($this->params, $params);
	}


	/**
	 * Установка адреса, куда будут отправлены данные формы
	 *
	 * @param string $action Адрес скрипта, куда будут отправлены данные формы
	 */
	public function setAction($action) {
		$this->params['action'] = $action;
	}


	/**
	 * Перебор параметров элеменов
	 *
	 * @param callback $callback Каллбэк, принимающий параметры каждого элемента
	 */
	public function eachElementsParams($callback)
	{
		/** @var \Wdpro\Form\Elements\Base $element */
		foreach($this->elements as $element)
		{
			$callback($element->getParams());
		}
	}


	/**
	 * Перебор элеменов
	 *
	 * @param callback $callback Каллбэк, принимающий параметры каждого элемента
	 */
	public function eachElements($callback)
	{
		/** @var \Wdpro\Form\Elements\Base $element */
		foreach($this->elements as $element)
		{
			$callback($element);
		}
	}


	/**
	 * Перебор групп
	 * 
	 * @param callback $callback Каллбэк, принимающий группу
	 */
	public function eachGroups($callback)
	{
		/** @var \Wdpro\Form\Elements\Base[] $element */
		foreach($this->groups as $group)
		{
			$callback($group);
		}
	}


	/**
	 * Установка данных полей формы
	 *
	 * @param array $data Данные
	 * @return $this
	 */
	public function setData($data)
	{
		$this->params['data'] = $data;
		
		return $this;
	}


	/**
	 * Запускает каллбэк при получении данных из формы
	 *
	 * @param callback $validCallback каллбэк, получающий данные, которые были запущены формой
	 */
	public function onSubmit($validCallback)
	{
		$data = $this->getData();

		if ($data)
		{
			if ($this->valid())
			{
				$validCallback($data);
			}
		}
	}


	/**
	 * Отправка формы администраторам
	 *
	 * @param string $subject Тема письма
	 * @param string $startHtml Html над формой
	 */
	public function sendToAdmins($subject, $startHtml='') {

		$startHtml = '';
		
		$data = $this->getSubmitData();
		
		$this->eachElements(function ($element) use (&$startHtml, &$data) {
			
			/** @var \Wdpro\Form\Elements\Base $element */
			
			if ($name = $element->getName()) {
				$startHtml .= $element->getSendTextHtml($data[$name]);
			}
		});
		
		\Wdpro\AdminNotice\Controller::sendMessageHtml($subject, $startHtml);
	}


	/**
	 * Проверка на правильную заполненность поля
	 * 
	 * @return bool
	 */
	public function valid()
	{
		$data = $this->getSubmitData();

		$valid = true;

		/** @var \Wdpro\Form\Elements\Base $element */
		foreach($this->elements as $element)
		{
			$name = $element->getName();
			if ($name)
			{
				if (!$element->valid($data))
				{
					$valid = false;
					
					/*$this->params['errors'][$element->getName()] = 
						$element->getError();*/
				}
			}
		}

		return $valid;
	}


	/**
	 * Возвращает необработанные данные, которые были отправлены с сайта этой формой
	 *
	 * @return null|array
	 */
	public function getSubmitData()
	{
		$data = strtolower($this->params['method']) == 'post' ? $_POST : $_GET;

		if (isset($this->params['name']) && $this->params['name'])
		{
			if (isset($data[$this->params['name']]))
			{
				return $data[$this->params['name']];
			}
		}

		return $data;
	}


	/**
	 * True, Если форма запущена
	 * 
	 * @return bool
	 */
	public function sended() {

		$data = strtolower($this->params['method']) == 'post' ? $_POST : $_GET;

		if (isset($this->params['name']) && $this->params['name'])
		{
			return isset($data[$this->params['name']]);
		}
	}


	/**
	 * True, если форма запущена и верно заполнена
	 * 
	 * @return bool
	 */
	public function sendedAndValid() {
		
		return $this->sended() && $this->valid();
	}


	/**
	 * Возвращает обработанные данные
	 * 
	 * @return array|null
	 */
	public function getData()
	{
		$data = $this->getSubmitData();
		
		if ($data)
		{
			$fixedData = array();

			/** @var \Wdpro\Form\Elements\Base $element */
			foreach($this->elements as $element)
			{
				$names = $element->getName();
				
				if ($names)
				{
					// Создаем место в массиве данных для этих данных
					// Это когда имя поля задано в виде массива ключей
					// Типа 'name'=>['key1', 'key2', 'keyN']
					// Тогда надо чтобы в общих данных это выглядело так:
					// $formData = [
					//  'key1'=>[
					//      'key2'=>[
					//          'keyN'=>$value
					//      ]
					//  ]
					//]
					if (!is_array($names)) $names = [$names];
					
					// Основной ключ
					$rootName = array_shift($names);

					// Получаем данные для сохранение
					// Здесь нужна проверка
					// А то на tridodo.ru в настройках getresponse данные обнулялись
					// после установки корректного API ключа, когда в _POST был только
					// ключ, и когда он был корректным, то форма начинала работать и
					// добавлялись другие поля и тогда форма выдавала пустые значения
					// по этим полям
					$value = $element->getDataFromSubmit($data);

					// Дополнительные ключи
					if (count($names)) {
						for($n=count($names)-1; $n >= 0; $n --) {
							$name = $names[$n];
							$value = [$name=>$value];
						}
					}

					if (isset($data[$rootName]) || $value) {

						if (is_array($value) && is_array($fixedData[$rootName])) {
							$fixedData[$rootName] = array_merge_recursive($fixedData[$rootName],
								$value);
						}
						else {
							$fixedData[$rootName] = $value;
						}
					}
				}
			}

			if (count($fixedData))
			{
				return $fixedData;
			}
		}
	}


	/**
	 * Установка имени формы
	 *
	 * @param string $name Имя формы
	 */
	public function setName($name)
	{
		$this->mergeParams(array(
			'name'=>$name,
		));
	}


	/**
	 * Установка Javascript имени формы
	 * 
	 * По этому имени форму можно будет получить через wdpro.forms.onForm()
	 *
	 * @param string $name Javascript Имя формы
	 */
	public function setJsName($name)
	{
		$this->mergeParams(array(
			'jsName'=>$name,
		));
	}


	/**
	 * Возвращает имя формы
	 * 
	 * @return null|string
	 */
	public function getName()
	{
		return $this->params['name'];
	}


	/**
	 * Убрать тэг самой формы
	 */
	public function removeFormTag()
	{
		$this->params['removeFormTag'] = true;
	}


	/**
	 * Добавляет в форму сообщение об ошибке
	 * 
	 * @param string $message Сообщение
	 */
	public function showErrorMessage($message) {
		
		$this->showMessage($message, ['err' => true]);
	}


	/**
	 * Добавляет в форму сообщение
	 * 
	 * @param string $message Текст сообщения
	 * @param null|array $params Параметры сообщения
	 */
	public function showMessage($message, $params=null) {
		
		if (!isset($this->params['messages']))
			$this->params['messages'] = [];
		
		$this->params['messages'][] = [
			'message'=>$message,
			'params'=>$params,
		];
	}
}






