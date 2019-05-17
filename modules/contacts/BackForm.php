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
			'center'=>'Ваше Имя',
			'*'=>true,
		));
		$this->add(array(
			'name'=>'contact',
			'center'=>'E-mail или телефон',
			'*'=>true,
		));
		$this->add(array(
			'name'=>'text',
			'center'=>'Сообщение',
			'type'=>'text',
			'*'=>true,
		));
		$this->add(array(
			'type'=>'submit',
			'text'=>'Отправить',
		));
		$this->add(array(
			'type'=>static::CHECK,
			'right'=>'Я даю свое согласие на обработку персональных данных и соглашаюсь с условиями и 
<a href=\'/privacy/\' target=\'_blank\'>политикой конфиденциальности</a>',
			'containerClass'=>'privacy-check',
			'checked'=>true,
			'name'=>'privacy',
			'*'=>true,
		));
	}


}