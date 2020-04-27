<?php
namespace Wdpro\Form\Elements;

class Base
{
	protected $params;
	protected $formI;

	/**
	 * @var \Wdpro\Form\Form
	 */
	protected $form;
	protected static $classInited = false;


	/**
	 * @param array $params Параметры
	 */
	public function __construct($params)
	{
		$this->params = $params;
	}


	/**
	 * Возвращает параметры поля
	 *
	 * @return array
	 */
	public function getParams()
	{
		$params = $this->params;
		unset($params['formName']);

		return $params;

		//return $this->params;
	}


	/**
	 * Обновляет параметры поля
	 * 
	 * @param array $newParams Новые параметры
	 */
	public function mergeParams($newParams) {
		$this->params = wdpro_extend($this->params, $newParams);
		
		if ($this->form) {
			$this->form->_mergeElementParamsByI($this->formI, $newParams);
		}
	}


	/**
	 * Возвращает данные для сохранения в базе
	 *
	 * @return mixed
	 */
	public function getSaveValue()
	{
		if (isset($this->value))
			return $this->value;
	}


	/**
	 * Дополнительная обработка данных запущенной формы этим полем
	 *
	 * @param array $formData Данные запущенной формы
	 * @returns mixed
	 */
	public function getDataFromSubmit($formData=null)
	{
		if (!$formData)
			$formData = $this->form->getData(null, true);

		$data = $this->getValueFromDataByName($formData);
		
		if (is_string($data)) $data = stripslashes($data);
		
		return $data;
	}


	/**
	 * Возвращает значение из данных по имени, заданному в параметрах
	 * 
	 * Имя может быть в виде массива имен, тогда данные будут браться из общих данных 
	 * следующим образом
	 * $data[$name1][$name2][$nameN]
	 * 
	 * @param $data
	 * @return mixed
	 */
	public function getValueFromDataByName($data) {
		
		$names = $this->getName();
		if (!is_array($names)) $names = [$names];
		
		foreach($names as $name) {
			
			if (isset($data[$name])) {
				$data = $data[$name];
			}
			
			else {
				$data = null;
			}
		}
		
		if ($data !== null)
		return $data;
	}


	/**
	 * Возвращает имя поля
	 *
	 * @return string
	 */
	public function getName()
	{
		if (isset($this->params['name']))
		{
			return $this->params['name'];
		}
	}


	/**
	 * Проверка поля на правильное заполнение
	 *
	 * @param $formData
	 * @return bool
	 */
	public function valid($formData)
	{
		if (isset($this->params['required']) && $this->params['required']
		|| isset($this->params['*']) && $this->params['*'])
		{
			if (!$this->getDataFromSubmit($formData))
			{
				$this->addError('Данное поле обязательно для заполнения');
				return false;
			}
		}

		return true;
	}


	/**
	 * Установка ошибки для отображения ее при следующем отображении формы
	 *
	 * @param string $errorText Текст ошибки
	 */
	public function addError($errorText)
	{
		$key = $this->geterrorOptionKey();

		$text = get_option($key);
		if ($text)
		{
			$text .= '<BR>';
		}
		$text .= $errorText;
		
		update_option($key, $text);
	}


	/**
	 * Возвращает последнюю ошибку
	 *
	 * @return mixed|void
	 */
	public function getError()
	{
		$key = $this->geterrorOptionKey();

		$error = get_option($key);

		if ($error)
		{
			delete_option($key);
			return htmlspecialchars($error);
		}
	}


	protected function geterrorOptionKey()
	{
		$key = '';

		if ($this->form)
		{
			$key .= 'F:'.$this->form->getName();
		}

		$name = $this->getName();
		if (is_array($name)) {
			$name = implode('_', $name);
		}
		$key .= '|I:'.$name;

		$key .= '|S:'.session_id();

		return $key;
	}


	/**
	 * Возвращает номер поля ф орме
	 *
	 * @return int
	 */
	public function getFormI() {

		if (!$this->formI)
		{
			return '0';
		}

		return $this->formI;
	}


	/**
	 * Установки формы
	 *
	 * @param WdproForm $form Имя формы
	 */
	public function setForm($form)
	{
		$this->form = $form;
	}


	/**
	 * Установка номера поля в форме
	 *
	 * @param int $formI
	 */
	public function setFormI( $formI ) {

		$this->formI = $formI;
	}


	/**
	 * Возвращает описание поля
	 * 
	 * @return string
	 */
	public function getLabel() {

		$label = null;
		
		if (isset($this->params['label'])) return $this->params['label'];

		if (isset($this->params['top'])) $label = $this->params['top'];
		if (isset($this->params['left'])) $label = $this->params['left'];
		if (isset($this->params['center'])) $label = $this->params['center'];
		if (isset($this->params['right'])) $label = $this->params['right'];
		
		if ($label) {
			if (is_array($label)) return $label['text'];
			return $label;
		}
	}


	/**
	 * Возвращает значение поля для отправки
	 * 
	 * @return string
	 */
	public function getSendValue() {
		
		return $this->getSaveValue();
	}


	/**
	 * Возвращает html код для вставки в письмо
	 *
	 * @param string $saveValue Значение, которое было предназначено для сохранения в базе
	 * @return string
	 */
	public function getSendTextHtml($saveValue) {
		
		if ($label = $this->getLabel()) {
			return '<p><strong>'.$label.':</strong> '.$saveValue;
		}
	}


	/**
	 * Удаляет файлы (этот метод наследуется в Image.php и File.php
	 */
	public function removeFiles() {

	}
}
