<?php
namespace Wdpro\Cart\Order;

class Controller extends \Wdpro\BaseController {

	/**
	 * @var \Wdpro\Form\Form
	 */
	static $formClass = Form::class;
	static $form;


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

		// Список товаров на странице заказа
		wdpro_default_file(
			__DIR__.'/default/templates/order_goods.php',
			WDPRO_TEMPLATE_PATH.'order_goods.php'
		);

		// Данные, которые заполнил пользователь
		wdpro_default_file(
			__DIR__.'/default/templates/order_card_customer.php',
			WDPRO_TEMPLATE_PATH.'order_card_customer.php'
		);


		// Страницы в админке
		// Оформление заказа
		wdpro_default_page('checkout', function () {
			return require __DIR__.'/default/pages/checkout.php';
		});

		// Карточка заказа
		wdpro_default_page('order', function () {
			return require __DIR__.'/default/pages/order.php';
		});
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite()
	{
		// Страница оформления заказа
		wdpro_on_uri_content('checkout', function ($content) {


			// Форма
			$form = static::getForm();

			// Обработка формы
			$form->onSubmit(function ($data) {

				// Создаем заказ
				$order = new Entity([
					'form' => $data,
				]);

				$order->save();

				\Wdpro\Cart\Controller::moveCartGoodsToOrder($order);

				wdpro_location($order->getUrl().'&done=1');

			});

			// Добавление формы на страницу
			wdpro_replace_or_append(
				$content,
				'[form]',

				$form->getHtml()
			);


			// Товары
			$summaryInfo = \Wdpro\Cart\Controller::getSummaryInfo([
				'extraColls'=>['key'],
			]);
			$summaryInfo = apply_filters('wdpro_checkout_cart_summary_info', $summaryInfo);

			wdpro_replace_or_append(
				$content,
				'[cart]',

				wdpro_render_php(
					WDPRO_TEMPLATE_PATH.'checkout_goods.php',
					$summaryInfo
				)
			);


			return $content;

		});


		// Страница заказа
		wdpro_on_uri_content('order', function ($content, $page) {

			$order = \Wdpro\Cart\Order\Entity::instance($_GET['i']);
			if (!$order->checkSecret($_GET['s'])) {
				$content = '<p>Ошибка доступа</p>';
				return $content;
			}


			// Данные формы оформления
			$content = str_replace(
				'[formData]',
				$order->getCustomerHtml(),
				$content
			);


			// Статус заказа
			$statusData = $order->getStatusData();
			$content = str_replace(
				'[statusText]',
				$statusData['text'],
				$content
			);


			// Товары
			$summaryInfo = \Wdpro\Cart\Controller::getSummaryInfo([
				'orderId' => $order->id(),
				'extraColls'=>['key'],
			]);
			$summaryInfo = apply_filters('wdpro_order_cart_summary_info', $summaryInfo);

			$content = str_replace(
				'[cart]',
				wdpro_render_php(
					WDPRO_TEMPLATE_PATH.'order_goods.php',
					$summaryInfo
				),
				$content
			);


			return $content;
		});
	}


	/**
	 * Установка не стандартной формы оформления заказа
	 *
	 * @param \Wdpro\Form\Form|string $formClass Класс не стандартной формы Form::class
	 */
	public static function setFormClass($formClass) {

		static::$formClass = $formClass;
	}


	/**
	 * Возвращает объект формы
	 *
	 * @return \Wdpro\Form\Form;
	 */
	public static function getForm() {

		if (!isset(static::$form)) {
			$formClass = static::$formClass;
			static::$form = new $formClass();
		}

		return static::$form;
	}


	public static function getFormDataHtml() {

	}

}


return __NAMESPACE__;