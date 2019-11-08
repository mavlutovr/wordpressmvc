<?php
namespace Wdpro\Cart\Order;

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
				'label'   => 'Заказы',
			],
			// Когда это дочерний элемент
			/*'where'  => [
				'WHERE `post_parent`=%d ORDER BY `menu_order`',
				[
					isset($_GET['sectionId']) ? $_GET['sectionId'] : 0,
				]
			],*/
			'where'=>'ORDER BY id DESC',
			'pagination'=>10,
			'icon' => 'fas fa-shopping-cart',
			'n'=>1,
			'showNew'=>true,
		];
	}


	/**
	 * Возвращает колонки таблицы
	 *
	 * @param array $data Данные строки
	 * @param \Wdpro\BaseEntity|Entity $entity Сущность
	 * @return array
	 */
	public function template($data, $entity)
	{

		$summaryData = \Wdpro\Cart\Controller::getSummaryInfo([ 'orderId' => $data['id'] ]);

		return [

			// №
			'<h1><a href="'.$entity->getUrl().'" target="_blank" style="font-size: 3em; line-height: 1em;">'.$data['id'].'</a></h1>'

			// Дата
			. '<p>'
				. wdpro_date($data['time'], [
					'today' => true,
					'time' => true,
				])
			. '</p>'

			// Статус
			. '<p>'.$entity->getConsoleStatus().'</p>',


			// Товары
			$entity->getConsoleGoods(),


			// Стоимость
			//'<p>'.$summaryData['cost'].' руб.</p>'
			apply_filters(
				'wdpro_order_console_cost',
				'<p><span class="js-cost">'.$summaryData['cost'].'</span> руб.</p>',
				$entity,
				$summaryData
			),


			// Данные покупателя
			$entity->getCustomerHtml(),

		];
	}


	/**
	 * Возвращает заголовки таблицы в виде массива
	 *
	 * @return array
	 */
	public function templateHeaders() {

		return [

			'№ / Дата / Статус',

			'Товары',

			'Стоимость',

			'Покупатель',
		];
	}


}