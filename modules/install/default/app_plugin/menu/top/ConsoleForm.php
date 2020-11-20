<?php

namespace App\Menu\Top;

class ConsoleForm extends \Wdpro\Form\Form
{

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields()
	{

		$this->add([
			'name' => 'post_content[lang]',
			'top' => 'Текст страницы',
			'type' => static::CKEDITOR,
		]);

		if ($info = \Wdpro\Page\Controller::getConsoleFormInfo()) {
			$this->add([
				'type'=>static::HTML,
				'html'=>$info,
			]);
		}
	}


}