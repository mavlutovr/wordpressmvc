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
class Fkwallet extends Base  implements MethodInterface {

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

    \Wdpro\Modules::addWdpro('pay/Methods/Fkwallet/wallets');
    
		
		// Result URL
		wdpro_ajax('fkwallet_check', function ($data) {

      
      exit();
		});


    // Get Wallet Info
    wdpro_ajax('fkwallet_open_wallet', function () {
      try {
        $currency = static::getCurrencyByKey($_GET['currencyKey']);
        $pay = \Wdpro\Pay\Controller::getPayByGet();
        $wallet = static::getWallet($currency['name']);
        $rate = static::getRate($currency['name']);

        $amount = $pay->getCost();
        $amountCrypt = $amount * $rate;

        return [
          'amount'=>$amount,
          'amountCrypt'=>$amountCrypt,
          'wallet'=>$wallet->getDataForClient(),
        ];
      }

      catch (\Exception $err) {
        return [
          'error'=>$err->getMessage(),
        ];
      }
    });
  }


  public static function runSite() {
		wdpro_add_script_to_site(__DIR__.'/../templates/kwallet.js');

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
		\Wdpro\Console\Menu::addSettings('Kwallet', function ($form) {
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
        'html'=>'<p><a href="https://www.fkwallet.ru/docs" target="_blank">Документация</a></p>'
      ]);

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_api_key',
        'top'=>'API KEY',
      ]);

      $form->add([
        'name'=>'pay_method_' . static::getName() . '_wallet_id',
        'top'=>'<a href="https://i.imgur.com/nctkrJW.png" target="_blank">Номер кошелька</a>',
      ]);

      $form->add($form::SUBMIT_SAVE);


      $ipnUrl = wdpro_ajax_url([
				'action'=>'fkwallet_check',
			]);

      $form->add(array(
				'type'=>'html',
				'html'=>'
				<p><b>Адрес сайта</b><br>'.home_url().'</p>
				
				<p><b>URL ДЛЯ КРИПТОВАЛЮТ УВЕДОМЛЕНИЙ</b><BR>
				'.$ipnUrl.'</p>
				
				',
			));

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
			__DIR__.'/../templates/fkwallet_block.php',
			WDPRO_TEMPLATE_PATH.'pay_method_fkwallet_block.php'
		);

    // 1 Метод оплаты
    return wdpro_render_php(
      WDPRO_TEMPLATE_PATH.'pay_method_fkwallet_block.php',
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
    return 'Fkwallet';
  }


  public static function getName() {
    return 'fkwallet';
  }


  public static function isCurrencyEnabled($currencyId) {
    return !!wdpro_get_option('pay_method_' . static::getName() . '_currency_'.$currencyId);
  }

  public static function isTestMode() {
    return wdpro_get_option('pay_method_' . static::getName() . '_test');
  }


  public static function getSecretKey() {
    return \wdpro_get_option('pay_method_' . static::getName() . '_secret_key');
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


  public static function getWallet($currency) {
    return \Wdpro\Pay\Methods\Fkwallet\Wallets\Controller::getWallet($currency);
  }


  public static function getWalletId() {
    return get_option('pay_method_' . static::getName() . '_wallet_id');
  }


  public static function getApiKey() {
    return get_option('pay_method_' . static::getName() . '_api_key');
  }
}