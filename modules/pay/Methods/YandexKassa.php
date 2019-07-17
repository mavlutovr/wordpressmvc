<?php
namespace Wdpro\Pay\Methods;

use PayPal\Api\PaymentCard;

/**
 * Яндекс касса
 * 
 * https://tech.yandex.ru/money/doc/payment-solution/About-docpage/
 * https://money.yandex.ru/doc.xml?id=527069#2
 * 
 * @package Wdpro\Pay\Methods
 */
class YandexKassa extends Base implements MethodInterface {

	/**
	 * Методы оплаты
	 * 
	 * @var array
	 */
	protected static $methods = [
		'PC' => 'Оплата из кошелька в Яндекс.Деньгах.',
		'AC' => 'Оплата с произвольной банковской карты.',
		'MC' => 'Платеж со счета мобильного телефона.',
		'GP' => 'Оплата наличными через кассы и терминалы.',
		'WM' => 'Оплата из кошелька в системе WebMoney.',
		'SB' => 'Оплата через Сбербанк: оплата по SMS или Сбербанк Онлайн.',
		'MP' => 'Оплата через мобильный терминал (mPOS).',
		'AB' => 'Оплата через Альфа-Клик.',
		'МА' => 'Оплата через MasterPass.',
		'PB' => 'Оплата через Промсвязьбанк.',
		'QW' => 'Оплата через QIWI Wallet.',
		'KV' => 'Оплата через КупиВкредит (Тинькофф Банк).',
	];
	

	/**
	 * Инициализация метода
	 */
	public static function init() {
		
			// Подключение Pop окошек
			\Wdpro\Modules::addWdpro('dialog');

		$md5Source = '';

		// Проверка Md5
		$checkMd5 = function ($action) use (&$md5Source) {
			// MD5:
			// action;orderSumAmount;orderSumCurrencyPaycash;orderSumBankPaycash;shopId;invoiceId;customerNumber;shopPassword
			$md5 = $action;
			$md5 .= ';' . $_POST['orderSumAmount'];
			$md5 .= ';' . $_POST['orderSumCurrencyPaycash'];
			$md5 .= ';' . $_POST['orderSumBankPaycash'];
			$md5 .= ';' . $_POST['shopId'];
			$md5 .= ';' . $_POST['invoiceId']; // ID перевода
			$md5 .= ';' . $_POST['customerNumber'];
			$md5 .= ';' . get_option('yandex.kassa.secret');

			$md5Source = $md5;
			$md5 = md5($md5);
			
			/*file_put_contents(__DIR__.'/post/MD5_'.date('Y.m.d, H:i:s'), 
				print_r($_POST, 1).PHP_EOL
				.$md5Source.PHP_EOL
				.strtolower($md5).' == '.strtolower($_POST['md5']));*/
			
			return strtolower($md5) == strtolower($_POST['md5']);
		};
		



		// Проверка, может ли пользователь платить
		// checkURL
		// https://tech.yandex.ru/money/doc/payment-solution/payment-notifications/payment-notifications-check-docpage/
		wdpro_ajax('yandexCheckURL', function () use (&$checkMd5, &$md5Source) {

			$ok = function () {
				static::response(
					'checkOrder',
					$_POST['invoiceId'],
					0
				);
			};


			if (isset($_POST['orderNumber']) && $_POST['orderNumber'] && $pay = \Wdpro\Pay\Controller::getPay( $_POST['orderNumber'] )) {

				// Md5
				if ($checkMd5('checkOrder')) {
					if (!$pay->data['info']) {
						$pay->data['info'] = [];
					}
					if (!$pay->data['info']['yandex']) {
						$pay->data['info']['yandex'] = [];
					}

					// Повторная проверка
					if ($pay->data['info']['yandex']['invoiceId'] == $_POST['invoiceId']) {

						$ok();
					}

					// Основная проверка
					else if ($_POST['shopId'] == static::getShopId()) {

						$pay->data['info']['yandex']['invoiceId'] = $_POST['invoiceId'];

						$ok();
					}

					// Не верный магазин
					else {
						static::response(
							'checkOrder',
							$_POST['invoiceId'],
							100,
							'Не верно указан ID магазина'
						);
					}
				}

				// Неверный Md5
				else {
					static::response(
						'checkOrder',
						$_POST['invoiceId'],
						100,
						'Неверная подпись md5'.PHP_EOL.PHP_EOL.$md5Source
					);
				}
			}
			
			// Нет такого заказа
			else {
				
				static::response(
					'checkOrder', 
					$_POST['invoiceId'],
					100,
					'Не удалось найти такой заказ #'.$_GET['orderNumber']
				);
			}
		} );
		
		
		// Уведомление об успешно совершенном платеже
		// Здесь заказ делается оплаченным
		// avisoURL
		wdpro_ajax('yandexAvisoURL', function () use (&$checkMd5, &$md5Source) {

			$ok = function () {
				static::response(
					'paymentAviso',
					$_POST['invoiceId'],
					0
				);
			};
			
			file_put_contents(
				__DIR__.'/post/adviso_'.date('Y-m-d, H:i:s'),
				print_r($_POST, 1)
			);

			if (isset($_POST['orderNumber']) && $_POST['orderNumber'] && $pay = \Wdpro\Pay\Controller::getPay( $_POST['orderNumber'] )) {

				if ($checkMd5('paymentAviso')) {
					// Md5
					if (!$pay->data['info']) {
						$pay->data['info'] = [];
					}
					if (!$pay->data['info']['yandex']) {
						$pay->data['info']['yandex'] = [];
					}

					/*if ($pay->data['info']['yandex']['invoiceId'] == $_POST['invoiceId']) {

						$ok();
					}*/

					// Проверка магазина
					if ($_POST['shopId'] == static::getShopId()) {

						// Сохраняем данные $_POST и $_GET
						$pay->mergeInfo([
							'yandex_result_data'=>[
								time().'_'.rand(1000, 10000) => [
									'get'=>$_GET,
									'post'=>$_POST,
								]
							]
						]);
						
						// Подтверждаем платеж
						$pay->confirm(static::getName());

						$ok();
						$message = 'OK';
					}

					// Не верный магазин
					else {
						static::response(
							'paymentAviso',
							$_POST['invoiceId'],
							100,
							'Не верно указан ID магазина'
						);
					}
				}

				// Неверный Md5
				else {
					static::response(
						'paymentAviso',
						$_POST['invoiceId'],
						100,
						'Ошибка Md5'.PHP_EOL.PHP_EOL.$md5Source
					);
				}
			}

			// Нет такого заказа
			else {
				static::response(
					'paymentAviso',
					$_POST['invoiceId'],
					100,
					'Не удалось найти такой заказ'
				);
			}
		});
	}


	/**
	 * Building XML response.
	 * @param  string $functionName  "checkOrder" or "paymentAviso" string
	 * @param  string $invoiceId     transaction number
	 * @param  string $result_code   result code
	 * @param  string $message       error message. May be null.
	 * @return string                prepared XML response
	 */
	private static function response($functionName, $invoiceId, $result_code, $message = 
null) {
		$date = new \DateTime();
		$performedDatetime = $performedDatetime = $date->format("Y-m-d") . "T" . $date->format("H:i:s") . ".000" . $date->format("P");
		
		$response = '<?xml version="1.0" encoding="UTF-8"?><' . $functionName . 'Response performedDatetime="' . $performedDatetime .
			'" code="' . $result_code . '" ' 
			. ($message != null ? 'message="' . $message . '" ' : '') 
			. 'invoiceId="' . $invoiceId . '" shopId="'
			. static::getShopId() . '"/>'.PHP_EOL;
		echo $response;

		$message = str_replace(get_option('yandex.kassa.secret'), '*******', $message);

		if (true || isset($_GET['test'])) {
			if (!is_dir(__DIR__.'/post')) mkdir(__DIR__.'/post');
			file_put_contents(
				__DIR__.'/post/'.$functionName.'_'.date('Y.m.d, H:i:s'),
				print_r($_POST, 1)
				. PHP_EOL . PHP_EOL
				. $message
				. PHP_EOL . PHP_EOL
				. $response
			);
		}
		exit();
	}


	/**
	 * Возвращает ID магазина
	 *
	 * @return string
	 */
	public static function getShopId() {
		if (static::demo()) {
			return get_option( 'yandex.kassa.demo.shop_id' );
		}
		else {
			return get_option( 'yandex.kassa.shop_id' );
		}
	}


	/**
	 * Проверка что это демо режим
	 *
	 * @return bool
	 */
	public static function demo() {
		return get_option('yandex.kassa.demo') == 1;
	}


	/**
	 * Запускается в админке
	 *
	 * В этом методе можно добавиьт например какие-нибудь кнопки в меню админки
	 */
	public static function runConsole() {
		
		// Настройки
		\Wdpro\Console\Menu::addSettings('Yandex.Kassa', function ($form) {
			/** @var \Wdpro\Form\Form $form */

			$form->add([
				'name'      => 'pay_method_' . static::getName() . '_enabled',
				'right'     => 'Включить метод оплаты',
				'type'      => 'check',
				'autoWidth' => false,
			]);

			$form->add($form::SUBMIT_SAVE);

			$form->add(array(
				'name'=>'yandex.kassa.secret',
				'top'=>'ShopPassword',
			));

			// Demo
			$form->add([
				'type'=>$form::HTML,
				'html'=>'<BR>',
			]);
			$form->add(array(
				'name'=>'yandex.kassa.demo',
				'right'=>'<h2 style="margin: 0;">Demo режим</h2>',
				'type'=>'check',
				'autowidth'=>false,
			));
			$form->add(array(
				'name'=>'yandex.kassa.demo.shop_id',
				'top'=>'ID магазина - <a href="http://screenshot3.seobit.ru/roma.2015.12.01___13:28:1448965737.png" target="_blank">SHOP ID</a> (Demo)',
			));
			$form->add(array(
				'name'=>'yandex.kassa.demo.scid',
				'top'=>'Номер витрины Контрагента, выдается Оператором. (Demo)',
			));


			// Боевой
			$form->add([
				'type'=>$form::HTML,
				'html'=>'<h2>Боевой режим</h2>',
			]);
			$form->add(array(
				'name'=>'yandex.kassa.shop_id',
				'top'=>'ID магазина - <a href="http://screenshot3.seobit.ru/roma.2015.12.01___13:28:1448965737.png" target="_blank">SHOP ID</a>',
			));
			$form->add(array(
				'name'=>'yandex.kassa.scid',
				'top'=>'Номер витрины Контрагента, выдается Оператором.',
			));
			
			// Способы оплаты
			$form->add(array(
				'type'=>'html',
				'html'=>'<h2>Способы оплаты</h2>',
			));
			foreach(static::$methods as $methodKey => $methodName) {
				
				$form->add(array(
					'name'=>'yandex.kassa.method.'.$methodKey,
					'type'=>'check',
					'right'=>$methodName,
				));
			}

			// Адреса
			/*$form->add(array(
				'name'=>'yandex.kassa.checkURL',
				'top'=>'checkURL',
			));
			$form->add(array(
				'name'=>'yandex.kassa.avisoURL',
				'top'=>'avisoURL (paymentAvisoURL)',
			));*/
			$form->add(array(
				'type'=>'html',
				'html'=>'<h2>Адреса страниц</h2>',
			));
			$form->add(array(
				'name'=>'yandex.kassa.shopSuccessURL',
				'top'=>'Success Url (URL, на который нужно отправить плательщика в случае успеха перевода)',
				'center'=>'Можно посмотреть снизу или указать здесь',
			));
			$form->add(array(
				'name'=>'yandex.kassa.shopFailURL',
				'top'=>'Fail Url (URL, на который нужно отправить плательщика в случае 
				ошибки оплаты)',
				'center'=>'Можно посмотреть снизу или указать здесь',
			));
			$form->add(array(
				'name'=>'yandex.kassa.shopName',
				'top'=>'Наименование магазина (только для анкеты)',
			));
			$form->add(array(
				'name'=>'yandex.kassa.reestrEmail',
				'top'=>'Email для реестров (только для анкеты)',
			));
			
			
			$form->add('submitSave');

			// Адреса
			$checkURLTest = wdpro_ajax_url([
				'action'=>'yandexCheckURL',
				'test'=>1,
			]);
			$checkURLProd = wdpro_ajax_url([
				'action'=>'yandexCheckURL',
			]);
			$avisoURLTest = wdpro_ajax_url([
				'action'=>'yandexAvisoURL',
				'test'=>1,
			]);
			$avisoURLProd = wdpro_ajax_url([
				'action'=>'yandexAvisoURL',
			]);
			$checkURLTest = str_replace('http://', 'https://', $checkURLTest);
			$checkURLProd = str_replace('http://', 'https://', $checkURLProd);
			$avisoURLTest = str_replace('http://', 'https://', $avisoURLTest);
			$avisoURLProd = str_replace('http://', 'https://', $avisoURLProd);

			$form->add(array(
				'type'=>'html',
				'html'=>'
				<h2>Анкета</h2>
				<p>Наименование магазина: '.get_option('yandex.kassa.shopName').'</p>
				<p>Адрес сайта: '.home_url().'</p>
				<p>CMS: Wordpress + Самописный модуль оплаты через Яндекс Кассу.</p>
				
				<p><b>checkURL</b><BR>
				для продакшн: '.$checkURLProd.'<BR>
				для тестирования: '.$checkURLTest.'</p>
				
				<p><b>paymentAvisoURL</b><BR>
				для продакшн: '.$avisoURLProd.'<BR>
				для тестирования: '.$avisoURLTest.'</p>
				
				<p>Секретное слово: '.get_option('yandex.kassa.secret').'</p>
				
				<p>Порядок перенаправления плательщика 
после завершения платежа<BR>
				Адреса магазина:<BR>
				для продакшн:<BR>
				successURL: '.home_url().'/aftersale<BR>
				failURL: '.home_url().'/pay-error<BR><BR>
				
				для тестирования:<BR>
				successURL: '.home_url().'/aftersale?test=1<BR>
				failURL: '.home_url().'/pay-error?test=1<BR>
				</p>
				
				<p>Email для реестров: '.get_option('yandex.kassa.reestrEmail').'</p>
				
				<!--<p><a href="https://money.yandex.ru/my/tech-integration" target="_blank">Которые настраиваются здесь</a></p>
				<p><b>checkURL:</b> '.$checkURLProd.'</p>
				<p><b>avisoURL:</b> '.$avisoURLProd.'</p>
				<p><b>successURL:</b> '.home_url().'/aftersale</p>
				<p><b>failURL:</b> '.home_url().'/pay-error</p>-->',
			));
			
			return $form;
		});
	}


	/**
	 * Возвращает имя метода оплаты
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function getName() {
		return 'yandex.kassa';
	}


	/**
	 * Возвращает данные для форм оплаты яндексом
	 *
	 * @param \Wdpro\Pay\Entity $pay Транзакция
	 * @return array
	 */
	public static function getBlockData($pay) {

		$data = $pay->getData();

		// ID магазина
		$data['shopId'] = static::getShopId();

		// Demo
		$data['demo'] = get_option('yandex.kassa.demo');
		if ($data['demo']) {

			// Номер витрины
			$data['scid'] = get_option('yandex.kassa.demo.scid');

			// Адрес формы
			$data['action'] = 'https://demomoney.yandex.ru/eshop.xml';
		}

		// Боевой режим
		else {

			// Номер витрины
			$data['scid'] = get_option('yandex.kassa.scid');

			// Адрес формы
			$data['action'] = 'https://money.yandex.ru/eshop.xml';
		}

		// Плательщик
		$data['customerNumber'] = $pay->getCustomerNumber();

		// E-mail
		$data['email'] = $pay->getEmail();

		// Идентификатор товара
		$articleId = get_option('yandex.kassa.shopArticleId');
		if ($articleId) {
			$data['shopArticleId'] = $articleId;
		}

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

		// Методы оплаты
		$data['methods'] = static::getAvailableMethods();
		if (!is_array($data['methods']) || count($data['methods']) == 0) {
			$data['methods'] = 'AC';
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
	 */
	public static function getBlock( $pay ) {
		$data = static::getBlockData($pay);
		
		
		// 1 Метод оплаты
		if (count($data['methods']) == 1) {
			
			foreach($data['methods'] as $method) {
				$data['method'] = $method;
			}

			return wdpro_render_php(
				__DIR__.'/../templates/yandexkassa_block.php',
				$data
			);
		}
		
		// Много методов
		else {

			return wdpro_render_php(
				__DIR__.'/../templates/yandexkassa_block_methods.php',
				$data
			);
		}
	}


	/**
	 * Возвращает форму для начала оплаты
	 *
	 * @param \Wdpro\Pay\Entity $pay Транзакция
	 * @return string
	 */
	static function getBlockMethods($pay) {

		$data = static::getBlockData($pay);


		return wdpro_render_php(
			__DIR__.'/../templates/yandexkassa_block_methods2.php',
			$data
		);
	}


	/**
	 * Возвращает включенные методы оплаты
	 * 
	 * @return array
	 */
	static function getAvailableMethods() {
		
		$methods = [];
		
		foreach(static::$methods as $methodKey=>$methodName) {
			
			if (get_option('yandex.kassa.method.'.$methodKey) == 1) {
				$methods[$methodKey] = $methodName;
			}
		}
		
		return $methods;
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {
		
		wdpro_add_script_to_site(__DIR__.'/../templates/yandex.kassa.js');
		
		// Страница по-умолчанию
		\Wdpro\Page\Controller::defaultPage(
			'pay-yandex-methods',
			function () {
				return require __DIR__.'/../default/yandex-kassa-methods.page.php';
			}
		);
		
		// Страница выбора способа оплаты
		wdpro_on_uri_content('pay-yandex-methods', function ($content) {
			
			if ($pay = \Wdpro\Pay\Controller::getPayByGet()) {
				
				$content = wdpro_replace_or_append(
					$content,
					'[yandex_kassa_methods]',
					static::getBlockMethods($pay)
				);
			}
			
			return $content;
		});
	}


	/**
	 * Возвращает название метода русскими буквами для использования во всяких текстах
	 *
	 * @return mixed
	 */
	public static function getLabel() {
		
		return 'Яндекс.Касса';
	}
}