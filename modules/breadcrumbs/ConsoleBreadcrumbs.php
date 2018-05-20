<?php
namespace Wdpro\Breadcrumbs;


class ConsoleBreadcrumbs extends Breadcrumbs {

	protected static $min = 2;
	protected $lastType;


	/**
	 * Создание структуры пути хлебных крошек
	 *
	 * @param \Wdpro\BasePage $entity Конечная (текущая) страница
	 * @param null|string $type Тип последней страницы
	 * Нужен, чтобы определять, список каких обхектов показывать у родительской страницы
	 */
	public function makeFrom($entity, $type=null)
	{
		$this->firstEntity = $entity;
		$element = new ConsoleEntityElement($entity);
		if (!$type)
		{
			$type = $this->lastType;
		}
		if ($type)
		{
			$element->setChildsType($type);
		}
		$this->lastType = $entity->getType();
		$this->prepend($element);

		$type = $entity::getType();
		$this->root['any'] = $entity->id();
		$this->root[$type] = $entity->id();

		if ($parent = $entity->getParent())
		{
			$this->parentByType[$type] = $parent->id();
			$this->makeFrom($parent);
		}
		else
		{
			$this->parentByType[$type] = 0;
			$this->afterMake();
		}
	}


	/**
	 * @param $element Element|array|string
	 * @return mixed
	 */
	protected function getElement($element)
	{
		if (is_string($element))
		{
			$element = array('text'=>$element);
		}
		if (is_array($element))
		{
			return new \Wdpro\Breadcrumbs\Element($element);
		}

		return $element;
	}



	/**
	 * Отображает хлебные крошки в админке
	 */
	public function display()
	{
		add_action(
			'all_admin_notices',
			function () use (&$params) {

				//echo($this->getConsoleBreadcrumbs());

				echo($this->getHtml());
			}
		);
	}


	/**
	 * Запускается по завершении $this->makeFrom()
	 */
	public function afterMake()
	{
		if ($this->firstEntity)
		{
			$firstRoll = $this->firstEntity->getConsoleRoll();
			$this->setFirstRoll($firstRoll);
			/*$this->prepend(new Element(array(
				'text'=>$firstRoll->getButtonText(),
				'uri'=>$firstRoll->getConsoleListUri(),
			)));*/
		}
	}


	/**
	 * Установка первой кнопкии по основному списку
	 * 
	 * @param \Wdpro\Console\PagesRoll $roll
	 */
	public function setFirstRoll($roll) {
		if ($roll) {
			$this->prepend(new Element(array(
				'text'=>$roll->getButtonText(),
				'uri'=>$roll->getConsoleListUri(),
			)));
		}
	}


	/**
	 * Возвращает html код
	 *
	 * @return string
	 */
	public function getHtml()
	{
		if ($template = $this->getTemplateData())
		{
			// Создаем html код
			$html = '';
			foreach($template['elements'] as $n=>$element)
			{
				if ($html) { $html .= ' <span class="dashicons dashicons-arrow-right"></span> '; }

				$text = $element['text'];

				if (isset($element['uri']) && $element['uri'])
				{
					$text = '<a href="'.$element['uri'].'">'.$element['text'].'</a>';
				}

				$html .= '<span class="breadcrumbs_element">'.$text.'</span>';
			}
			
			$firstType = '';
			if ($this->firstEntity)
			{
				$firstType = $this->firstEntity->getType();
			}

			return '<div class="breadcrumbs" id="js-breadcrumbs" data-first-type="'
			.$firstType
			.'">'.$html.'</div><hr>';
		}
	}
}