<?php
namespace Wdpro\AdminNotice;

class ConsoleRoll extends \Wdpro\Console\Roll {

	/**
	 * Возвращает класс сущностей данного списка
	 *
	 * @return \Wdpro\BaseEntity
	 */
	public static function getEntityClass() {

		return Entity::class;
	}


	/**
	 * Текст, который отображается вверху страницы
	 *
	 * @return string
	 */
	public function about() {

		return 'Это список админских контактов, на которые сайт будет отправлять 
		письма, например когда пользователи будут заполнять форму обратной связи.';
	}

	public static function params() {
		
		return array(
			'labels'=>array(
				'label'=>'Контакты для уведомлений',
				'add_new'=>'Добавить контакт',
			),
			'where'=>'ORDER BY sorting',
		);
	}
	
	
	public function templateHeaders() {
		
		return array('E-mail', '№ п.п.');
	}
	
	
	public function template($data, $entity) {
		
		return array($data['email'], $data['sorting']);
	}
}