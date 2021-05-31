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
      'query'=>'bitcoin:[address]?amount=[amount]&message=[message]&time=[time]&exp=86400',
    ],
    "ethereum" => [
      'id'=>12,
      'name'=>'ETH',
      'image'=>'ethereum.png',
      'query'=>'bitcoin:[address]?amount=[amount]&message=[message]&time=[time]&exp=86400',
    ],  
    "litecoin" => [
      'id'=>14,
      'name'=>'LTC',
      'image'=>'litecoin.png',
      'query'=>'litecoin:[address]?amount=[amount]&message=[message]&time=[time]&exp=86400',
    ],  
    "dogecoin" => [
      'id'=>15,
      'name'=>'DOGE',
      'image'=>'dogecoin.png',
      'query'=>'dogecoin:[address]?amount=[amount]&message=[message]&time=[time]&exp=86400',
    ],
    "dash" => [
      'id'=>16,
      'name'=>'DASH',
      'image'=>'dash.png',
      'query'=>'dash:[address]?amount=[amount]&message=[message]&time=[time]&exp=86400',
    ], 
    "bitcoincash" => [
      'id'=>18,
      'name'=>'BCH',
      'image'=>'bitcoincash.png',
      'query'=>'bitcoincash:[address]?amount=[amount]&message=[message]&time=[time]&exp=86400',
    ],  
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


  protected static $mins;


  public static function cron() {
    $last = get_option('nowpayments_last_min_update');

    if (!$last || $last < time() - 60 * 5) {
      static::updateMins();
      update_option('nowpayments_last_min_update', time());
    }
  }


  public static function init() {

    \Wdpro\Modules::add(__DIR__.'/NowPayments');
    \Wdpro\Modules::addWdpro('extra/qrcodejs');


    // Result URL
		wdpro_ajax('nowpayments_check', function () {

      $headers = getallheaders();
      $sig = $headers['x-nowpayments-sig'];

      try {

        // Sign checking
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        ksort($data);
        $hmacJson = json_encode($data);
        $hmacJson = str_replace('\\/', '/', $hmacJson);

        $hmac1 = hash_hmac('sha512', $hmacJson, static::getSecretKey());
        if ($_SERVER['HTTP_X_NOWPAYMENTS_SIG'] !== $hmac1) {
          throw new \Exception('NowPayment Error Check');
        }


        // Pay configming
        $payment = NowPayments\Controller::getEntityByPaymentId($data['payment_id']);
        $payment->updateStatus($data);


        \Wdpro\AdminNotice\Controller::sendMessageHtml(
          'IOctopus / NowPayment Checking Ok ('.$data['payment_status'].')',
          'headers: '.print_r($headers, true).PHP_EOL.PHP_EOL
          .'POST: '.PHP_EOL
          .print_r($_POST, true).PHP_EOL.PHP_EOL
          .'SERVER: '.PHP_EOL
          .print_r($_SERVER, true).PHP_EOL.PHP_EOL
          .$json
        );
      }

      catch(\Exception $err) {
        \Wdpro\AdminNotice\Controller::sendMessageHtml(
          'IOctopus / '.$err->getMessage(),

          // Text
          'headers: '.print_r($headers, true).PHP_EOL.PHP_EOL

          .'POST: '.PHP_EOL
          .print_r($_POST, true).PHP_EOL.PHP_EOL

          .'SERVER: '.PHP_EOL
          .print_r($_SERVER, true).PHP_EOL.PHP_EOL

          .'hmac1: '.PHP_EOL
          .$hmac1.PHP_EOL.PHP_EOL

          .'data: '.PHP_EOL
          .print_r($data, true).PHP_EOL.PHP_EOL

          .'hmacJson: '.PHP_EOL
          .$hmacJson.PHP_EOL.PHP_EOL

          .'json: '.PHP_EOL
          .$json.PHP_EOL.PHP_EOL
        );
      }
        
      exit();
      
		});


    wdpro_on_uri('pay', function () {
      try {
        static::$mins = static::getMins();
      }
      catch(\Exception $err) {
        throw $err;
      }
    });


    // Get Payment Data
    wdpro_ajax('nowpayment_get_pay_data', function () {

      try {
        $pay = \Wdpro\Pay\Controller::getPayByGet();
        $amount = $pay->getCost();
        $currency = static::getCurrencyByKey($_GET['currencyKey']);
        
        $rate = static::request(
          'https://api.nowpayments.io/v1/estimate'
          .'?amount='.$amount
          .'&currency_from='. static::getMainCurrency()
          .'&currency_to='. $currency['name']
        );

        $amountCrypt = static::mainAmountToCurrency($currency['name'], $amount);
        // $amountCrypt = wdpro_number_no_e($rate['estimated_amount']);

        $minAmount = static::getMinForCurrency($currency['name']);

        $error = null;
        if ($amount < $minAmount) {
          $error = 'The minimum amount for '.$currency['name']
          .' is now $'.(round($minAmount*10)/10).'.'
          .PHP_EOL
          .'Please increase the number of months or choose another paying method...';
        }
        

        return [
          'amount'=>$amount,
          'amountCrypt'=>wdpro_number_no_e($amountCrypt),
          'currency'=>$currency,
          'minAmount'=>$minAmount,
          'error'=>$error,
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
        // $amountCrypt = static::mainAmountToCurrency($currency['name'], $amount);

        $req = [
          'price_amount'=>$amount,
          'price_currency'=>mb_strtolower(static::getMainCurrency()),
          'pay_currency'=>mb_strtolower($currency['name']),
          // 'ipn_callback_url'=>static::getCheckUrl(),
          'order_id'=>$pay->id(),
          'order_description'=>$pay->getMessage(),
        ];

        if (!wdpro_local()) {
          $req['ipn_callback_url'] = static::getCheckUrl();
        }

        $res = static::request(
          'https://api.nowpayments.io/v1/payment',
          $req
        );

        $payment = NowPayments\Controller::add($res, $pay);
        $res['url'] = $payment->getUrl();

        
        // Время, до которого можно платить
        $res['valid_until'] = $payment->getValidUntil();
        $res['valid_until_string'] = date('Y-m-d, H:i', $res['valid_until']);


        // Email to user
        $email = $pay->getEmail();
        if ($email) {
          \Wdpro\Sender\Templates\Email\Controller::send(
            'nowpayments_create',
            $email,
            $res
          );
        }
        

        return [
          'url'=>$res['url'],
        ];

        exit();
      }
      catch(\Exception $err) {
        return [
          'error'=>$err->getMessage(),
        ];
      }

      exit();
    });

    
  }


  public static function request($url, $postData=null, $timeout=0) {

    if (static::isTestMode()) {
      $url = str_replace(
        'https://api.nowpayments.io',
        'https://api.sandbox.nowpayments.io',
        $url
      );
    }

    // $headers = [
    //   'x-api-key: '.trim(static::getApiKey()),
    // ];

    $postMethod = $postData && count($postData);

    // $ch = curl_init();
    $curl = curl_init();
    $options = [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => $timeout,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $postMethod ? 'POST' : 'GET',
      CURLOPT_HTTPHEADER => [
        'x-api-key: '.trim(static::getApiKey()),
      ],
    ];

    if ($postMethod) {
      $options[CURLOPT_POST] = 1;
      $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
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

    if (!empty($data['message'])) {
      throw new \Exception($data['message']);
    }

    return $data;
  }


  public static function updateMins() {
    $mins = [];
    $minRatio = static::getMinRatio();


    foreach(static::getEnabledCurrencies() as $currency) {

      $mins[$currency['name']] = static::getMinForCurrency($currency['name']);
    }

    update_option('nowpayments_mins', json_encode($mins));
  }


  public static function getMinForCurrency($currencyName, $rate=null) {

    $minAmount = static::request(
      'https://api.nowpayments.io/v1/min-amount?currency_from='
        .$currencyName
        // .'&currency_to='.$currency['name']
        ,
      null,
      5
    );


    if (!empty($minAmount['min_amount'])) {
      if (!$rate) {
        $url = 'https://api.nowpayments.io/v1/estimate'
          .'?amount='.wdpro_number_no_e($minAmount['min_amount'])
          .'&currency_from='.$currencyName
          .'&currency_to='. static::getMainCurrency();

        $rate = static::request($url, null, 5);
      }

      if (isset($rate['estimated_amount'])) {
        return $rate['estimated_amount'] * static::getMinRatio();
      }
    }

    return '?';
  }


  public static function mainAmountToCurrency($currencyName, $mainAmount) {
    $rate = static::request(
      'https://api.nowpayments.io/v1/estimate'
      .'?amount='.$mainAmount
      .'&currency_from='. static::getMainCurrency()
      .'&currency_to='. $currencyName
    );

    return $rate['estimated_amount'];
  }


  public static function getMins() {
    $minsJson = get_option('nowpayments_mins');
    if ($minsJson) {
      return json_decode($minsJson, true);
    }

    return [];
  }


  public static function runSite() {
		wdpro_add_script_to_site(__DIR__.'/../templates/nowpayments.js');
    \Wdpro\Extra\QrCodeJs\Controller::requireScript();

    wdpro_on_uri('pay', function () {
      \wdpro_default_file(
        __DIR__.'/../templates/nowpayments.site.soy',
        WDPRO_TEMPLATE_PATH.'soy/nowpayments.site.soy'
      );
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

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_min_ratio',
        'left'=>'Множитель минимальных оплат',
      ]);


      $form->addHeader('Старницы');

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_thankyou',
        'left'=>'Успешная оплата',
      ]);

      $form->addHeader('В боевом режиме');

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_api_key',
        'left'=>'API KEY',
      ]);

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_secret_key',
        'left'=>'Secret key',
      ]);

      $form->addHeader('В тестовом режиме');

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_test_api_key',
        'left'=>'API KEY',
      ]);

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_test_secret_key',
        'left'=>'Secret key',
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
    $data['mins'] = static::$mins;

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


  public static function getCurrencyByName($name) {
    foreach(static::$currencies as $key => $currency) {
      if (mb_strtolower($currency['name']) == mb_strtolower($name)) {
        $currency['key'] = $key;
        return $currency;
      }
    }

    throw new \Exception('A currency not found by the name '.$key.'');
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


  public static function getMinRatio() {
    $ratio = get_option(
      'pay_method_' . static::getName() . '_min_ratio'
    );

    if ($ratio) {
      return $ratio * 1;
    }

    return 1;
  }


  public static function getThankYouPageUrl() {
    return get_option('pay_method_' . static::getName() . '_thankyou');
  }
}