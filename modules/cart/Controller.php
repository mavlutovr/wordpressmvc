<?php

namespace Wdpro\Cart;

use Wdpro\BasePage;

class Controller extends \Wdpro\BaseController
{

	/**
	 * Инициализация модуля
	 */
	public static function init()
	{
		// Добавление в корзину
		wdpro_ajax('cart_count', function () {

			/** @var CartElementInterface|BasePage $entity */
			$entity = wdpro_object_by_key($_POST['key']);


			static::updateCount($entity, $_POST['count']);

			return [
				'html' => static::getAddToCartButton($entity),
				'cart_info' => static::getInfoData(),
			];
		});
	}


	/**
	 * Возвращает информацию о корзине
	 *
	 * Чтобы, например, на сайте указать количество товаров, добавленных в корзину
	 *
	 * @return array
	 */
	public static function getInfoData()
	{

		$info = [
			'count' => 0,
			'count_all' => 0,
			'list' => [],
		];

		$where = [
			'WHERE ( visitor_id=%d OR (person_id=%d AND person_id!=0)) ',
			[ wdpro_visitor_session_id(), wdpro_person_auth_id()]
		];

		if ($sel = SqlTable::select($where)) {

			foreach ($sel as $row) {

				$info['count']++;
				$info['count_all'] += $row['count'];

				$info['list'][] = [
					'id' => $row['id'],
					'cost_for_one' => $row['cost_for_one'],
					'cost_for_all' => $row['cost_for_all'],
					'count' => $row['count'],
				];
			}
		}

		return $info;
	}


	/**
	 * Добавление элемента в корзину или изменение количества элементов в корзине
	 *
	 * @param \Wdpro\BasePage $entityObjectOrKey Добавляемый в корзину элемент
	 * @param int $count Количество добавляемых элементов
	 * @throws \Exception
	 */
	public static function updateCount($entityObjectOrKey, $count = 1)
	{

		if (is_string($entityObjectOrKey)) {
			$entityObjectOrKey = wdpro_object_by_key($entityObjectOrKey);
		}

		$row = [
			'element_key' => $entityObjectOrKey->getKey(),
			'count' => $count,
			'visitor_id' => wdpro_visitor_session_id(),
			'person_id' => wdpro_person_auth_id(),
		];

		$where = [
			'WHERE element_key=%s AND ( visitor_id=%d OR (person_id=%d AND person_id!=0)) ',
			[$row['element_key'], $row['visitor_id'], $row['person_id']]
		];


		// Когда элемент уже есть в корзине
		if ($current = SqlTable::getRow($where)) {

			$current['count'] = $count;

			// Меняем количество
			if ($current['count'] > 0) {
				$current = apply_filters('wdpro_cart_elment_update', $current);
				SqlTable::update($current, ['id' => $current['id']]);
			} // Удаляем
			else {
				SqlTable::delete(['id' => $current['id']]);
			}
		} // Когда нет в корзине
		else {
			$row = apply_filters('wdpro_cart_elment_update', $row);

			SqlTable::insert($row);
		}
	}


	/**
	 * Корректировка данных корзины
	 *
	 * @param array $data Данные корзины
	 * @param CartElementInterface|BasePage $entity
	 * @return array
	 */
	protected static function updateData($data, $entity)
	{

		$data['cost_for_one'] = $entity->getCost();
		$data['cost_for_all'] = $data['cost_for_one'] * $data['count'];

		$data = apply_filters('wdpro_cart_elment_update', $data);

		return $data;
	}


	/**
	 * Возвращает кнопку "Добавить в корзину" для элемента
	 *
	 * @param CartElementInterface|\Wdpro\BasePage $entity Элемент, который можно добавить в корзину по этой кнопке
	 * @return string
	 */
	public static function getAddToCartButton($entity)
	{

		$templateData = [];

		$templateData['added'] = SqlTable::getRow([
			'WHERE element_key=%s AND order_id=0',
			[$entity->getKey()]
		]);

		$templateData['entity'] = $entity->getData();
		$templateData['key'] = $entity->getKey();

		return wdpro_render_php(
			WDPRO_TEMPLATE_PATH . 'add_to_cart_button.php',
			$templateData
		);
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite()
	{
		wdpro_default_file(
			__DIR__ . '/default/add_to_cart_button.php',
			WDPRO_TEMPLATE_PATH . 'add_to_cart_button.php'
		);
	}


}


return __NAMESPACE__;