<?php
namespace Wdpro\Reviews;

class ConsoleForm extends \Wdpro\Form\Form {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {
		
		$this->add(['name'=>'text', 'type'=>static::CKEDITOR_SMALL, 'top'=>'Цитата' ]);
		$this->add(['name'=>'name', '*'=>true, 'top'=>'Имя']);
		$this->add(static::SORTING);
		$this->add(static::SUBMIT_SAVE);
	}


}