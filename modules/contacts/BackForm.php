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

		$this->add(static::RECAPTCHA3);

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