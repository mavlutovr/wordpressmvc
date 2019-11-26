<?php
namespace Wdpro\Cart;

/**
 * Основная сущность модуля
 */
class Entity extends \Wdpro\BaseEntity {


	/**
	 * Перемещает товар в корзину
	 *
	 * @param Order\Entity $order Заказ
	 * @return Entity
	 */
	public function moveToOrder($order) {

		$this->data['order_id'] = $order->id();

		return $this;
	}
}