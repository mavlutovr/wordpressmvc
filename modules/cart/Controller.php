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

			$cartRow = static::updateCount($entity, $_POST['count']);

			// Когда это страница корзины
			if ($_GET['currentPostName'] === 'cart') {

				// Возвращаем блок списка корзины
				$cartRow = Roll::prepareDataForTemplate($cartRow);
				$html = $cartRow['html'];
			}

			// Любая другая страница
			else {
				// Возвращаем кнопку "Добавить в корзину"
				$html = static::getAddToCartButton($entity);
			}

			return [
				'html' => $html,
				'cartInfo' => static::getSummaryInfo(),
			];
		});
	}


	/**
	 * Перемещает товары, которые сейчас в корзине в заказ
	 *
	 * @param Order\Entity $order
	 */
	public static function moveCartGoodsToOrder($order) {

		if ($sel = SqlTable::select( static::getWhere() )) {
			foreach ($sel as $row) {

				$item = Entity::instance($row);
				$item->moveToOrder($order)->save();
			}
		}
	}


	/**
	 * Возвращает информацию о корзине
	 *
	 * Чтобы, например, на сайте указать количество товаров, добавленных в корзину
	 *
	 * @param null|array $params Параметры
	 *            ['extraColls'] Массив полей, которые дополнительно добавить в список товаров из табицы
	 *
	 * @return array
	 *            ['list'] Список товаров
	 *            ['count'] Количество элементов в корзине
	 *            ['count_all'] Количество всех товаров в корзине
	 */
	public static function getSummaryInfo($params=null)
	{

		$summary = [
			'count_types' => 0,
			'count_all' => 0,
			'list' => [],
			'cost'=>0,
			'discount'=>0,
			'total'=>0,
		];

		$where = static::getWhere($params);

		if ($sel = SqlTable::select($where)) {

			foreach ($sel as $row) {

				$summary['count_types']++;
				$summary['count_all'] += $row['count'];

				/** @var \Wdpro\Cart\CartElementInterface $good */
				$good = wdpro_object_by_key($row['key']);

				$listElement = [
					'id' => $row['id'],
					'cost_for_one' => $row['cost_for_one'],
					'cost_for_all' => $row['cost_for_all'],
					'count' => $row['count'],
					'keyArray'=>wdpro_key_parse($row['key']),
					'good'=>$good->getDataForCartSummaryInfo($row['key']),
				];

				if ($params && isset($params['extraColls']) && is_array($params['extraColls'])) {
					foreach ($params['extraColls'] as $extraColl) {
						$listElement[$extraColl] = $row[$extraColl];
					}
				}

				$summary['list'][] = $listElement;
				$summary['cost'] += $listElement['cost_for_all'];
			}
		}

		$summary['total'] = $summary['cost'];


		$summary = apply_filters('wdpro_cart_summary', $summary);


		return $summary;
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
			'key' => $entityObjectOrKey->getKey(),
			'count' => $count,
			'visitor_id' => wdpro_visitor_session_id(),
			'person_id' => wdpro_person_auth_id(),
		];

		$where = static::getWhere([
			'key'=>$row['key'],
		]);


		// Обновляем уже добавленный в корзину товар
		if ($current = SqlTable::getRow($where)) {

			$current['count'] = $count;

			// Меняем количество
			if ($current['count'] > 0) {
				$current = static::updateData($current, $entityObjectOrKey);
				SqlTable::update($current, ['id' => $current['id']]);
			}

			// Удаляем
			else {
				SqlTable::delete(['id' => $current['id']]);
			}

			return $current;
		}

		// Добавляем в корзину новый товар
		else {

			$row = static::updateData($row, $entityObjectOrKey);

			$id = SqlTable::insert($row);

			$rowSql = SqlTable::getRow([
				'WHERE id=%d',
				[ $id ]
			]);

			do_action('wdpro_cart_added', $rowSql);

			return $rowSql;
		}
	}


	/**
	 * Корректировка данных элемента корзины
	 *
	 * @param array $data Данные корзины
	 * @param CartElementInterface|BasePage|array|string $entityObjectOrKey
	 * @return array
	 */
	protected static function updateData($data, $entityObjectOrKey)
	{
		if (is_string($entityObjectOrKey) || is_array($entityObjectOrKey)) {
			$entityObjectOrKey = wdpro_object_by_key($entityObjectOrKey);
		}

		$data['cost_for_one'] = $entityObjectOrKey->getCost();
		$data['cost_for_all'] = $data['cost_for_one'] * $data['count'];

		$data = apply_filters('wdpro_cart_elment_update', $data);

		return $data;
	}


	/**
	 * Возвращает кнопку "Добавить в корзину" для элемента
	 *
	 * @param CartElementInterface|\Wdpro\BasePage $entity Элемент, который можно добавить в корзину по этой кнопке
	 * @param null|string|array $entityKey Ключ товара, в котором может хранится не только сам товар,
	 *    но и дополнительная информация, такая, как цвет и размер.
	 * @return string
	 * @throws \Exception
	 */
	public static function getAddToCartButton($entity, $entityKey=null)
	{

		$templateData = [];

		if (!$entityKey)
			$entityKey = $entity->getKey();

		$entityKey = wdpro_key_parse($entityKey);

		$templateData['added'] = SqlTable::getRow(static::getWhere([
			'key'=>$entityKey['key']
		]));

		// Убираем лишние копейки
		if ($templateData['added']) {
			$templateData['added']['cost_for_one'] *= 1;
			$templateData['added']['cost_for_all'] *= 1;
		}

		$templateData['good'] = $entity->getDataForCartButton($entityKey);
		$templateData['key'] = $entityKey;

		return wdpro_render_php(
			WDPRO_TEMPLATE_PATH . 'cart_add_button.php',
			$templateData
		);
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite()
	{
		wdpro_default_file(
			__DIR__ . '/default/templates/cart_add_button.php',
			WDPRO_TEMPLATE_PATH . 'cart_add_button.php'
		);

		wdpro_default_file(
			__DIR__ . '/default/templates/cart_list.php',
			WDPRO_TEMPLATE_PATH . 'cart_list.php'
		);

		wdpro_default_file(
			__DIR__.'/default/templates/cart_list_item.php',
			WDPRO_TEMPLATE_PATH.'cart_list_item.php'
		);

		wdpro_default_page('cart', __DIR__.'/default/pages/cart.php');


		wdpro_on_content_uri('cart', function ($content, $page) {

			wdpro_replace_or_append(
				$content,
				'[cart_list]',

				Roll::getHtml(static::getWhere())
			);

			return $content;
		});
	}


	/**
	 * Возвращает Where для выборки товаров в корзине для текущего посетителя
	 *
	 * @param null|array $params Параметры
	 *      [orderId] - Номер заказа
	 *
	 * @return array
	 */
	protected static function getWhere($params=null) {

		if ($params === null) $params = [];
		if (!isset($params['orderId'])) $params['orderId'] = 0;

		$where = [
			'WHERE ( visitor_id=%d OR (person_id=%d AND person_id!=0)) AND order_id=%d ',
			[ wdpro_visitor_session_id(), wdpro_person_auth_id(), $params['orderId'] ]
		];

		if (isset($params['key'])) {
			$where[0] .= ' AND `key`=%s';
			$where[1][] = $params['key'];
		}

		return $where;
	}


	/**
	 * Возвращает список товаров заказа для админки
	 *
	 * @param int $orderId ID заказа
	 * @return string
	 * @throws \Exception
	 */
	public static function getConsoleGoods($orderId) {

		$html = '';

		$summaryInfo = static::getSummaryInfo([
			'orderId'=> $orderId
		]);

		foreach ($summaryInfo['list'] as $cartData) {

			/** @var CartElementInterface $goodItem */
			$goodItem = wdpro_object_by_key($cartData['keyArray']);

			$html .= '<div class="order-item">'.$goodItem->getConsoleHtml($cartData).'</div>';
		}

		return $html;
	}
}


return __NAMESPACE__;