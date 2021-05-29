<?php
namespace Wdpro\Pay\Methods;

/**
 * https://github.com/blockcypher/php-client
 * 
 * @package Wdpro\Pay\Methods
 */
class Blockcypher extends Base  implements MethodInterface {

  protected static $currencies = [
    "bitcoin" => [
      'id'=>11,
      'NAME'=>'BTC',
      'name'=>'btc',
      'image'=>'bitcoin.png',
    ],
    "ethereum" => [
      'id'=>12,
      'NAME'=>'ETH',
      'name'=>'eth',
      'image'=>'ethereum.png',
    ],  
    "litecoin" => [
      'id'=>14,
      'NAME'=>'LTC',
      'name'=>'ltc',
      'image'=>'litecoin.png',
    ],  
    "dogecoin" => [
      'id'=>15,
      'NAME'=>'DOGE',
      'name'=>'doge',
      'image'=>'dogecoin.png',
    ],
    "dash" => [
      'id'=>16,
      'NAME'=>'DASH',
      'name'=>'dash',
      'image'=>'dash.png',
    ],
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

    \Wdpro\Modules::add(__DIR__.'/../cryptwallets');


    // Get Payment Data
    // https://www.blockcypher.com/dev/bitcoin/?shell#address-forwarding
    // https://www.blockcypher.com/dev/bitcoin/?shell#addressforward (Подробнее)
    wdpro_ajax('blockcypher_get_pay_data', function () {

      try {
        $pay = \Wdpro\Pay\Controller::getPayByGet();
        $amount = $pay->getCost();
        $currency = static::getCurrencyByKey($_GET['currencyKey']);

        $walletAddress = static::getWalletAddress($currency['name']);

        $req = static::request($currency['name'], 'forwards', [
          'destination'=>$walletAddress,
          'callback_url'=>static::getCallbackUrl(),
          'enable_confirmations'=>true,
          'mining_fees_satoshis'=>370, // 10 руб
        ]);

        if ($req['error']) {
          return $req;
        }

        print_r($req);
        
        
        
        // curl -d '{"destination":"15qx9ug952GWGTNn7Uiv6vode4RcGrRemh","callback_url": "https://my.domain.com/callbacks/new-pay"}' https://api.blockcypher.com/v1/btc/main/forwards?token=YOURTOKEN
      }
      catch(\Exception $err) {
        return [
          'error'=>$err->getMessage(),
        ];
      }

      exit();
    });
    
		
		// Result URL
		wdpro_ajax('blockcypher_check', function ($data) {

      // wdpro_post_request();
      
      exit();
		});


    // Create Payment
    wdpro_ajax('blockcypher_craete_payment', function () {

      try {
        $pay = \Wdpro\Pay\Controller::getPayByGet();
        $amount = $pay->getCost();
        $currency = static::getCurrencyByKey($_GET['currencyKey']);

        
        
      }
      catch(\Exception $err) {
        return [
          'error'=>$err->getMessage(),
        ];
      }

      exit();
    });

    
  }


  public static function request($coin, $action='', $postData=null) {

    $postMethod = $postData && count($postData);

    if (static::isTestMode()) {
      if ($coin === 'eth') {
        $coin = 'beth';
      }
      else {
        $coin = 'bcy';
      }

      $url = 'https://api.blockcypher.com/v1/'
      .$coin.'/'
      .'test/';
    }
    else {
      $url = 'https://api.blockcypher.com/v1/'
      .$coin.'/'
      .'main/';
    }
    $url .= $action;

    $url .= '?token='.static::getToken();

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      // CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $postMethod ? 'POST' : 'GET',
      // CURLOPT_HTTPHEADER => array(
      //   'x-api-key: '.trim(static::getApiKey())
      // ),
    ));

    if ($postMethod) {
      $json = json_encode($postData);
      // $headers[] = 'Content-Type: application/json';
      curl_setopt($curl, CURLOPT_HTTPHEADER, 'Content-Type: application/json');
      // curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
      // curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    }

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


  public static function runSite() {
		wdpro_add_script_to_site(__DIR__.'/../templates/blockcypher.js');

    wdpro_on_uri('pay', function () {
      \wdpro_default_file(
        __DIR__.'/../templates/blockcypher.site.soy',
        WDPRO_TEMPLATE_PATH.'soy/blockcypher.site.soy'
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
		\Wdpro\Console\Menu::addSettings('Blockcypher', function ($form) {
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
        'html'=>'<p>Документация: <a href="https://www.blockcypher.com/dev/ethereum/" target="_blank">ETH</a>, <a href="https://www.blockcypher.com/dev/bitcoin/?php" target="_blank">BTC</a></p>'
      ]);

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_token',
        'left'=>'Token',
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


      $form->addHeader('Адреса кошельков');

      foreach (static::$currencies as $currency => $currencyData) {
        $form->add([
          'name'=>'pay_method_' . static::getName() . '_address_'.$currencyData['name'],
          'left'=>$currencyData['NAME'],
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
			__DIR__.'/../templates/blockcypher_block.php',
			WDPRO_TEMPLATE_PATH.'pay_method_blockcypher_block.php'
		);

    // 1 Метод оплаты
    return wdpro_render_php(
      WDPRO_TEMPLATE_PATH.'pay_method_blockcypher_block.php',
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
    return 'Blockcypher';
  }


  public static function getName() {
    return 'blockcypher';
  }


  public static function isCurrencyEnabled($currencyId) {
    return !!wdpro_get_option('pay_method_' . static::getName() . '_currency_'.$currencyId);
  }


  public static function isTestMode() {
    return wdpro_get_option('pay_method_' . static::getName() . '_test');
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


  public static function getToken() {
    return get_option('pay_method_' . static::getName() . '_token');
  }


  public static function getCallbackUrl() {
    return wdpro_ajax_url([
      'action'=>'blockcypher_callback',
    ]);
  }


  public static function getWalletAddress($currency) {
    return get_option('pay_method_' . static::getName() . '_address_'.$currency);
  }
}