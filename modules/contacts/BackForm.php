<?php
namespace Wdpro\Contacts;

class BackForm extends \Wdpro\Form\Form {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

		$this->setJsName('contactsBack');
		$this->setClass('wdpro-contacts-form');
		$this->add(array(
			'name'=>'name',
			'top'=>'Ваше Имя',
			'*',
		));
		$this->add(array(
			'name'=>'contact',
			'top'=>'E-mail или телефон',
			'*',
		));
		$this->add(array(
			'name'=>'text',
			'top'=>'Сообщение',
			'type'=>'text',
			'*',
		));
		$this->add(array(
			'type'=>'submit',
			'text'=>'Отправить',
		));
	}


}