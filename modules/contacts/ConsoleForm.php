<?php
namespace Wdpro\Contacts;

class ConsoleForm extends \Wdpro\Form\Form {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

		$this->add(array(
			'name'=>'text[lang]',
			'top'=>'Адрес, контакты',
			//'*',
			'type'=>'ckeditor',
			//'config'=>WDPRO_FORM_CKEDITOR_SMALL,
			'autoLeft'=>false,
		));
		$this->add(array(
			'name'=>'map',
			'top'=>'Код карты',
			'bottom'=>'<a href="https://yandex.ru/map-constructor/?from=mapstools" target="_blank">Конструктор карт</a>',
			'type'=>'text',
			'width'=>'700px',
			'autoLeft'=>false,
		));
		$this->add('sorting');
		$this->add('submitSave');
	}


}