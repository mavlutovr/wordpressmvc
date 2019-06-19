<?php
namespace Wdpro\Pay\Methods;

use PayPal\Api\PaymentCard;

/**
 * Яндекс деньги
 *
 * https://money.yandex.ru/i/forms/guide-to-custom-p2p-forms.pdf
 *
 * @package Wdpro\Pay\Methods
 */
class YandexMoney extends Base implements MethodInterface {

	/**
	 * Инициализация метода
	 */
	public static function init()
	{
		//require_once __DIR__.'/YandexMoneySDK/lib/api.php';

		// Проверка оплаты
		wdpro_ajax('yandexMoneyHttpCheck', function () {

			// Секретное слово
			$secret = get_option('yandex.money.secret');

			// Получение данных
			$info = [

				// p2p-incoming - кошелек
				// card-incoming - карта
				'notification_type' => $_POST['notification_type'],

				// ID транзакции (в истории счета получателя)
				'operation_id' => $_POST['operation_id'],

				// Сумма зачисления
				'amount' => $_POST['amount'],

				// Сумма списания
				'withdraw_amount' => $_POST['withdraw_amount'],

				// Код валюты (всегда 643 - рубль)
				'currency' => $_POST['currency'],

				// Дата и время перевода
				'datetime' => $_POST['datetime'],

				// Номер кошелька отправителя
				// Либо пустая строка, когда перевод с карты
				'sender' => $_POST['sender'],

				// Код протекции, когда оплата через кошелек
				'codepro' => $_POST['codepro'],

				// Метка платежа
				'label' => $_POST['label'],

				// Код проверки
				'sha1_hash' => $_POST['sha1_hash'],
			];


			// Объект платежа
			$pay = \Wdpro\Pay\Controller::getById($info['label']);
			if ($pay->loaded()) {

				// Осуществляем проверку
				// Все ОК
				if ($info['sha1_hash'] === sha1(
						$info['notification_type'].'&'.
						$info['operation_id'].'&'.
						$info['amount'].'&'.
						$info['currency'].'&'.
						$info['datetime'].'&'.
						$info['sender'].'&'.
						$info['codepro'].'&'.
						$secret.'&'.
						$info['label']
					)) {

					// Когда сумма оплаты не равна изначальной сумме, которую было предложено оплатить
					$startCost = $pay->getCost();
					if ($startCost != $info['withdraw_amount']) {

						// Обновляем сумму
						$pay->mergeData([
							'cost'=>$info['withdraw_amount'],
						]);

						// Добавляем сообщение об этом
						$info['cost_changed'] = 'Сначала сумма перевода была '.$startCost.' руб. Было оплачено '.$info['withdraw_amount'].' руб.';

					}
					$pay->mergeInfo($info)->setSignature($info['sha1_hash']);
					$pay->confirm(static::getName());
				}

				// Ошибка
				else {

					// Запоминаем ошибку
					$pay->confirm(static::getName(), 0);
				}
			}


		});
	}


	/**
	 * Запускается в админке
	 *
	 * В этом методе можно добавиьт например какие-нибудь кнопки в меню админки
	 */
	public static function runConsole()
	{
		\Wdpro\Console\Menu::addSettings('Яндекс.Деньги', function ($form) {
			/** @var \Wdpro\Form\Form $form */

			$form->add([
				'name'      => 'pay_method_' . static::getName() . '_enabled',
				'right'     => 'Включить метод оплаты',
				'type'      => 'check',
				'autoWidth' => false,
			]);


			// Адрес http уведомлений
			$form->addHeader('Адрес, на который хотите получать уведомления');

			$httpCheckUrl = wdpro_ajax_url([
				'action'=>'yandexMoneyHttpCheck',
			]);

			$form->add([
				'type'=>$form::HTML,
				'html'=>'<p>'.$httpCheckUrl.'</p>
						<p>Указывается здесь <a href="https://money.yandex.ru/myservices/online.xml" target="_blank">https://money.yandex.ru/myservices/online.xml</a></p>',
			]);

			$form->addHeader('Настройки');

			$form->add([
				'name'=>'yandex.money.app_key',
				'top'=>'Ключ приложения',
			]);

			$form->add([
				'name'=>'yandex.money.receiver',
				'top'=>'Номер кошелька в Яндекс.Деньгах',
			]);

			$form->add(array(
				'name'=>'yandex.money.secret',
				'top'=>'Секрет | <a href="https://money.yandex.ru/myservices/online.xml" target="_blank">получить секрет</a>',
			));

			$form->add($form::SUBMIT_SAVE);

			return $form;
		});
	}


	/**
	 * Возвращает ключ приложения
	 *
	 * @return string
	 */
	public static function getAppKey() {
		return get_option('yandex.money.app_key');
	}


	/**
	 * Возвращает номер кошелька
	 *
	 * @return string
	 */
	public static function getReceiver() {
		return get_option('yandex.money.receiver');
	}


	/**
	 * Возвращает данные для форм оплаты яндексом
	 *
	 * @param \Wdpro\Pay\Entity $pay Транзакция
	 * @return array
	 */
	public static function getBlockData($pay) {

		$data = $pay->getData();

		// Ключ приложения
		$data['appKey'] = static::getAppKey();

		// Номер кошелька
		$data['receiver'] = static::getReceiver();

		// Плательщик
		$data['customerNumber'] = $pay->getCustomerNumber();

		// E-mail
		$data['email'] = $pay->getEmail();

		// TargetId
		$data['targetId'] = $pay->target()->id();

		// Адреса страниц
		// Afrersale
		if ($afterSaleUrl = $pay->getAftersaleUrl()) {
			$data['shopSuccessURL'] = $afterSaleUrl;
		}
		else {
			$data['shopSuccessURL'] = get_option('yandex.kassa.shopSuccessURL');
		}
		if (!$data['shopSuccessURL']) {
			$data['shopSuccessURL'] = home_url().'/aftersale';
		}

		// Pay-error
		if ($errorUrl = $pay->getErrorUrl()) {
			$data['shopFailURL'] = $errorUrl;
		}
		else {
			$data['shopFailURL'] = get_option('yandex.kassa.shopFailURL');
		}
		if (!$data['shopFailURL']) {
			$data['shopFailURL'] = home_url().'/pay-error';
		}

		// _GET
		$data['get'] = $_GET;

		return $data;
	}


	/**
	 * Возвращает форму для начала оплаты
	 *
	 * @param \Wdpro\Pay\Entity $pay Транзакция
	 * @return string
	 * @throws Exception
	 */
	public static function getBlock($pay)
	{
		$data = static::getBlockData($pay);

		return wdpro_render_php(
			__DIR__.'/../templates/yandexmoney_block.php',
			$data
		);
	}





	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite()
	{

	}


	/**
	 * Возвращает название метода русскими буквами для использования во всяких текстах
	 *
	 * @return mixed
	 */
	public static function getLabel()
	{
		return 'Яндекс.Деньги';
	}


	/**
	 * Возвращает имя метода оплаты
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function getName() {
		return 'yandex.money';
	}
}