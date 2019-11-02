<?php
namespace Wdpro\Cart\Order;

class Controller extends \Wdpro\BaseController {

	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite()
	{
		// Шаблоны по-умолчанию
		// Список товаров на странице оформления заказа
		wdpro_default_file(
			__DIR__.'/default/templates/checkout_goods.php',
			WDPRO_TEMPLATE_PATH.'checkout_goods.php'
		);

		// Страница в админке
		wdpro_default_page('checkout', function () {
			return require __DIR__.'/default/pages/checkout.php';
		});
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite()
	{
		wdpro_on_uri_content('checkout', function ($content) {


			// Товары
			wdpro_replace_or_append(
				$content,
				'[cart]',

				print_r(
					\Wdpro\Cart\Controller::getSummaryInfo(),
					true
				)
			);


			// Форма
			wdpro_replace_or_append(
				$content,
				'[form]',

				'FORM'
			);


			return $content;

		});
	}


}


return __NAMESPACE__;