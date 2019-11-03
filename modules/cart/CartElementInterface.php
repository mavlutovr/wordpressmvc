<?php
namespace Wdpro\Cart;

interface CartElementInterface {

	/**
	 * Возвращает стоимость товара за одну единицу
	 *
	 * @param null|string $key Ключ объекта товара.
	 *    В котором может содержаться помимо информации для получения самого объекта товара,
	 *    дополнительная информация, такая как цвет, размер и тд.
	 *
	 * @return float
	 */
	public function getCost($key=null):float ;


	/**
	 * Возвращает данные для кнопки "Добавить в корзину"
	 *
	 * @param string|array $entityKey Ключ товара с дополнительной информацией, такой как цвет, размер и тп.
	 * @return array
	 */
	public function getDataForCartButton($entityKey);
}