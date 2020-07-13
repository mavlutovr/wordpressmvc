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
			'left'=>'Имя (папка) языка из 2-х букв (ru, en, de...)',
		]);

		$this->add([
			'name'=>'name',
			'left'=>'Полное имя (Русский, English...)',
			''
		]);

		$this->add([
			'name'=>'code',
			'left'=>'Код языка (en-us)',
		]);

		$this->add([
			'name'=>'flag',
			'left'=>'Флаг для админки',
			'bottom'=>'Download <a href="http://www.world-globe.ru/countries/flags/">flags 1</a>, <a href="https://www.iconfinder.com/iconsets/flags-37" target="_blank">flags 2</a>',
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