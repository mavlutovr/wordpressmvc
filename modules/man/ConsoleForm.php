<?php
namespace Wdpro\Man;

class ConsoleForm extends \Wdpro\Form\Form {


	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields()
	{
		$entity = $this->getEntity();


		if (!$entity->existsInDb()) {
			$this->add([
				'name'=>'template',
				'left'=>'Шаблон',
				'type'=>static::SELECT,
				'options'=>Controller::getTemplatesOptions(),
			]);

			$this->add([
				'type'=>static::HTML,
				'html'=>'<h2 style="margin-left: 200px;">Или</h2>',
				'autoLeft'=>true,
			]);
		}

		$this->add([
			'name'=>'name',
			'left'=>'Название',
		]);

		$this->add([
			'name'=>'text',
			'top'=>'Текст',
			'type'=>static::CKEDITOR,
		]);

		$this->add(static::SORTING);
		$this->add(static::SUBMIT_SAVE);
	}


}