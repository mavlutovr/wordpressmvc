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
	 * @param null|array $cartData Данные корзины этого товара
	 * @return float
	 */
	public function getCost($key=null, $cartData=null):float ;


	/**
	 * Возвращает данные для кнопки "Добавить в корзину"
	 *
	 * @param string|array $entityKey Ключ товара с дополнительной информацией, такой как цвет, размер и тп.
	 * @return array
	 */
	public function getDataForCartButton($entityKey);


	/**
	 * Возвращает данные товара для сводных данных о товарах в корзине
	 *
	 * Эти данные так же используются на странице оформления заказа и в карточке заказа
	 *
	 * @return array
	 */
	public function getDataForCartSummaryInfo();


	/**
	 * Возвращает html код для списка товаров заказа в админке
	 *
	 * @param array $cartData Данные корзины из таблицы wdpro_cart
	 * @return string
	 */
	public function getConsoleHtml($cartData);
	
	
	/**
	 * Возвращает стоимость и скидку одного товара при заказе определенного количества
	 * 
	 * @param null|array|string $key Ключ товара, содержащий его параметры
	 * @param null|array $cartData Данные корзины
	 * @return array
	 */
	public function getCostData($key = null, $cartData = null);
}