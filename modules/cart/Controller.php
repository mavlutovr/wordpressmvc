<?php
namespace Wdpro\Cart;

class Controller extends \Wdpro\BaseController {

	/**
	 * Возвращает кнопку "Добавить в корзину" для элемента
	 *
	 * @param CartElementInterface|\Wdpro\BasePage $entity Элемент, который можно добавить в корзину по этой кнопке
	 * @return string
	 */
	public static function getAddToCartButton($entity) {

		$templateData = [];

		$templateData['added'] = SqlTable::getRow([
			'WHERE element_key=%s AND order_id=0'
		]);

		$templateData['entity'] = $entity->getData();

		return wdpro_render_php(
			WDPRO_TEMPLATE_PATH.'add_to_cart_button.php',
			$templateData
		);
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite()
	{
		wdpro_default_file(
			__DIR__.'/default/add_to_cart_button.php',
			WDPRO_TEMPLATE_PATH.'add_to_cart_button.php'
		);
	}


}


return __NAMESPACE__;