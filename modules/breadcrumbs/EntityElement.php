<?php
namespace Wdpro\Breadcrumbs;

class EntityElement extends Element
{
	/** @var \Wdpro\BasePage */
	protected $entity;
	protected $childsType;
	protected $comment;


	/**
	 * Конструктор
	 * 
	 * @param \Wdpro\BasePage $entity страница
	 */
	public function __construct($entity)
	{
		$this->entity = $entity;
	}


	/**
	 * Возвращает данные
	 *
	 * @return array
	 */
	public function getData()
	{
		$text = $this->entity->getBreadcrumbsLabel();
		if ($this->comment)
		{
			$text .= ': '.$this->comment;
		}

		$url = $this->entity->getBreadcrumbsUrl($this->childsType);
		if ($url === '#') $url = '';

		return array(
			'text'=>$text,
			'uri'=>$url,
		);
	}


	/**
	 * Установка типа отображаемых дочерних элементов
	 * 
	 * У одного и того же раздела могут быть разные типы дочерних элементов. Например, 
	 * у одного и того же раздела каталога могут быть как товары, так и подразделы 
	 * каталога
	 * 
	 * @param string $type Тип отображаемых дочерних элементов
	 */
	public function setChildsType($type)
	{
		$this->childsType = $type;
	}


	/**
	 * Проверяет абсолютный адрес на соответствие адресу данного элемента
	 *
	 * @param string $url Сверяемый адрес
	 * @return bool
	 */
	public function isUrl($url)
	{
		return $this->entity->isUrl($url);
	}


	/**
	 * Проверяет относительный адрес на соответствие адресу данного элемента
	 *
	 * @param string $uri Сверяемый адрес
	 * @return bool
	 */
	public function isUri($uri) {
		return $this->entity->isUri($uri);
	}


	/**
	 * Установкаа комментария
	 *
	 * @param $comment
	 * @return $this
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
		
		return $this;
	}
}