<?php
namespace Wdpro\Pay\Methods;

use PayPal\Api\Amount;
use PayPal\Api\FlowConfig;
use PayPal\Api\InputFields;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Presentation;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\WebProfile;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

/**
 * Способ оплаты PayPal
 * 
 * Статьи по установке
 * http://habrahabr.ru/post/128198/
 * https://habrahabr.ru/company/unitmobile/blog/199370/
 * https://developer.paypal.com/developer/applications/
 * http://stackoverflow.com/questions/28991712/applying-a-paypal-web-pofile-disables-check-out-as-a-guest
 * 
 * @package Wdpro\Pay\Methods
 */
class PayPal extends Base  implements MethodInterface {
	
	use \Wdpro\BaseErrorsStatic;
	
	protected static $_endPoint = [
		'sandbox'=>'https://api-3t.sandbox.paypal.com/nvp',
		'live'=>'https://api-3t.paypal.com/nvp',
	];
	
	protected static $_payUrl = [
		'sandbox'=>'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=',
		'live'=>'https://www.paypal.com/webscr?cmd=_express-checkout&token=',
	];

	/**
	 * Инициализация метода
	 */
	public static function init() {
		
		// Подключение пространства имен PayPal Sdk
		\Wdpro\Autoload::add('PayPal', __DIR__.'/PayPal');
		
		wdpro_add_script_to_site(
			__DIR__.'/../templates/paypal.js'
		);

		// Ajax
		wdpro_ajax('pay_paypal_get_pay_url', function () {

			// Платежка
			$pay = \Wdpro\Pay\Controller::getById($_GET['payId']);
			
			$apiContext = static::getApiContext();


			$brandName = get_option('paypal_presentation_brandname');
			$logo = get_option('paypal_presentation_logo');
			$profileId = null;
			
			if ($brandName || $logo) {
				
				$flow = new FlowConfig();
				$flow->setLandingPageType('Billing');

				$presentation = new Presentation();
				if ($brandName) {
					$presentation->setBrandName($brandName);
				}
				if ($logo) {
					$presentation->setLogoImage(
						WDPRO_UPLOAD_IMAGES_URL.$logo);
				}

				$webProfile = new WebProfile();
				$webProfile->setName('Tridodo' . uniqid())
					->setPresentation($presentation)
				;

				try {
					$createProfileResponse = $webProfile->create($apiContext);
				} catch (\PayPal\Exception\PayPalConnectionException $ex) {
					die($ex);
				}

				$profileId = $createProfileResponse->getId();
			}
			
			
			$payer = new Payer();
			$payer->setPaymentMethod('paypal');
			//$payer->setPaymentMethod('credit_card');
			$amount = new Amount();
			$amount->setCurrency('RUB');
			$amount->setTotal($pay->getCost());
			
			$item1 = new Item();
			$item1->setName( $pay->getText() )
				->setCurrency( 'RUB' )
				->setQuantity( 1 )
				->setPrice( $pay->getCost() );
			
			$itemList = new ItemList();
			$itemList->setItems([$item1]);
			
			$transaction = new Transaction();
			$transaction->setAmount($amount);
			$transaction->setDescription($pay->getText());
			$transaction->setItemList($itemList);
			
			$payment = new Payment();
			$payment->setIntent('sale');
			$payment->setPayer($payer);
			$payment->setTransactions([$transaction]);
			$payment->setNoteToPayer($pay->getText());
			
			$redirectsUrls = new RedirectUrls();
			$redirectsUrls->setCancelUrl(static::getCancelUrl($pay));
			$redirectsUrls->setReturnUrl(static::getReturnUrl($pay));
			$payment->setRedirectUrls($redirectsUrls);

			if ($profileId) {
				$payment->setExperienceProfileId($profileId);
			}

			$payment->create($apiContext);
			
			// Получаем номер платежа, сохраняем его в заказе
			$payId = $payment->getId();
			$pay
				->mergeInfo( [
					static::getName() => [
						'token' => $payId,
						'status'=>'ajaxEnd',
					],
				] )
				->setHashId($payId)
				->save();

			$links = $payment->getLinks();
			foreach ($links as $link) {
				if ($link->getMethod() == 'REDIRECT') {
					return [
						'url'=>$link->getHref()
					];
					/*header('location:'.$link->getHref());
					return [
						'url'=>static::$_payUrl[$modeKey] . urlencode($payId),
					];*/
				}
			}
		});
	}


	/**
	 * Запускается в админке
	 *
	 * В этом методе можно добавиьт например какие-нибудь кнопки в меню админки
	 */
	public static function runConsole() {
		
		\Wdpro\Console\Menu::addSettings('PayPal', function ($form) {
			
			/** @var \Wdpro\Form\Form $form */
			
			$form->add([
				'name'=>'pay_method_'.static::getName().'_enabled',
				'right'=>'Включить метод оплаты',
				'type'=>'check',
				'autoWidth'=>false,
			]);

			$form->add($form::SUBMIT_SAVE);

			$form->add([
				'name'=>'paypal_presentation_brandname',
				'left'=>'Название компании',
			]);
			$form->add([
				'name'=>'paypal_presentation_logo',
				'left'=>'Логотип',
				'type'=>$form::IMAGE,
			]);

			$form->add(array(
				'name'=>'paypal_live_mode',
				'right'=>'Боевой режим (Live)',
				'type'=>'check',
				'autoWidth'=>false,
			));

			$form->addHtml('<p>Надо создать приложение на
				<a href="https://developer.paypal.com/developer/applications/" 
				target="_blank">этой странице</a> 
				и получить его Client ID и Secret.</p>');

			$form->addHtml('<h2>Sandbox</h2>');
			$form->add([
				'name'=>'paypal_sandbox_clientid',
				'left'=>'Client ID',
			]);
			$form->add([
				'name'=>'paypal_sandbox_secret',
				'left'=>'Secret',
			]);

			$form->addHtml('<h2>Live</h2>');
			$form->add([
				'name'=>'paypal_live_clientid',
				'left'=>'Client ID',
			]);
			$form->add([
				'name'=>'paypal_live_secret',
				'left'=>'Secret',
			]);


			/*$form->add(array(
				'type'=>'html',
				'html'=>'<p>Данные ниже можно узнать вот на <a 
				href="https://developer.paypal.com/developer/accounts/" 
				target="_blank">этой странице</a>.<BR>
				Выбираем E-mail, Profile, API Credentials</p>'
			));*/

			$form->add(array(
				'name'=>'paypal_return_url',
				'top'=>'Страница, которая открывается после успешной оплаты',
				'center'=>'По-умолчанию: '.home_url().'/aftersale',
			));
			$form->add(array(
				'name'=>'paypal_cancel_url',
				'top'=>'Страница, которая открывается после НЕ успешной оплаты',
				'center'=>'По-умолчанию: '.home_url().'/pay-error',
			));
			
			$form->add('submitSave');
			
			return $form;
		});
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {
		
		//$url = static::getReturnUrl();
		
		
		// Return Url
		wdpro_on_content(function () {
			if (isset($_GET['paymentId']) && !empty($_GET['paymentId'])
				&& isset($_GET['token']) && isset($_GET['PayerID'])
			) {

				if ($pay = \Wdpro\Pay\Controller::getByHashId($_GET['paymentId'])) {

					$apiContext = static::getApiContext();

					$payment = Payment::get( $_GET['paymentId'], $apiContext );
					$paymentExecution = new PaymentExecution();
					$paymentExecution->setPayerId( $_GET['PayerID'] );
					$payment->execute( $paymentExecution, $apiContext );

					// Удачная оплата
					if ($payment->getState() == 'approved') {
						$pay->mergeInfo([
							static::getName()=>[
								'transactionId' => $payment->getId(),
								'_GET'=>$_GET,
								'status'=>'confirm',
							],
						]);

						// Сохраняем данные $_POST и $_GET
						$pay->mergeInfo([
							'paypal_result_data'=>[
								time().'_'.rand(1000, 10000) => [
									'get'=>$_GET,
									'post'=>$_POST,
								]
							]
						]);

						$pay->confirm(static::getName());
					}

					else {

						$pay->mergeInfo( [
							static::getName() => [
								'status'    => 'error',
								'_GET' => $_GET,
							]
						] )->save();
						wdpro_location( static::getCancelUrl($pay) );
					}
				}
			}
		});
	}


	/**
	 * Возвращает контекст PayPal
	 * 
	 * @return ApiContext
	 */
	public static function getApiContext() {

		// Ключ для получения значений в зависимости от песочницы/боевого режимов
		$modeKey = get_option('paypal_live_mode') == 1 ? 'live' : 'sandbox';

		$apiContext = new ApiContext(
			new OAuthTokenCredential(
				get_option('paypal_'.$modeKey.'_clientid'),
				get_option('paypal_'.$modeKey.'_secret')
			)
		);

		$apiContext->setConfig(['mode'=>$modeKey]);
		
		return $apiContext;
	}


	/**
	 * Сформировываем запрос
	 * 
	 * Взято с http://habrahabr.ru/post/128198/
	 *
	 * @param string $method Данные о вызываемом методе перевода
	 * @param array $params Дополнительные параметры
	 * @return array / boolean Response array / boolean false on failure
	 */
	public static function request($method, $params=[]) {

		// Ключ для получения значений в зависимости от песочницы/боевого режимов
		$modeKey = get_option('paypal_live_mode') == 1 ? 'live' : 'sandbox';
		
		// Параметры нашего запроса
		$requestParams = array(
			'METHOD' => $method,
			'VERSION' => get_option('paypal_version'),
			'USER'=>get_option('paypal_'.$modeKey.'_user'),
			'PWD'=>get_option('paypal_'.$modeKey.'_pwd'),
			'SIGNATURE'=>get_option('paypal_'.$modeKey.'_signature'),
		);

		// Сформировываем данные для NVP
		$request = http_build_query($requestParams + $params);

		// Настраиваем cURL
		$curlOptions = array (
			CURLOPT_URL => static::$_endPoint[$modeKey],
			CURLOPT_VERBOSE => 1,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_CAINFO => __DIR__ . '/cacert.pem', // Файл сертификата
			//CURLOPT_CAINFO => WDPRO_UPLOAD_FILES_URL.get_option('paypal_cert'),
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $request
		);
		
		print_r($curlOptions);

		$ch = curl_init();
		curl_setopt_array($ch,$curlOptions);

		// Отправляем наш запрос, $response будет содержать ответ от API
		$response = curl_exec($ch);

		// Проверяем, нету ли ошибок в инициализации cURL
		if (curl_errno($ch)) {
			static::addError(curl_error($ch));
			curl_close($ch);
			return false;
		} else  {
			curl_close($ch);
			$responseArray = array();
			parse_str($response,$responseArray); // Разбиваем данные, полученные от NVP в массив
			return $responseArray;
		}
	}


	/**
	 * Возвращает имя метода оплаты
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function getName() {
		return 'paypal';
	}


	/**
	 * Возвращает ReturnUrl
	 *
	 * @param \Wdpro\Pay\Entity $pay Транзакция
	 *
	 * @return string
	 */
	public static function getReturnUrl($pay) {

		if ($url = $pay->getAftersaleUrl()) {
			return $url;
		}

		$returnUrl = get_option('paypal_return_url');
		if ($returnUrl) return $returnUrl;

		return home_url().'/aftersale';
	}


	/**
	 * Возвращает CancelUrl
	 *
	 * @param \Wdpro\Pay\Entity $pay Транзакция
	 *
	 * @return string
	 */
	public static function getCancelUrl($pay) {

		if ($url = $pay->getErrorUrl()) {
			return $url;
		}

		$cancelUrl = get_option('paypal_cancel_url');
		if ($cancelUrl) return $cancelUrl;

		return home_url().'/pay-error';
	}


	/**
	 * Возвращает форму для начала оплаты
	 *
	 * @param \Wdpro\Pay\Entity $pay Транзакция
	 * @return string
	 */
	public static function getBlock( $pay ) {

		$data = $pay->getData();
		
		\wdpro_default_file(
			__DIR__.'/../templates/paypal_block.php',
			WDPRO_TEMPLATE_PATH.'pay_method_paypal_block.php'
		);

		return wdpro_render_php(
			WDPRO_TEMPLATE_PATH.'pay_method_paypal_block.php',
			$data
		);
	}


	/**
	 * Возвращает название метода русскими буквами для использования во всяких текстах
	 *
	 * @return mixed
	 */
	public static function getLabel() {

		return 'PayPal';
	}

}