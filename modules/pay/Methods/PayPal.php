<?php

// install
// from this dir:
// git clone https://github.com/paypal/ipn-code-samples.git

namespace Wdpro\Pay\Methods;

class PayPal extends Base  implements MethodInterface {


  public static function init() {

		// IPN Listener
		wdpro_ajax('paypal_ipn', function () {
      
			\Wdpro\AdminNotice\Controller::sendMessageHtml(
				'PayPal.post',
				print_r($_POST, true)
			);

      require __DIR__.'/ipn-code-samples/php/PaypalIPN.php';

      $ipn = new PaypalIPN();

      if (static::isSandbox()) {
        $ipn->useSandbox();
      }

      $verified = $ipn->verifyIPN();
			\file_put_contents(__DIR__.'/PayPal.post2', 'verified: '.$verified);
			\Wdpro\AdminNotice\Controller::sendMessageHtml(
				'PayPal.verified',
				'verified: '.$verified
			);

      if ($verified) {
      }

      // Reply with an empty 200 response to indicate to paypal the IPN was received correctly.
      header("HTTP/1.1 200 OK");
      exit();
		});


		// Get Pay Data (link)
		wdpro_ajax('paypal_get_pay_data', function () {
			try {
				$pay = \Wdpro\Pay\Controller::getPayByGet();

				$amount = [
					'currency_code'=>static::getCurrencyCode(),
					'value'=>$pay->getCost(),
				];
				
				$amount = apply_filters('wdpro_paypal_amount', $amount);
				$amount['value'] = round($amount['value'], 2);

				$description = $pay->getComment();


				$res = static::request(
					'https://api-m.sandbox.paypal.com/v2/checkout/orders',
					[
						'intent'=>'CAPTURE',
						'purchase_units'=>[
							[
								'description'=>$description,
								'amount'=>$amount,
							]
						],

						// https://developer.paypal.com/docs/api/orders/v2/#definition-order_application_context
						'application_contextobject'=>[
							'brand_name'=>static::getBrandName(),
							'return_url'=>static::getReturnUrl($pay),
							'cancel_url'=>static::getCancelUrl($pay),
						],
					]
				);



				print_r($res);
			}
			catch(\Exception $err) {
				return [
					'error'=>$err->getMessage(),
				];
			}
			
		});
  }


	public static function request($url, $postData=null) {

    $url = static::fixUrl($url);
		$token = static::getToken();

    $postMethod = $postData && count($postData);

    // $ch = curl_init();
    $curl = curl_init();
    $options = [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      // CURLOPT_ENCODING => '',
      // CURLOPT_MAXREDIRS => 10,
      // CURLOPT_TIMEOUT => 30,
      // CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_CUSTOMREQUEST => $postMethod ? 'POST' : 'GET',
    ];

    if ($postMethod) {
      $options[CURLOPT_POST] = 1;
      $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
      $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer '.$token;
      $options[CURLOPT_POSTFIELDS] = json_encode($postData);
    }

    curl_setopt_array($curl, $options);

    $result = trim(curl_exec($curl));
    // $c_errors = curl_error($ch);
    curl_close($curl);

    $data = json_decode($result, true);

    if (!empty($data['errors'])) {
      print_r($data['errors']);
    }

    // if (!empty($data['message'])) {
    //   throw new \Exception($data['message']);
    // }

    return $data;
  }


	protected static function getToken() {

		$tokenKey = 'pay_method_' . static::getName() . '_token';
		$tokenTimeKey = 'pay_method_' . static::getName() . '_token_time';
		$token = static::getOption($tokenKey);
		$tokenTime = static::getOption($tokenTimeKey);

		if ($tokenTime >= time() + 60) {
			return $token;
		}

		$url = 'https://api-m.sandbox.paypal.com/v1/oauth2/token';
    $url = static::fixUrl($url);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Accept: application/json',
			'Accept-Language: en_US',
		]);

		$auth =  static::getClientId().':'.static::getSecret();
		curl_setopt($curl, CURLOPT_USERPWD, $auth);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		// curl_setopt($curl, CURLOPT_ENCODING, 'UTF-8');

		$json = trim(curl_exec($curl));
		$res = json_decode($json, true);
		$token = $res['access_token'];
		$tokenTime = $res['expires_in'] + time();
		static::updateOption($tokenKey, $token);
		static::updateOption($tokenTimeKey, $tokenTime);

		return $token;
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

      $form->add([
        'name'=> 'pay_method_' . static::getName() . '_test',
        'right'=>'Test mode',
        'type'=>$form::CHECK,
      ]);


			$form->addHeader('Настройки');

			$form->add([
				'name'=>'pay_method_'.static::getName().'_brand_name',
        'left'=>'Название компании',
			]);
      
      // Код валюты
      $form->add([
        'name'=>'paypal_currency_code',
        'left'=>'<a href="https://developer.paypal.com/docs/api/reference/currency-codes/" traget="_blank">Код валюты</a>',
      ]);

			$form->add($form::SUBMIT_SAVE);


			$form->addHeader('PayPal');
			$form->addHtml('<p><a href="https://developer.paypal.com/developer/applications" target="_blank">Создать приложение</a></p>');
      
      $form->addHeader('Sandbox');

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_receiver_email_test',
				'left'=>'E-mail получателя',
        'bottom'=>'(куда поступают средства)',
      ]);

			$form->add([
        'name'=>'pay_method_' . static::getName() . '_client_id_test',
				'left'=>'Client ID',
			]);

			$form->add([
        'name'=>'pay_method_' . static::getName() . '_secret_test',
				'left'=>'Secret',
			]);


      $form->addHeader('Live');

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_receiver_email_live',
				'left'=>'E-mail получателя',
        'bottom'=>'(куда поступают средства)',
      ]);

			$form->add([
        'name'=>'pay_method_' . static::getName() . '_client_id_live',
				'name'=>'paypal_sandbox_clientid',
				'left'=>'Client ID',
			]);

			$form->add([
        'name'=>'pay_method_' . static::getName() . '_secret_live',
				'left'=>'Secret',
			]);


      $form->addHeader('Страницы');

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

			$form->add([
				'type'=>$form::HTML,
				'html'=>'<p>URL IPN Listener:<BR>'
					.\wdpro_ajax_url([
						'action'=>'paypal_ipn',
					])
					.'</p>',
			]);
			
			$form->add('submitSave');
			
			return $form;
		});
  }
  

  /**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {
		wdpro_on_uri('pay', function () {
			wdpro_add_script_to_site(__DIR__.'/../templates/paypal.js');
		});
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

		$returnUrl = get_option('paypal_return_url');
		if ($returnUrl) return $returnUrl;

		if ($url = $pay->getAftersaleUrl()) {
			return $url;
		}

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
    

    // $target = $pay->target();
    // $data['item_name'] = $pay->getComment();
    // $data['items_quanity'] = 1;
    // $data['button_url'] = static::getButtonUrl();
    // $data['email'] = static::getReceiverEmail();
    // $data['custom_data'] = $pay->getCustomData();

    // $data['return_url'] = static::getReturnUrl($pay);

    // $data['currency_code'] = wdpro_get_option('pay_method_' . static::getName() . '_main_currency', 'USD');

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


  protected static function isSandbox() {
		return static::isTestMode();
    // return get_option('paypal_live_mode') != 1;
  }

	
  public static function isTestMode() {
    return !!wdpro_get_option('pay_method_' . static::getName() . '_test');
  }
  

  protected static function getModeKey() {
    return static::isSandbox() ? 'test' : 'live';
  }


  protected static function getOption($optionName) {
    $modeKey = static::getModeKey();
    return \get_option($optionName.'_'.$modeKey);
  }


  protected static function updateOption($optionName, $value) {
    $modeKey = static::getModeKey();
    return \update_option($optionName.'_'.$modeKey, $value);
  }


	protected static function getReceiverEmail() {
		return static::getOption('pay_method_' . static::getName() . '_receiver_email');
	}


	protected static function getSecret() {
		return static::getOption('pay_method_' . static::getName() . '_secret');
	}


	protected static function getClientId() {
		return static::getOption('pay_method_' . static::getName() . '_client_id');
	}


	protected static function getBrandName() {
		return get_option('pay_method_'.static::getName().'_brand_name');
	}


  protected static function getButtonUrl() {
    return static::isSandbox() ? 'https://www.sandbox.paypal.com/cgi-bin/websc' : '';
  }


	protected static function getCurrencyCode() {
		return get_option('paypal_currency_code');
	}


	public static function fixUrl($url) {
		if (!static::isTestMode()) {
      $url = str_replace(
        'https://api-m.sandbox.paypal.com/',
        'https://api-m.paypal.com/',
        $url
      );
    }

		return $url;
	}


}