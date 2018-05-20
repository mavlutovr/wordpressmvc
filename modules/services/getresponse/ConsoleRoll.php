<?php
namespace Wdpro\Services\Getresponse;

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
	 *        // http://fontawesome.io/icons/
	 *
	 *  'subsections'=>false,
	 *  'where'=>["WHERE ... %d, %d", [1, 2]],
	 *  'pagination'=>10, //  Количество элементов на странице
	 *  'info'=>'<p>Всякая информация над списком элементов</p>'
	 * );
	 * </pre>
	 *
	 * @return array
	 */
	public static function params() {
		return [
			'labels' => [
				'label'=>'GetResponse лог',
			],
			'icon'=>'fa-list',
			'pagination'=>20,
			'info'=>'<p>Это история подписки. Сюда добавляются ящики, которые 
			пробовали подписаться.</p>',
			'where'=>'ORDER BY id DESC',
		];
	}

	/**
	 * Возвращает заголовки таблицы в виде массива
	 *
	 * @return array
	 */
	public function templateHeaders() {
		return [
			'E-mail',
			'Статус',
			'Время',
		];
	}


	/**
	 * Возвращает колонки таблицы
	 *
	 * @param array $data Данные строки
	 * @param \Wdpro\BaseEntity $entity Объект списка
	 *
	 * @return array
	 */
	public function template( $data, $entity ) {
		return [
			$data['email'],
			$data['status'],
			wdpro_date($data['date'], [ 'time'=>false ])
		];
	}


}