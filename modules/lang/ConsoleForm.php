<?php
namespace Wdpro\Lang;

class ConsoleForm extends \Wdpro\Form\Form {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {
		$this->add([
			'name'=>'uri',
			'left'=>'Имя языка из 2-х букв (ru, en, de...)',
		]);

		$this->add([
			'name'=>'name',
			'left'=>'Полное имя (Русский, English...)',
			''
		]);

		$this->add([
			'name'=>'flag',
			'left'=>'Флаг для админки',
			'botton'=>'<a href="http://www.world-globe.ru/countries/flags/">Скачать флаги</a>',
			'type'=>static::IMAGE,
			'resize'=>[
				['height'=>16]
			],
			'*'=>true,
		]);

		$this->add([
			'name'=>'flag_site',
			'left'=>'Флаг для сайта',
			'type'=>static::IMAGE,
		]);

		$this->add([
			'name'=>'visible',
			'left'=>'Показывать на сайте',
			'type'=>static::CHECK,
		]);

		$this->add(static::SORTING);

		$this->add(static::SUBMIT_SAVE);
	}


}