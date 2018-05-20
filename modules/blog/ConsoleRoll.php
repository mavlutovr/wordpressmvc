<?php
namespace Wdpro\Blog;

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
				'label'=>'Статьи',
				'add_new'=>'Добавить статью',
			),
			'subsections'=>false,
			'icon'=>'fa-file-text',
			'order'=>'DESC',
			'hierarchical'=>1,
		);
	}


}