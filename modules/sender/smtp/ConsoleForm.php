<?php
namespace Wdpro\Sender\Smtp;

class ConsoleForm extends \Wdpro\Form\Form {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

		$this->add([
			'name'=>'mail',
			'left'=>'E-mail',
			'*'=>true,
			'center'=>'Например, your.name@yandex.ru',
		]);

		$this->add([
			'name'=>'server',
			'left'=>'Адрес сервера',
			'*'=>true,
			'center'=>'Например, smtp.yandex.ru',
		]);

		$this->add([
			'name'=>'port',
			'left'=>'Порт',
			'*',
			'center'=>'Например, 465',
		]);

		$this->add([
			'name'=>'login',
			'left'=>'Логин',
			'*',
			'center'=>'Обычно то, что до знака @',
		]);

		$this->add([
			'name'=>'pass',
			'left'=>'Пароль',
			'type'=>static::PASS,
		]);

		$this->add([
			'name'=>'protocol',
			'left'=>'Протокол',
			'type'=>'select',
			'options'=>array(
				''=>'',
				'ssl'=>'SSL',
				'tsl'=>'TSL',
			),
		]);

		$this->add(static::SORTING);
		$this->add(static::SUBMIT_SAVE);
	}


}