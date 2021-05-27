<?php
namespace Wdpro\Pay\Methods;

require_once __DIR__.'/PaykassaPro/paykassa_sci.class.php';

/**
 * Метод оплаты 2checkout
 * 
 * https://www.2checkout.com/
 * 
 * 
 * @package Wdpro\Pay\Methods
 */
class NowPayments extends Base  implements MethodInterface {

  protected static $currencies = [
    "bitcoin" => [
      'id'=>11,
      'name'=>'BTC',
      'image'=>'bitcoin.png',
    ],
    "ethereum" => [
      'id'=>12,
      'name'=>'ETH',
      'image'=>'ethereum.png',
    ],  
    "litecoin" => [
      'id'=>14,
      'name'=>'LTC',
      'image'=>'litecoin.png',
    ],  
    // "dogecoin" => [
    //   'id'=>15,
    //   'name'=>'DOGE',
    //   'image'=>'dogecoin.png',
    // ],
    // "dash" => [
    //   'id'=>16,
    //   'name'=>'DASH',
    //   'image'=>'dash.png',
    // ], 
    // "bitcoincash" => [
    //   'id'=>18,
    //   'name'=>'BCH',
    //   'image'=>'bitcoincash.png',
    // ],  
    // "zcash" => [
    //   'id'=>19,
    //   'name'=>'ZEC',
    //   'image'=>'zcash.png',
    // ],
    // "ripple" => [
    //   'id'=>22,
    //   'name'=>'XRP',
    //   'image'=>'ripple.png',
    // ],   
    // "tron" => [
    //   'id'=>27,
    //   'name'=>'TRX',
    //   'image'=>'tron.png',
    // ],  
    // "stellar" => [
    //   'id'=>28,
    //   'name'=>'XLM',
    //   'image'=>'stellar.png',
    // ],  
    // "binancecoin" => [
    //   'id'=>29,
    //   'name'=>'BNB',
    //   'image'=>'binancecoin.png',
    // ],
  ];

  public static function init() {

    \Wdpro\Modules::add(__DIR__.'/cryptwallets');
    
		
		// Result URL
		wdpro_ajax('nowpayments_check', function ($data) {

      // wdpro_post_request();
      
      exit();
		});


    // Get Payment Data
    wdpro_ajax('nowpayment_get_pay_data', function () {

      try {
        $pay = \Wdpro\Pay\Controller::getPayByGet();
        $amount = $pay->getCost();
        $currency = static::getCurrencyByKey($_GET['currencyKey']);
        
        $req = static::request(
          'https://api.nowpayments.io/v1/estimate'
          .'?amount='.$amount
          .'&currency_from='. mb_strtolower(static::getMainCurrency())
          .'&currency_to='. mb_strtolower($currency['name'])
        );
        $amountCrypt = wdpro_number_no_e($req['estimated_amount']);

        return [
          'amount'=>$amount,
          'amountCrypt'=>$amountCrypt,
          'currency'=>$currency,
        ];
        
      }
      catch(\Exception $err) {
        return [
          'error'=>$err->getMessage(),
        ];
      }

      exit();
    });


    // Create Payment
    wdpro_ajax('nowpayment_craete_payment', function () {

      try {
        $pay = \Wdpro\Pay\Controller::getPayByGet();
        $amount = $pay->getCost();
        $currency = static::getCurrencyByKey($_GET['currencyKey']);

        $data = [
          'price_amount'=>$amount,
          'price_currency'=>mb_strtolower(static::getMainCurrency()),
          'pay_currency'=>mb_strtolower($currency['name']),
          'ipn_callback_url'=>static::getCheckUrl(),
          'order_id'=>$pay->id(),
          'order_description'=>$pay->getMessage(),
        ];

        print_r($data);
        $req = static::request(
          'https://api.nowpayments.io/v1/payment',
          $data
        );

        print_r($req);
        exit();
        
        $req = static::request(
          'https://api.nowpayments.io/v1/estimate'
          .'?amount='.$amount
          .'&currency_from='. mb_strtolower(static::getMainCurrency())
          .'&currency_to='. mb_strtolower($currency['name'])
        );
        $amountCrypt = wdpro_number_no_e($req['estimated_amount']);

        return [
          'amount'=>$amount,
          'amountCrypt'=>$amountCrypt,
          'currency'=>$currency,
        ];
        
      }
      catch(\Exception $err) {
        return [
          'error'=>$err->getMessage(),
        ];
      }

      exit();
    });

    
  }


  public static function request($url, $postData=null) {

    if (static::isTestMode()) {
      $url = str_replace(
        'https://api.nowpayments.io',
        'https://api.sandbox.nowpayments.io',
        $url
      );
    }

    $headers = [
      'x-api-key: '.trim(static::getApiKey()),
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($postData && count($postData)) {
      $headers[] = 'Content-Type: application/json';
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    $result = trim(curl_exec($ch));
    $c_errors = curl_error($ch);
    curl_close($ch);

    $data = json_decode($result, true);

    if (!empty($data['errors'])) {
      print_r($data['errors']);
    }

    if (!empty($data['message'])) {
      throw new \Exception($data['message']);
    }

    return $data;
  }


  public static function runSite() {
		wdpro_add_script_to_site(__DIR__.'/../templates/nowpayments.js');

    wdpro_on_uri('pay', function () {
      \wdpro_default_file(
        __DIR__.'/../templates/nowpayments.site.soy',
        WDPRO_TEMPLATE_PATH.'soy/nowpayments.site.soy'
      );
    });

  }


  /**
	 * Запускается в админке
	 *
	 * В этом методе можно добавиьт например какие-нибудь кнопки в меню админки
	 */
	public static function runConsole() {
		
		// Настройки
		\Wdpro\Console\Menu::addSettings('NowPayments', function ($form) {
      /** @var \Wdpro\Form\Form $form */
      
      $form->add([
				'name'      => 'pay_method_' . static::getName() . '_enabled',
				'right'     => 'Включить метод оплаты',
				'type'      => 'check',
				'autoWidth' => false,
      ]);
      

      /* $form->add([
        'name'      => 'pay_method_' . static::getName() . '_link',
        'top'=>'<a href="https://secure.2checkout.com/cpanel/integration.php" target="_blank">Pay link</a>',
        'type'=>$form::TEXT,
      ]); */

      $form->add([
        'name'      => 'pay_method_' . static::getName() . '_test',
        'right'=>'Test mode',
        'type'=>$form::CHECK,
      ]);

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_main_currency',
        'left'=>'Валюта сайта',
      ]);


      $form->addHeader('Настройки');

      $form->add([
        'type'=>$form::HTML,
        'html'=>'<p><a href="https://documenter.getpostman.com/view/7907941/S1a32n38?version=latest#9998079f-dcc8-4e07-9ac7-3d52f0fd733a" target="_blank">Документация</a></p>'
      ]);

      $form->addHeader('В боевом режиме');

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_api_key',
        'top'=>'API KEY',
      ]);

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_secret_key',
        'top'=>'Secret key',
      ]);

      $form->addHeader('В тестовом режиме');

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_test_api_key',
        'top'=>'API KEY',
      ]);

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_test_secret_key',
        'top'=>'Secret key',
      ]);

      $form->add($form::SUBMIT_SAVE);

      $form->addHeader('Валюты');

      foreach (static::$currencies as $currency => $currencyData) {
        $currencyId = $currencyData['id'];

        $form->add([
          'name'=>'pay_method_' . static::getName() . '_currency_'.$currencyId,
          'right'=>$currency,
          'type'=>$form::CHECK,
        ]);
      }

      $form->add($form::SUBMIT_SAVE);

      return $form;
    });
  }


  /**
	 * Возвращает данные для форм оплаты
	 *
	 * @param \Wdpro\Pay\Entity $pay Транзакция
	 * @return array
	 */
	public static function getBlockData($pay) {
    $data = $pay->getData();

    $target = \wdpro_object_by_key($data['target_key']);
    
    $data['currencies'] = static::getEnabledCurrencies();

    // $data['rates'] = static::getRate();

    return $data;
  }



  public static function getRate($currency=null) {
    // https://currency.paykassa.pro/index.php?currency_in=USD&currency_out=BTC
    
    $url = 'https://currency.paykassa.pro/index.php?currency_in='
      .static::getMainCurrency().'&currency_out=';

    $getRate = function ($currency) use ($url) {
      $url = $url . $currency;
      $json = file_get_contents($url);
      $data = json_decode($json, true);
      
      if (!empty($data['data']['value'])) {
        return $data['data']['value'];
      }
    };

    if ($currency) {
      return $getRate($currency);
    }

    $rates = [];

    foreach(static::getCurrencies() as $currency) {
      if ($rate = $getRate($currency)) {
        $rates[$currency] = $rate;
      }
    }

    return $rates;
  }


  /**
	 * Возвращает форму для начала оплаты
	 *
	 * @param \Wdpro\Pay\Entity $pay Транзакция
	 * @return string
	 */
  public static function getBlock($pay) {
    $data = static::getBlockData($pay);

    \wdpro_default_file(
			__DIR__.'/../templates/nowpayments_block.php',
			WDPRO_TEMPLATE_PATH.'pay_method_nowpayments_block.php'
		);

    // 1 Метод оплаты
    return wdpro_render_php(
      WDPRO_TEMPLATE_PATH.'pay_method_nowpayments_block.php',
      $data
    );
  }

  public static function getCurrencies() {
    return static::$currencies;
  }
  
  public static function getEnabledCurrencies() {
    $currencies = static::getCurrencies();
    $enabledCurrencies = [];

    foreach($currencies as $key => $currency) {
      $currency['key'] = $key;

      if (static::isCurrencyEnabled($currency['id'])) {
        $enabledCurrencies[] = $currency;
      }
    }

    return $enabledCurrencies;
  }

  public static function getLabel() {
    return 'NowPayments';
  }


  public static function getName() {
    return 'nowpayments';
  }


  public static function isCurrencyEnabled($currencyId) {
    return !!wdpro_get_option('pay_method_' . static::getName() . '_currency_'.$currencyId);
  }

  public static function isTestMode() {
    return wdpro_get_option('pay_method_' . static::getName() . '_test');
  }


  public static function getSecretKey() {
    return \wdpro_get_option('pay_method_' . static::getName() . static::getTextSuffix() . '_secret_key');
  }


  public static function getTextSuffix() {
    if (static::isTestMode()) {
      return '_test';
    }

    return '';
  }


  public static function getMainCurrency() {
    return wdpro_get_option('pay_method_' . static::getName() . '_main_currency');
  }


  public static function getCurrencyByKey($key) {
    if (isset(static::$currencies[$key])) {
      return static::$currencies[$key];
    }

    throw new \Exception('Currency '.$key.' not found');
  }


  public static function getWalletId() {
    return get_option('pay_method_' . static::getName() . '_wallet_id');
  }


  public static function getApiKey() {
    return get_option('pay_method_' . static::getName() . static::getTextSuffix() . '_api_key');
  }


  public static function getCheckUrl() {
    return wdpro_ajax_url([
      'action'=>'nowpayments_check',
    ]);
  }
}