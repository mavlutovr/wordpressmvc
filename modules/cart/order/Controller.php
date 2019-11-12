<?php
namespace Wdpro\Cart\Order;

class Controller extends \Wdpro\BaseController {

	/**
	 * @var \Wdpro\Form\Form
	 */
	static $formClass = Form::class;
	static $form;


	/**
	 * Инициализация модуля
	 */
	public static function init()
	{
		\Wdpro\Modules::addWdpro('sender/templates/email');


		// Смена статуса
		wdpro_ajax('console_order_status', function () {

			if (!current_user_can('administrator')) return false;

			$order = Entity::instance($_POST['key']);
			if ($order->loaded()) {

				$order->setStatus($_POST['status'])
					->save();

				return [
					'html' => $order->getConsoleStatus(),
				];
			}
		});
	}


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


		// Карточка заказа - Заголовок
		wdpro_on_uri('order', function ($wpPage) {

			$page = wdpro_get_post_by_id($wpPage->ID);

			$page->setDataForParamsTemplate([
				'order_id'=>$_GET['i'],
			]);

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


				// Отправляем письмо покупателю и админу
				$order->sendFirstEmail();


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
			if (!$order->checkSecret($_GET['se'])) {
				$content = '<p>Ошибка доступа</p>';
				return $content;
			}


			// Обработка шаблона
			$content = $order->getHtmlByTemplate($content);


			// Сообщение
			$message = empty($_GET['done']) ?
				'' :
				wdpro_get_option('wdpro_order_done_message', '
				<h2>Ваш заказ оформлен</h2>
				<p>Мы свяжемся с вами в ближайшее время.</p>
				');

			$content = str_replace(
				'[message]',
				$message,
				$content
			);


			return $content;
		});
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole()
	{
		// Меню
		\Wdpro\Console\Menu::add(ConsoleRoll::class);


		// Настройки
		\Wdpro\Console\Menu::addSettings('Заказы', function ($form) {

			/** @var \Wdpro\Form\Form $form */

			$form->add([
				'name'=>'wdpro_order_done_message',
				'top'=>'Сообщение после успешного оформления заказа',
				'type'=>$form::CKEDITOR,
			]);

			$form->add([
				'name'=>'wdpro_order_admin_email',
				'top'=>'E-mail на который отправлять уведомления о заказах',
			]);

			$form->add($form::SUBMIT_SAVE);

			return $form;
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