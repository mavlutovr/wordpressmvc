<?php
namespace Wdpro\Cart;

interface CartElementInterface {

	/**
	 * Возвращает стоимость товара за одну единицу
	 *
	 * @return float
	 */
	public function getCost():float ;
}