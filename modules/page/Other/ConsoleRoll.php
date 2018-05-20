<?php
namespace Wdpro\Page\Other;

/**
 * Список других страниц в админке
 * 
 * @package Wdpro\Page\Other
 */
class ConsoleRoll extends \Wdpro\Console\PagesRoll {

	/**
	 * Возвращает параметры списка (необходимо переопределить в дочернем классе для
	 * установки настроек)
	 *
	 * @return array
	 */
	public static function params() {

		return array(
			'labels'=>array(
				'label'=>'Другие страницы',
				'add_new'=>'Добавить страницу',
			),
			'orderby'=>'menu_order',
			//'hierarchical'=>false,
		);
	}


	
}