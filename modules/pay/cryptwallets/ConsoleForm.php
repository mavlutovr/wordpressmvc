<?php
namespace Wdpro\Pay\CryptWallets;

class ConsoleForm extends \Wdpro\Form\Form {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

		// Сортировка и сохранение
		$this->add(static::SORTING);
		$this->add(static::SUBMIT_SAVE);

	}


}