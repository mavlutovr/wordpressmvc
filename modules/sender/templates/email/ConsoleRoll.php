<?php
namespace Wdpro\Sender\Templates\Email;

class ConsoleRoll extends \Wdpro\Console\Roll {

	/**
	 * Возвращает параметры списка (необходимо переопределить в дочернем классе для
	 * установки настроек)
	 *
	 * <pre>
	 * return array(
	 *  'labels'=>array(
	 *   'name'=>'Разделы каталога',
	 *   'label'=>'Каталог',
	 *   'add_new'=>'Добавить раздел...',
	 *  ),
	 *  'order'=>'ASC',
	 *  'orderby'=>'menu_order',
	 *  'icon'=>WDPRO_ICONS_PRODUCTS,
	 *      // https://developer.wordpress.org/resource/dashicons/#lock
	 *
	 *  'subsections'=>false,
	 *  'where'=>["WHERE ... %d, %d", [1, 2]],
	 * );
	 * </pre>
	 *
	 * @return array
	 */
	public static function params() {

		return array(
			'labels'=>array(
				'label'=>'Шаблоны писем',
				'add_new'=>'Добавить шаблон письма',
			),
			'where'=>'ORDER BY `sorting`',
			'icon'=>'fa-envelope-o',
		);
	}


	/**
	 * Возвращает колонки таблицы
	 *
	 * @param array $data Данные строки
	 * @param \Wdpro\BaseEntity $entity
	 * @return array
	 */
	public function template( $data, $entity ) {

		return array(
			$data['name'],
			$data['subject'],
		);
	}


	/**
	 * Возвращает заголовки таблицы в виде массива
	 *
	 * @return array
	 */
	public function templateHeaders() {

		return array(
			'Имя шаблона',
			'Тема письма',
		);
	}


}