<?php
namespace Wdpro\Lang;

/**
 * Основная сущность модуля
 */
class Entity extends \Wdpro\BaseEntity {

	/**
	 * Срабатывает после сохранения
	 */
	protected function onChange () {

		// Создаем файлик с данными языков
		Controller::updateData();
	}


}