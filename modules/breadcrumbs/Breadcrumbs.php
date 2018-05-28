<?php
namespace Wdpro\Breadcrumbs;

class Breadcrumbs
{
	/** @var Element[] */
	protected $prepended = array();
	/** @var Element[] */
	protected $appended = array();
	
	/** @var  \Wdpro\BasePage */
	protected $firstEntity;
	protected $lastAddedEntity;
	protected $lastElementsWithoutLinks = 1;
	
	protected static $min = 1;
	protected static $removeLast = false;

	/**
	 * Шаблон хлебных крошек
	 * 
	 * @var string
	 */
	protected $templateFilName;

	/**
	 * Для каждого типа страницу зхдесь сохраняется ID корневого раздела этого типа
	 * 
	 * any - это любой тип, т.е. в этом ключе вообще самый корневой раздел, независимо 
	 * от типа
	 * 
	 * @var array
	 */
	public $root = array();


	/**
	 * Для каждого типа страниц тут сохраняется ID раздела, в котором находится данный 
	 * типа страниц
	 *
	 * @var array
	 */
	public $parentByType = array();


	/**
	 * Конструктор
	 */
	public function __construct() {
		
		// Шаблон по-умолчанию
		$this->templateFilName = WDPRO_TEMPLATE_PATH.'breadcrumbs_template.php';
	}


	/**
	 * Создание структуры пути хлебных крошек
	 * 
	 * @param \Wdpro\BasePage $entity Конечная (текущая) страница
	 */
	public function makeFrom($entity)
	{
		if ($entity)
		{
			$this->firstEntity = $entity;
			$element = new EntityElement($entity);
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
	}


	/**
	 * Запускается по завершении $this->makeFrom()
	 */
	public function afterMake()
	{
		
	}


	/**
	 * Добавляет страницу в конец хлебных крошек
	 *
	 * @param Element|array|string $element Элемент хлебных крошек
	 */
	public function append($element)
	{
		$this->appended[] = $this->getElement($element);
	}
	
	
	/**
	 * Добавляет страницу в начало хлебных крошек
	 *
	 * @param Element|array|string $element Элемент хлебных крошек
	 */
	public function prepend($element)
	{
		$this->prepended[] = $this->getElement($element);
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
	 * Преобразует данные для добавления в данные хлебных крошек
	 *
	 * @param \Wdpro\BasePage|string $pageOrText Страница или текст ссылки
	 * @param string $uri Адрес ссылки (не нужно указывать, когда указана страница)
	 * @return array
	 */
	public function getData($pageOrText, $uri)
	{
		if (is_object($pageOrText))
		{
			return array(
				'uri'=>$pageOrText->getUri(),
				'text'=>$pageOrText->getButtonText(),
			);
		}

		else
		{
			return array(
				'uri'=>$uri,
				'text'=>$pageOrText,
			);
		}
	}


	public function replacePrepend($i, $callback)
	{
		$this->prepended[$i] = $callback($this->prepended[$i]);
	}


	/**
	 * Возвращает элемент из добавленных влево
	 * 
	 * @param int $i Номер элемента
	 * @return EntityElement
	 */
	public function getPrepend($i) {
		
		return $this->prepended[$i];
	}


	/**
	 * Возвращает объект самой первой страницы в пути страниц
	 * 
	 * @return \Wdpro\BaseEntity
	 */
	public function getFirstEntity()
	{
		return $this->firstEntity;
	}


	/**
	 * Возвращает все элементы слева и справа в одном массиве
	 * 
	 * @return Element[]
	 */
	public function getElements()
	{
		/** @var \Wdpro\Breadcrumbs\Element[] $elements */
		$elements = array();
		$prepended = $this->prepended;
		array_reverse($prepended);
		for($i = count($prepended) - 1; $i >= 0; $i --)
		{
			$elements[] = $prepended[$i];
		}
		foreach($this->appended as $element)
		{
			$elements[] = $element;
		}
		
		return $elements;
	}


	/**
	 * Возвращает данные для шаблона
	 * 
	 * @return array
	 */
	public function getTemplateData()
	{
		$elements = $this->getElements();
		
		$templateData = array();
		foreach($elements as $element)
		{
			$templateData[] = $element->getData();
		}
		//-----------------------------------------------
		$count = count($templateData);
		if (static::$removeLast)
		{
			unset($templateData[$count - 1]);
			$count --;
		}

		for($j=1; $j<=$this->lastElementsWithoutLinks; $j ++)
		{
			unset($templateData[$count - $j]['uri']);
		}

		if (static::$min <= $count)
		{
			return array(
				'elements'=>$templateData,
			);
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
			// Корректировка адресов
			foreach($template['elements'] as $n=>$element)
			{
				if (isset($element['uri']) && $element['uri'])
				{
					$url = $element['uri'];
					if (!wdpro_is_absolute_url($url)) {
						$template['elements'][$n]['uri'] = home_url($url);
					}
				}
			}
			
			// Рендерим и возвращаем результат
			return wdpro_render_php(
				$this->templateFilName,
				$template
			);
			
			// Создаем html код
			$html = '';
			foreach($template['elements'] as $n=>$element)
			{
				if ($html) { $html .= ' <span class="breadcrumbs-separator">/</span> '; }

				$text = $element['text'];

				if (isset($element['uri']) && $element['uri'])
				{
					$url = $element['uri'];
					if (!wdpro_is_absolute_url($url)) {
						$url = home_url($url);
					}
					
					$text = '<a href="'.$url.'">'
						.$element['text']
						.'</a>';
				}

				$html .= $text;
			}

			return '<div class="breadcrumbs">'.$html.'</div>';
		}
	}


	/**
	 * Удалить ссылку с последнего элемента у которого есть ссылка
	 */
	public function removeLink()
	{
		$this->lastElementsWithoutLinks ++;
	}


	/**
	 * Вернуть ссылку к последнему элементу, у которого есть ссылка
	 */
	public function unremoveLink() {
		$this->lastElementsWithoutLinks --;
	}


	/**
	 * Возвращает правый элемент левой части
	 * 
	 * @return Element
	 */
	public function getRightPrepend()
	{
		return $this->prepended[0];
	}


	/**
	 * Проверяет, есть ли абсолютный адрес в хлебных крошках
	 *
	 * @param string $url Проверяемый адрес
	 * @return bool
	 */
	public function isUrl($url)
	{
		$elements = $this->getElements();

		foreach($elements as $element)
		{
			if ($element->isUrl($url))
			{
				return true;
			}
		}

		return false;
	}


	/**
	 * Проверяет, есть ли относительный адрес в хлебных крошках
	 *
	 * @param string $uri Проверяемый адрес
	 * @return bool
	 */
	public function isUri($uri)
	{
		$elements = $this->getElements();

		foreach($elements as $element)
		{
			if ($element->isUri($uri))
			{
				return true;
			}
		}

		return false;
	}


	/**
	 * Возвращает ID самой первой страницы заданного типа
	 * 
	 * any - любой тип, т.е. возвратит ID просто самого корневого раздела
	 * 
	 * @param string $postType Тип поста
	 * @return int
	 */
	public function getRootPostId($postType='any') {
		
		if (isset($this->root[$postType])) {
			
			return $this->root[$postType];
		}
	}


	/**
	 * Возвращает ID раздела, в котором находится первая страница заданного типа
	 * 
	 * Например, когда в хлебных крошках:
	 * Курсы / Курс / Подраздел курса / Подраздел курса
	 * 
	 * То если мы запросим этот метод и укажем тип "Подраздел курса", то она возвратит
	 * ID Подраздела курса
	 *
	 * @param string $postType Тип поста
	 * @return int
	 */
	public function getParentIdOfPostType($postType) {
		
		if (isset($this->parentByType[$postType])) {

			return $this->parentByType[$postType];
		}
	}


	/**
	 * Установка шаблона для вывода хлебных крошек
	 * 
	 * @param string $templateFileName Путь к файлу шаблона
	 */
	public function setTemplate($templateFileName) {
		
		$this->templateFilName = $templateFileName;
	}


	/**
	 * Удаляет последний элемент (который такой же как заголовок)
	 *
	 * @param bool $remove true - убрать, false - вернуть
	 */
	public function removeLast($remove=true) {
		static::$removeLast = $remove;
	}
}