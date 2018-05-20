<?php
namespace Wdpro\Getresponse\Move;

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
	 *  'info'=>'<p>Всякая информация над списком элементов</p>'
	 * );
	 * </pre>
	 *
	 * @return array
	 */
	public static function params() {

		return [
			'labels' => [
				'label'   => 'Getresponse перемещения',
				'add_new' => 'Добавить перемещение',
			],
			// Когда это дочерний элемент
			// 'where'  => ['WHERE `post_parent`=%d ORDER BY `sorting`', [$_GET['sectionId']]],
			'where'  => 'ORDER BY `sorting`',
			'icon' => 'fa-exchange',
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

		$compaigns = \Wdpro\Services\Getresponse\Controller::getCompaignsOptions();

		return [
			$compaigns[$data['from']],
			$data['days'],
			$compaigns[$data['to']],
			$data['enable'] ? 'Включен' : '',
			$data['sorting'],
		];
	}


	/**
	 * Возвращает заголовки таблицы в виде массива
	 *
	 * @return array
	 */
	public function templateHeaders() {

		return [
			'Откуда',
			'Дни',
			'Куда',
			'Включен',
			'№ п.п.',
		];
	}


}