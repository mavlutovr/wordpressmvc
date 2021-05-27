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
class PaykassaPro extends Base  implements MethodInterface {

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
    "dogecoin" => [
      'id'=>15,
      'name'=>'DOGE',
      'image'=>'dogecoin.png',
    ],
    "dash" => [
      'id'=>16,
      'name'=>'DASH',
      'image'=>'dash.png',
    ], 
    "bitcoincash" => [
      'id'=>18,
      'name'=>'BCH',
      'image'=>'bitcoincash.png',
    ],  
    "zcash" => [
      'id'=>19,
      'name'=>'ZEC',
      'image'=>'zcash.png',
    ],
    "ripple" => [
      'id'=>22,
      'name'=>'XRP',
      'image'=>'ripple.png',
    ],   
    "tron" => [
      'id'=>27,
      'name'=>'TRX',
      'image'=>'tron.png',
    ],  
    "stellar" => [
      'id'=>28,
      'name'=>'XLM',
      'image'=>'stellar.png',
    ],  
    "binancecoin" => [
      'id'=>29,
      'name'=>'BNB',
      'image'=>'binancecoin.png',
    ],   
  ];

  public static function init() {
    
		
		// Result URL
		wdpro_ajax('paykassapro_process', function ($data) {

      // https://ioctopus.online/wp-admin/admin-ajax.php?action=wdpro&wdproAction=paykassapro_process

      
      $paykassa = static::getPaykassa();
      $res = $paykassa->sci_confirm_order();

      if ($res['error']) {
        \Wdpro\AdminNotice\Controller::sendMessageHtml('Payment Error', $res['message']);
      }

      else {
        try {
          $payId = $res["data"]["order_id"];
          $pay = \Wdpro\Pay\Controller::getById($payId);
          $pay->setResultPost($res);

          $transaction = $res["data"]["transaction"]; // номер транзакции в системе paykassa: 96401
          $hash = $res["data"]["hash"];               // hash, пример: bde834a2f48143f733fcc9684e4ae0212b370d015cf6d3f769c9bc695ab078d1
          $currency = $res["data"]["currency"];       // валюта платежа, пример: DASH
          $system = $res["data"]["system"];           // система, пример: Dash
          $address = $res["data"]["address"];         // адрес криптовалютного кошелька, пример: Xybb9RNvdMx8vq7z24srfr1FQCAFbFGWLg
          $tag = $res["data"]["tag"];                 // Tag для Ripple и Stellar
          $partial = $res["data"]["partial"];         // настройка приема недоплаты или переплаты, 'yes' - принимать, 'no' - не принимать
          $amount = (float)$res["data"]["amount"];    // сумма счета, пример: 1.0000000

          // Проверка суммы
          if (!$pay->isValidAmount($amount, $res)) {
            throw new \Exception('The amount '.$amount.' is not equal to '.$pay->getCost());
          }

          $pay->confirm(static::getName(), 1);

          echo $id.'|success';
        }
        catch (\Exception $err) {
          echo $err->getMessage();
          $pay->logError($err->getMessage())->save();
        }
        
      }
      
      exit();
		});


    // Get Link
    wdpro_ajax('paykassa_get_link', function () {
      try {

        $pay = \Wdpro\Pay\Controller::getPayByGet();
        $amount = $pay->getCost();

        $currency = static::getEnabledCurrencyByKey($_GET['currencyKey']);
        
        $rate = static::getRate($currency['name']);
        $amountCrypt = $amount * $rate;

        $paykassa = static::getPaykassa();

        $res = $paykassa->sci_create_order(
          $amountCrypt,
          $currency['name'],
          // static::getMainCurrency(),
          $pay->id(),
          "IOctopus / Completed",
          $currency['id']
        );

        if ($res['error']) {
          throw new \Exception($res['error']);
        }

        return [
          'currency'=>$currency,
          'amount'=>$amount,
          // 'amountCrypt'=>''.sprintf ("%.16f", $amountCrypt),
          'amountCrypt'=>$amountCrypt,
          'url'=>$res['data']['url'],
        ];

        print_r([
          $amountCrypt,
          $currency['name'],
          // static::getMainCurrency(),
          $pay->id(),
          "IOctopus / Completed",
          $currency['id']
        ]);

        print_r($res);
      }
      catch (\Exception $err) {
        return [
          'error'=>$err->getMessage(),
        ];
      }

      exit();
    });
  }


  public static function runSite() {
		wdpro_add_script_to_site(__DIR__.'/../templates/paykassa.js');

    wdpro_on_uri('pay', function () {
      \wdpro_default_file(
        __DIR__.'/../templates/paykassa.site.soy',
        WDPRO_TEMPLATE_PATH.'soy/paykassa.site.soy'
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
		\Wdpro\Console\Menu::addSettings('PayKassa Pro', function ($form) {
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
        'left'=>'Валюта цен',
      ]);


      $form->addHeader('Настройки');

      $form->add([
        'type'=>$form::HTML,
        'html'=>'<p><a href="https://paykassa.pro/ru/user/shops/" target="_blank">Open settings</a></p>'
      ]);

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_id',
        'top'=>'ID',
      ]);


      $form->add([
        'name'=>'pay_method_' . static::getName() . '_secret_key',
        'top'=>'Secret Key',
      ]);

      $form->add($form::SUBMIT_SAVE);


      $ipnUrl = wdpro_ajax_url([
				'action'=>'paykassapro_process',
			]);

      $form->add(array(
				'type'=>'html',
				'html'=>'
				<p><b>Адрес сайта</b><br>'.home_url().'</p>
				
				<p><b>URL уведомлений</b><BR>
				'.$ipnUrl.'</p>
				
				
				<p><b>URL успешной оплаты</b><BR>
				'.home_url().'/aftersale
				</p>
				
				
				<p><b>URL неудачной оплаты</b><BR>
				'.home_url().'/pay-error
				</p>',
			));

      $form->addHeader('Валюты');

      foreach (static::$currencies as $currency => $currencyData) {
        $currencyId = $currencyData['id'];

        $form->add([
          'name'=>'pay_method_' . static::getName() . '_currency_'.$currencyId,
          'right'=>$currency,
          'type'=>$form::CHECK,
        ]);

        // $form->add([
        //   'name'=>'pay_method_' . static::getName() . '_currency_logo_'.$currencyId,
        //   'type'=>$form::IMAGE,
        //   'left'=>'Лого '.$currency,
        //   'resize'=>[
        //     [ 'width'=>100 ],
        //   ],
        //   'style'=>'margin-bottom: 30px;',
        // ]);
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


  public static function getPaykassa() {
    return new \PayKassaSCI( 
      static::getShopId(),       // идентификатор магазина
      static::getShopSecretKey()  // пароль магазина
    );
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
      if ($rate = $getRate($currency['name'])) {
        $rates[$currency['name']] = $rate;
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
			__DIR__.'/../templates/paykassa_block.php',
			WDPRO_TEMPLATE_PATH.'pay_method_paykassa_block.php'
		);

    // 1 Метод оплаты
    return wdpro_render_php(
      WDPRO_TEMPLATE_PATH.'pay_method_paykassa_block.php',
      $data
    );
  }
  

  public static function getLabel() {
    return 'PaykassaPro';
  }


  public static function getName() {
    return 'paykassapro';
  }


  public static function isTestMode() {
    return wdpro_get_option('pay_method_' . static::getName() . '_test');
  }


  public static function getSecretKey() {
    return \wdpro_get_option('pay_method_' . static::getName() . '_secret_key');
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


  public static function getEnabledCurrencyByKey($key) {
    if (empty(static::$currencies[$key])) {
      throw new \Exception('Currency not found');
    }

    $currency = static::$currencies[$key];
    if (!static::isCurrencyEnabled($currency['id'])) {
      throw new \Exception('Currency is disabled');
    }

    return $currency;
  }

  public static function getCurrensyByName($name) {
    foreach(static::$currencies as $key => $currency) {
      if ($currency['name'] === $name) {
        $currency['key'] = $key;
        return $currency;
      }
    }

    throw new \Exception('Currency "'.$name.'" not found');
  }


  public static function isCurrencyEnabled($currencyId) {
    return !!wdpro_get_option('pay_method_' . static::getName() . '_currency_'.$currencyId);
  }


  public static function getMainCurrency() {
    return wdpro_get_option('pay_method_' . static::getName() . '_main_currency');
  }


  protected static function getShopId() {
    return get_option('pay_method_' . static::getName() . '_id');
  }

  protected static function getShopSecretKey() {
    return get_option('pay_method_' . static::getName() . '_secret_key');
  }
}