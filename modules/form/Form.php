<?php
namespace Wdpro\Form;

use Wdpro\Exception;


/**
 * Форма
 */
class Form
{
	protected $params = [];
	/** @var \Wdpro\Form\Elements\Base */
	protected $elements = [];
	protected $elementsByName = [];
	protected $elementI = 0;
	protected $groups = [];
	protected $saveErrorsToOptions = false;
	protected static $defaultMethod = 'POST';

	const CKEDITOR = 'ckeditor';
	const CKEDITOR_SMALL = 'ckeditorSmall';
	const CHECK = 'check';
	const CHECKS = 'checks';
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
	const MENU_ORDER = 'menuOrder';
	const MENU_ORDER_TOP = 'menuOrderTop';
	const RECAPTCHA3 = 'recaptcha3';
	const PRIVACY = 'privacy';

	static $n = 0;

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
		'checks'=>'\Wdpro\Form\Elements\Checks',
		'checkbox'=>'\Wdpro\Form\Elements\Check',
		'checkboxes'=>'\Wdpro\Form\Elements\Checks',
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
		'menuOrder'=>'\Wdpro\Form\Elements\MenuOrder',
		'menuOrderTop'=>'\Wdpro\Form\Elements\MenuOrderTop',
		'recaptcha3'=>'\Wdpro\Form\Elements\Recaptcha3',
		'privacy'=>'\Wdpro\Form\Elements\Privacy',
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
			'method'=>static::$defaultMethod,
			'elements'=>[],
			'attributes'=>[],
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
	 * Добавляет заголовок (Актуально для админки, на сайте лучше не добавлять)
	 *
	 * @param string $headerText Текст заголовка
	 * @param bool $marginLeft Сделать отступ слева
	 * @throws Exception
	 */
	public function addHeader($headerText, $marginLeft = false) {
		$this->add([
			'type'=>static::HTML,
			'html'=>'<h2>'.$headerText.'</h2>',
			'left'=>$marginLeft,
			'autoWidth'=>false,
		]);
	}


	/**
	 * Добавляет поле в форму
	 *
	 * @param array|string $params Параметры
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
					$element->setForm($this);
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
								\Wdpro\Lang\Data::getSuffix($lang),
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
	 * Удаление элементов
	 *
	 * Например, для того, чтобы убрать поля формы после отправки, а оставить только сообщение об успешной отправке
	 */
	public function removeElements() {
		$this->groups = [];
	}


	/**
	 * Добавление аттрибута в форму
	 *
	 * @param string $name Имя атррибута
	 * @param string|number|mixed $value Значение
	 */
	public function addAttribute($name, $value) {
		$this->params['attributes'][$name] = $value;
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
		static::$n ++;
		$id = 'formData'.time().'_'.static::$n;

		return '
<script>
window.'.$id.' = '.($this->getJson()).';
</script>
<div class="js-wdpro-form" data-id="'.$id.'"></div>';
	}


	/**
	* Возвращает Json строку с данными формы
	*
	* Чтобы потом на странице с помощью js превратить данные в саму форму
	*/
	public function getJson() {

		$params = $this->getParams();
		unset($params['entity']);

		return json_encode(
			$params,
			JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_HEX_QUOT
		);
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
	* Переключает в режим ajax
	*
	* @param boolean|string|array $ajaxEnableOrAjaxUrl
	*/
	public function setAjax($ajaxEnableOrAjaxUrl=true) {

		if (is_array($ajaxEnableOrAjaxUrl)) {
			$ajaxEnableOrAjaxUrl = wdpro_ajax_url($ajaxEnableOrAjaxUrl);
		}

		// Url
		if (is_string($ajaxEnableOrAjaxUrl)) {
			$this->params['ajax'] = true;
			$this->setAction($ajaxEnableOrAjaxUrl);
		}

		// Просто включение
		else {
			$this->params['ajax'] = $ajaxEnableOrAjaxUrl;
		}
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
	 * @param callback|null $noSubmitCallback каллбэк, который срабатывает, если форма не была отправлена
	 */
	public function onSubmit($validCallback, $noSubmitCallback=null)
	{
		$data = $this->getData($this->getSubmitData());

		if ($data)
		{
			if ($this->valid())
			{
				$validCallback($data);
			}
		}
		else {
			if ($noSubmitCallback) {
				$noSubmitCallback();
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

		if (!empty($_GET['fromPostId'])) {
			$post = wdpro_get_post_by_id($_GET['fromPostId']);
			$startHtml .= '<p>Страница: <a href="'.home_url().'/'.$post->getUri().'" target="_blank">'.$post->getTitle().'</a></p>';
		}

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


	public function getSubmitProcessedData() {

	}


	/**
	 * Возвращает обработанные данные
	 *
	 * @param array $data Не стандартные данные (например, когда форма не отправлялась, а просто надо обработать какие-то данные через форму)
	 * @return array|null
	 */
	public function getData($data=null, $useSettedDataIfNoSubmit = false)
	{
		if ($data === null)
			$data = $this->getSubmitData();

		// Это нужно, чтобы при удалении элементов удалялись файлы (например, картинки)
		// Чтобы поля могли получить данные формы без отправки и удалить файлы
		// Да и в принципе чтобы поля знали введенные в форму данные без отправки формы
		if (!$data && $useSettedDataIfNoSubmit && isset($this->params['data']))
			$data = $this->params['data'];

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

					if (isset($data[$rootName]) || isset($value)) {

						if (is_array($value)
							&& isset($fixedData[$rootName])
							&& is_array($fixedData[$rootName])) {

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
		if (!empty($this->params['name']))
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


	/**
	 * Есть ли у формы картинки с водяными знаками
	 *
	 * Это нужно для того, чтобы определять, перерисовывать водяные знаки для этой формы
	 * во время перерисовки всех знаков или нет.
	 *
	 * @return bool
	 */
	public function haveWatermark() {
		foreach($this->elements as $element) {
			if (method_exists($element, 'haveWatermark')) {
				if ($element->haveWatermark()) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Возвращает элементы (поля картинок), у которых есть водяные знаки и которые могут
	 * быть перерисованы
	 *
	 * @return \Wdpro\Form\Elements\Image[]
	 */
	public function getWatermarkRedrawingElements() {
		$elements = [];

		foreach($this->elements as $element) {
			if (method_exists($element, 'canRedrawWatermark') && $element->canRedrawWatermark()) {
				$elements[] = $element;
			}
		}

		if (count($elements)) {
			return $elements;
		}
	}


	/**
	 * Устанавливаем метод отправки
	 *
	 * @param string $method Метод (POST, GET)
	 */
	public function setMethod($method) {
		$this->params['method'] = $method;
	}


	/**
	 * Возвращает элемент (сущность), который редактируется данной формой
	 *
	 * @return \Wdpro\BaseEntity
	 */
	public function getEntity() {
		return $this->params['entity'];
	}


	public function removeFiles() {
		$this->eachElements(function ($element) {

			/** @var \Wdpro\Form\Elements\Base|\Wdpro\Form\Elements\File|\Wdpro\Form\Elements\Image */

			$element->removeFiles();
		});
	}


	/**
	 * Добавляет в форму галочку для согласия с политикой конфиденциальности
	 *
	 * @throws \Wdpro\Exception
	 */
	public function addPrivacy() {

		$this->add(static::PRIVACY);

	}
}
