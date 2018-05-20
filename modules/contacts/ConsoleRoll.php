<?php
namespace Wdpro\Contacts;

class ConsoleRoll extends \Wdpro\Console\Roll {

	
	/**
	 * Возвращает параметры списка (необходимо переопределить в дочернем классе для
	 * установки настроек)
	 *
	 * @return array
	 */
	public static function params() {

		return array(
			'labels'=>array(
				'label'=>'Контакты',
				'add_new'=>'Добавить блок контактов',
			),
			'where'=>'ORDER BY sorting',
		);
	}


	/**
	 * Возвращает колонки таблицы
	 *
	 * @param array $data Данные строки
	 * @param \Wdpro\BaseEntity $entity Сущность
	 * @return array
	 */
	public function template( $data, $entity ) {

		return array(
			$data['text'],
			$data['sorting'],
		);
	}


	/**
	 * Возвращает заголовки таблицы в виде массива
	 *
	 * @return array
	 */
	public function templateHeaders() {

		return array(
			'Адрес, контакты',
			'№ п.п.',
		);
	}


}