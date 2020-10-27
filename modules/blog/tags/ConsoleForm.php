<?php
namespace Wdpro\Blog\Tags;

class ConsoleForm extends \Wdpro\Form\Form {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

		$this->add([
			'name'=>'tag',
			'left'=>'Тег',
			'*'=>true,
		]);

		$this->add([
			'name'=>'slug',
			'left'=>'URI тега',
			'*'=>true,
		]);

		// Сортировка и сохранение
		$this->add(static::SUBMIT_SAVE);

	}


}