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

		// Текст страницы
		$this->add([
		'name' => 'post_content[lang]',
		'top' => 'Текст страницы',
		'type' => static::CKEDITOR,
		]);


	}


}