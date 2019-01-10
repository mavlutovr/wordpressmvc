<?php
namespace Wdpro\Sender\Templates\Email;

class ConsoleForm extends \Wdpro\Form\Form {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

		$this->add(array(
			'name'=>'name',
			'left'=>'Имя шаблона',
			'bottom'=>'Имя, по которому к шаблону обращаться в скриптах (английские буквы)',
			'*'=>true,
		));
		$this->add(array(
			'name'=>'subject',
			'left'=>'Тема письма',
			'*'=>true,
			'width'=>600,
		));
		$this->add(array(
			'name'=>'text',
			'top'=>'Текст письма',
			'type'=>'ckeditor',
			'autoWidth'=>false,
		));
		$this->add('submitSave');
		$this->add(array(
			'name'=>'info',
			'left'=>'Информация о шаблоне',
			'type'=>'text',
			'autoWidth'=>false,
			'style'=>'width: 600px; height: 100px',
			'bottom'=>'Сюда можно добавить описания шорткодов',
		));
	}


	
}