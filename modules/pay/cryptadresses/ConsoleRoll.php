<?php
namespace Wdpro\Pay\CryptAddresses;

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
	 *  'orderby'=>'$ORDER_FIELD',
	 *  'icon'=>WDPRO_ICONS_PRODUCTS,
	 *      // https://developer.wordpress.org/resource/dashicons/#lock
	 *
	 *  'subsections'=>false,
	 *  'where'=>["WHERE ... %d, %d", [1, 2]],
	 *  'pagination'=>10, //  Количество элементов на странице
	 *  'info'=>'<p>Всякая информация над списком элементов</p>',
	 *
	 *  'showNew' => true, // Показывать в меню количество новых записей
	 * );
	 * </pre>
	 *
	 * @return array
	 */
	public static function params() {

		return [
			'labels' => [
				'label'   => 'Fkwallets / Временные адреса',
				
			],
			// Когда это дочерний элемент
			/*'where'  => [
				'WHERE `post_parent`=%d ORDER BY `menu_order`',
				[
					isset($_GET['sectionId']) ? $_GET['sectionId'] : 0,
				]
			],*/
			'where'=>'ORDER BY id',
			'icon' => 'fab fa-bitcoin',

			// 'pagination'=>10, //  Количество элементов на странице
			// 'info'=>'<p>Всякая информация над списком элементов</p>',
		];
	}


	/**
	 * Возвращает колонки таблицы
	 *
	 * @param array $data Данные строки
	 * @param \Wdpro\BaseEntity $entity Сущность
	 * @return array
	 */
	public function template($data, $entity) {

		return [
			$this->getSortingField($data)
		];
	}


	/**
	 * Возвращает заголовки таблицы в виде массива
	 *
	 * @return array
	 */
	public function templateHeaders() {

		return [
			'№ п.п.',
		];
	}


}