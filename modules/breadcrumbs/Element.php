<?php
namespace Wdpro\Breadcrumbs;

class Element
{
	/** @var  array */
	protected $params;
	
	/**
	 * @param array $params Параметры
	 */
	public function __construct($params)
	{
		if (!is_array($params))
		{
			$params = array(
				'text'=>$params,
			);
		}
		$this->params = $params;
	}


	/**
	 * Возвращает данные
	 * 
	 * @return array
	 */
	public function getData()
	{
		return $this->params;
	}


	/**
	 * Проверяет абсолютный адрес на соответствие адресу данного элемента
	 * 
	 * @param string $url Сверяемый адрес
	 * @return bool
	 */
	public function isUrl($url)
	{
		if (isset($this->params['uri']) && !$this->params['uri'])
		{
			return home_url($this->params['uri']) == $url;
		}
	}

	/**
	 * Проверяет относительный адрес на соответствие адресу данного элемента
	 *
	 * @param string $uri Сверяемый адрес
	 * @return bool
	 */
	public function isUri($uri) {
		if (isset($this->params['uri']) && !$this->params['uri'])
		{
			echo $this->params['uri'].' == '.$uri.PHP_EOL.PHP_EOL;
			return $this->params['uri'] == $uri;
		}
	}


	/**
	 * Установка другого адреса
	 *
	 * @param string $uri Адрес ссылки
	 * @return $this
	 */
	public function setUri($uri) {
		
		$this->params['uri'] = $uri;
		
		return $this;
	}
}