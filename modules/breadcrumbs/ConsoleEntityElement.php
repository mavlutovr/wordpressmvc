<?php
namespace Wdpro\Breadcrumbs;

class ConsoleEntityElement extends EntityElement
{
	
	/**
	 * Возвращает данные
	 *
	 * @return array
	 */
	public function getData()
	{
		$text = $this->entity->getButtonText();
		
		$type = $this->entity->getType();
		if ($type != $this->childsType && $this->childsType)
		{
			//$rollParams = $this->entity->getConsoleRoll()->getParams();
			/** @var \Wdpro\BasePage $entityClass */
			$entityClass = wdpro_get_class_by_post_type($this->childsType);
			$roll = $entityClass::getConsoleRoll();
			$rollParams = $roll->getParams();
			$text .= ': '.$rollParams['labels']['label'];
		}
		
		if ($this->comment)
		{
			$text .= ': '.$this->comment;
		}
		
		if (isset($this->params['uri'])) {
			$uri = $this->params['uri'];
		}
		else {
			$uri = $this->entity->getConsoleListUri($this->childsType);
		}
		
		return array(
			'text'=>$text,
			'uri'=>$uri,
		);
	}

}