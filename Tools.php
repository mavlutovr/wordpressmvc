<?php
namespace Wdpro;


/**
 * Запоминалка статических данных для разных данных
 * 
 * Обычные статичные данные остаются одинаковыми в разных классах при наследовании
 * И чтобы они становились разными, надо их переопределять
 * Чтобы не переопределять, подключается вот эта штука
 * 
 * @package Wdpro
 */
trait Tools
{
	protected static $staticsList = array();
	protected $_key;
	protected $events;
	protected static $eventsStatic;


	/**
	 * Запоминает данные
	 * 
	 * @param string $name Название данных
	 * @param mixed $value Значение
	 */
	public static function setStatic($name, $value) {

		$key = get_called_class();
		
		if (!isset(static::$staticsList[$key])) static::$staticsList[$key] = array();

		static::$staticsList[$key][$name] = $value;
	}


	/**
	 * Возвращает данные
	 * 
	 * @param string $name Название данных
	 * @return mixed 
	 */
	public static function getStatic($name) {
		
		$key = get_called_class();
		
		if (isset(static::$staticsList[$key][$name]))
		{
			return static::$staticsList[$key][$name];
		}
	}


	/**
	 * Проверяет наличие данных
	 * 
	 * @param string $name Имя данных
	 * @return bool
	 */
	public static function issetStatic($name)
	{
		$key = get_called_class();
		return isset(static::$staticsList[$key][$name]);
	}


	/**
	 * Возвращает пространство имен того класса, в котором запускается этот метод
	 *
	 * Если создать класс наследуемый от этого класса и вызвать этот метод в том классе,
	 * то возвратиться пространство имен того класса наследника
	 *
	 * @return mixed
	 */
	public static function getNamespace()
	{
		if (!static::issetStatic('namespace'))
		{
			$reflector = new \ReflectionClass(get_called_class());
			static::setStatic('namespace', $reflector->getNamespaceName());
		}

		return '\\'.static::getStatic('namespace');
	}


	/**
	 * Возвращает объект таблицы сущности
	 * 
	 * @return \Wdpro\BaseSqlTable
	 * @throws EntityException
	 */
	public static function sqlTable()
	{
		if ($tableClass = static::getSqlTableClass())
		{
			//return $tableClass;
			return wdpro_object($tableClass);
		}
		
		else
		{
			throw new EntityException(
				'У сущности '.get_called_class().' не указано класс таблицы в методе 
				getSqlTableClass()'
			);
		}
	}

	
	/**
	 * Дополнительная таблица
	 *
	 * @return \Wdpro\BaseSqlTable
	 */
	public static function getSqlTableClass()
	{
		return static::getNamespace().'\\SqlTable';
	}



	/**
	 * Возвращает имя класса контроллера
	 * 
	 * @return \Wdpro\BaseController
	 */
	public static function getController() {
		
		$namespace = static::getNamespace();
		
		return $namespace.'\\Controller';
	}


	/**
	 * Возвращает ключ объекта (строку, по которой можно получить этот объект)
	 *
	 * @param null|string|array $additionalKeyData Дополнительные данные, добавляемые в ключ
	 *    Это может пригодиться для того, чтобы сохранить в ключе состояние объекта или его модификацию.
	 *    Например, когда у товара есть цвета, можно добавить в этот ключ цвет и положить в корзину товар определенного цвета.
	 *
	 * @return string
	 */
	public function getKey($additionalKeyData=null) {

		$keyData = $this->getKeyArray($additionalKeyData);

		$key = '';
		foreach ($keyData['object'] as $k => $v) {
			if ($key) $key .= ',';
			$key .= $k.':'.$v;
		}

		return $key;
	}


	/**
	 * Возвращает ключ объекта (объект с информацией, по которой можно получить этот объект)
	 *
	 * @param null|string|array $additionalKeyData Дополнительные данные, добавляемые в ключ
	 *    Это может пригодиться для того, чтобы сохранить в ключе состояние объекта или его модификацию.
	 *    Например, когда у товара есть цвета, можно добавить в этот ключ цвет и положить в корзину товар определенного цвета.
	 *
	 * @return array
	 */
	public function getKeyArray($additionalKeyData=null) {
		$keyData = [
			'name'=>wdpro_get_class($this),
			'id'=>$this->id(),
		];

		if (isset($this->_key)) {
			$settedKey = $this->_key;
			$settedKey = wdpro_key_parse($settedKey);
			foreach ($settedKey['object'] as $k => $v) {
				$keyData[$k] = $v;
			}
		}

		if (is_array($additionalKeyData)) {
			foreach ($additionalKeyData as $k => $v) {
				$keyData[$k] = $v;
			}
		}

		else if (is_string($additionalKeyData)) {
			$arr1 = explode(',', $additionalKeyData);
			foreach ($arr1 as $item) {
				$arr2 = explode(':', $item);
				$keyData[$arr2[0]] = $arr2[1];
			}
		}

		return [ 'object' => $keyData ];
	}


	/**
	 * Установка ключа объекта, который приводит объект к надлежащему виду
	 *
	 * Не надо удалять, т.к. сюда отправляются данные из функции keyObj()
	 *
	 * @param string|array $key Ключ
	 */
	public function setKey($key) {

		// Преобразуем ключ в массив
		$key = wdpro_key_parse($key);

		// Запоминаем ключ
		$this->_key = $key;
	}


	/**
	 * Возвращает значение ключа или задает, если указано значение
	 *
	 * @param string $key ключ ключа
	 * @param string $value Значение
	 * @return mixed
	 */
	public function keyValue($key, $value='---noValue---') {

		// Возвращает значение ключа
		if ($value === '---noValue---') {
			if (isset($this->_key['object'][$key]) && $this->_key['object'][$key])
				return wdpro_key_unescape($this->_key['object'][$key]);
		}

		// Устанавливает значение внутри ключа
		else {
			$this->setKey(
				wdpro_key_add_values($this->getKey(), [
					$key=>$value,
				])
			);
		}
	}


	/**
	 * Прослушка события
	 * 
	 * @param string $eventName Имя события
	 * @param callback $callback Каллбэк, запускающийся при событии
	 */
	public function on($eventName, $callback) {
		
		if (!isset($this->events[$eventName]))
			$this->events[$eventName] = [];
		
		$this->events[$eventName][] = $callback;
	}


	/**
	 * Запуск события
	 * 
	 * @param string $eventName Имя события
	 * @param null|array $data Данные, отправляемые в каллбэки, которые ожидают событие
	 */
	public function trigger($eventName, $data=null) {
		
		if (isset($this->events[$eventName])) {
			
			foreach($this->events[$eventName] as $callback) {
				/** @var callback $callback */
				$callback($data);
			}
		}
	}



	/**
	 * Прослушка события
	 *
	 * @param string $eventName Имя события
	 * @param callback $callback Каллбэк, запускающийся при событии
	 */
	public static function onStatic($eventName, $callback) {
		
		$eventNameFull = get_called_class().':'.$eventName;
		if (!isset(static::$eventsStatic[$eventNameFull]))
			static::$eventsStatic[$eventNameFull] = [];

		static::$eventsStatic[$eventNameFull][] = $callback;
	}


	/**
	 * Запуск события
	 *
	 * @param string $eventName Имя события
	 * @param null|array|mixed $data Данные, отправляемые в каллбэки, которые ожидают 
	 * событие
	 */
	public static function triggerStatic($eventName, $data) {
		$eventNameFull = get_called_class().':'.$eventName;
		if (isset(static::$eventsStatic[$eventNameFull])) {

			foreach(static::$eventsStatic[$eventNameFull] as $callback) {
				/** @var callback $callback */
				$callback($data);
			}
		}
	}

}