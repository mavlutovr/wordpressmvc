<?php
namespace Wdpro\Sender\Mailing;

class ConsoleForm extends \Wdpro\Form\Form {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

		if ($info = wdpro_get_option('mailing-form-info')) {
			if ($info) {
				$this->addHtml($info);
			}
		}


		$this->add([
			'name'=>'label',
			'top'=>'Название (для себя)',
			'width'=>600,
		]);


		$this->add([
			'name'=>'subject',
			'top'=>'Тема письма',
			'*'=>true,
			'width'=>600,
		]);


		$this->add([
			'name'=>'text',
			'top'=>'Текст письма',
			'type'=>static::CKEDITOR,
		]);
		$this->addHtml(Controller::getConsoleFormTextInfo());
		
		$this->addAdditionalFields();
		
		$this->add([
			'name'=>'status',
			'top'=>'Статус',
			'type'=>static::SELECT,
			'options'=>Controller::getStatuses(),
		]);

		$this->add(static::SUBMIT_SAVE);
	}



	protected function addAdditionalFields() {
		
	}
}