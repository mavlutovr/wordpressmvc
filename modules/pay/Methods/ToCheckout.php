<?php
namespace Wdpro\Pay\Methods;

/**
 * Метод оплаты 2checkout
 * 
 * https://www.2checkout.com/
 * 
 * 
 * @package Wdpro\Pay\Methods
 */
class ToCheckout extends Base  implements MethodInterface {


  public static function init() {
    
		
		// Result URL
		wdpro_ajax('2checkout_ipn', function ($data) {

      $data = $_POST;
      $cacheFileName = __DIR__.'/post/2checkout_ipn';

      // TODO: Выключить
      // TODO: Выключить
      // TODO: Выключить
      // TODO: Выключить
      // TODO: Выключить
      // TODO: Выключить
      // TODO: Выключить
      // TODO: Выключить
      // TODO: Выключить
      // TODO: Выключить
      // TODO: Выключить
      if (false && !empty($_GET['tridodo_repeat'])) {
        $json = \file_get_contents($cacheFileName);
        $data = \json_decode($json, 1);

        ini_set('display_errors', 'on');
        \error_reporting(7);
      }

      else {
        if (static::isTestMode()) {
          \file_put_contents($cacheFileName, json_encode($_POST, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        }
        else {
          if (\file_exists($cacheFileName)) {
            @\unlink($cacheFileName);
          }
        }
      }


      if (!empty($data['ORIGINAL_REFNOEXT'][0])) {
        $pay = \Wdpro\Pay\Controller::getPay($data['ORIGINAL_REFNOEXT'][0]);
        $renew = true;
      }
      else {
        $pay = \Wdpro\Pay\Controller::getPay($data['REFNOEXT']);
        $renew = false;
      }


			// Получаем объект оплаты
			if ($pay) {
				
				// Получаем данные оплаты
				$payData = $pay->getData();

        // Проверка подписи
        if (!static::checkRequestHash($data)) {
          echo 'Hash check error.';
          exit();
        }
				
				// Сохраняем данные $_POST и $_GET
					$pay->mergeInfo([
						'2checkout_result_data'=>[
							time().'_'.rand(1000, 10000) => [
								'get'=>$_GET,
								'post'=>$data,
							]
						]
          ]);
					
          // Запускаем оплату
          if ($renew) {
            $renewDate = date_parse($data['IPN_LICENSE_EXP'][0]);
            $renewTime = \mktime(
              $renewDate['hour'],
              $renewDate['minute'],
              $renewDate['second'],
              $renewDate['month'],
              $renewDate['day'],
              $renewDate['year']
            );
            $pay->update(['until'=>$renewTime]);
          }
          else {
            $pay->confirm('2checkout', 1);
          }

          $date = date('YmdHis');
          $hash = static::getReturnHash($data, $date);
          
          echo '<EPAYMENT>'.$date.'|'.$hash.'</EPAYMENT>';
					exit();
			}
		});
  }


  public static function runSite() {

  }


  /**
	 * Запускается в админке
	 *
	 * В этом методе можно добавиьт например какие-нибудь кнопки в меню админки
	 */
	public static function runConsole() {
		
		// Настройки
		\Wdpro\Console\Menu::addSettings('2checkout', function ($form) {
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
        'name'=>'pay_method_' . static::getName() . '_secret_key',
        'top'=>'<a href="https://secure.2checkout.com/cpanel/webhooks_api.php" target="_blank">Secret Key</a>',
      ]);


      $ipnUrl = wdpro_ajax_url([
				'action'=>'2checkout_ipn',
			]);

      $form->add(array(
				'type'=>'html',
				'html'=>'
				<p><b>Адрес сайта</b><br>'.home_url().'</p>
				
				<p><b>IPN URL</b><BR>
				'.$ipnUrl.'<BR>
				
				
				<p><b>Redirect URL</b><BR>
				'.home_url().'/aftersale
				</p>',
			));

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
    $url = $target->get2checkoutUrl($pay);
    $url .= '&REF='.$pay->id();

    //$url .= '&SHOPPER_REFERENCE_NUMBER='.$pay->id();

    $data['result_url'] = $url;

    return $data;



    
    $url = wdpro_get_option('pay_method_' . static::getName() . '_link');
    $parsedUrl = parse_url($url);
    \parse_str($parsedUrl['query'], $query);

    if (static::isTestMode()) {
      $query['TEST'] = 1;
    }

    $target = \wdpro_object_by_key($data['target_key']);
    $query = $target->update2checkooutQuery($query);

    if (!empty($data['person_id'])) {
      $query['CUSTOMERID'] = $data['person_id'];
    }

    $query['PLNKEXP'] = time() + 60*60;
    $query['PLNKID'] = $data['id'];


    // https://knowledgecenter.2checkout.com/Documentation/07Commerce/Checkout-links-and-options/Buy-Link-parameters
    // 129PRODS=123456&QTY=1&OPTIONS123456=option1,option2&PRICES123456[EUR]=10&PRICES123456[USD]=11.5&PLNKEXP=1286532283&PLNKID=4A4681F0E5
    // $query['PHASH'] = 
    $hashQuery = '';
    $addToHashQuery = function ($keyReg) use (&$hashQuery, $query) {
      if (strstr($keyReg, '~')) {
        
        foreach($query as $key => $value) {
          if (\preg_match($keyReg, $key)) {
            if ($hashQuery) $hashQuery .= '&';
            $hashQuery .= $key;

            if (is_array($value)) {
              foreach($value as $vkey => $vvalue) {
                $hashQuery .= '['.$vkey.']='.$vvalue;
              }
            }

            else {
              $hashQuery .= '='.$value;
            }
          }
        }
      }

      else {
        if ($hashQuery) $hashQuery .= '&';
        $hashQuery .= $keyReg . '=';

        if (isset($query[$keyReg])) {
          $hashQuery .= $query[$keyReg];
        }
      }
    };
    
    $addToHashQuery('PRODS');
    $addToHashQuery('QTY');
    $addToHashQuery('~OPTIONS([0-9]+)~');
    $addToHashQuery('~PRICES([0-9]+)~');
    $addToHashQuery('PLNKEXP');
    $addToHashQuery('PLNKID');

    $hashQuery = strlen($hashQuery).$hashQuery;
    $query['PHASH'] = hash_hmac(
      'md5',
      $hashQuery,
      static::getSecretKey()
    );

    $queryString = http_build_query($query);
    $url = $parsedUrl['scheme'].'://'
      . $parsedUrl['host']
      . $parsedUrl['path']
      . '?' . $queryString;

    $data['result_url'] = $url;

    return $data;
  }


  public static function getLinkForOnTheFlyPrice($params) {

    if (!empty($params['url'])) {
      $url = $params['url'];
    }
    else {
      $url = wdpro_get_option('pay_method_' . static::getName() . '_link');
    }

    if (empty($params['CURRENCY'])) $params['CURRENCY'] = 'USD';
    if (empty($params['PLNKEXP'])) $params['PLNKEXP'] = time() + 60*60;
    if (empty($params['PLNKID'])) $params['PLNKID'] = \wdpro_generate_password();

    $parsedUrl = parse_url($url);
    \parse_str($parsedUrl['query'], $query);

    if (!empty($params['test']) || static::isTestMode()) {
      $query['DOTEST'] = 1;
    }

    if (!empty($params['person_id'])) {
      $query['CUSTOMERID'] = $params['person_id'];
    }

    $query['PLNKEXP'] = time() + 60*60;
    $query['PLNKID'] = $params['PLNKID'];

    // PRODS=32545814&QTY=1&CART=1&CARD=1&CURRENCY=USD&ORDERSTYLE=nLWsm5XPnLo=&PRICES32545814[USD]=10&PLNKID=Y9MSMKONY7&PHASH=5dec0c438213cd718073c7e3d8c370a2
    $query['PRODS'] = '';
    foreach($params['products'] as $product) {
      if ($query['PRODS']) $query['PRODS'] .= ',';
      $query['PRODS'] .= $product['id'];

      $query['PRICES'.$product['id'].'['.$params['CURRENCY'].']'] = $product['price'];
    }



    // https://knowledgecenter.2checkout.com/Documentation/07Commerce/Checkout-links-and-options/Buy-Link-parameters
    // 129PRODS=123456&QTY=1&OPTIONS123456=option1,option2&PRICES123456[EUR]=10&PRICES123456[USD]=11.5&PLNKEXP=1286532283&PLNKID=4A4681F0E5
    $hashQuery = '';
    $addToHashQuery = function ($keyReg) use (&$hashQuery, $query) {
      if (strstr($keyReg, '~')) {
        
        foreach($query as $key => $value) {
          if (\preg_match($keyReg, $key)) {
            if ($hashQuery) $hashQuery .= '&';
            $hashQuery .= $key;

            if (is_array($value)) {
              foreach($value as $vkey => $vvalue) {
                $hashQuery .= '['.$vkey.']='.$vvalue;
              }
            }

            else {
              $hashQuery .= '='.$value;
            }
          }
        }
      }

      else {
        if ($hashQuery) $hashQuery .= '&';
        $hashQuery .= $keyReg . '=';

        if (isset($query[$keyReg])) {
          $hashQuery .= $query[$keyReg];
        }
      }
    };
    
    $addToHashQuery('PRODS');
    $addToHashQuery('QTY');
    $addToHashQuery('~OPTIONS([0-9]+)~');
    $addToHashQuery('~PRICES([0-9]+)~');
    $addToHashQuery('PLNKEXP');
    $addToHashQuery('PLNKID');

    $hashQuery = strlen($hashQuery).$hashQuery;
    $query['PHASH'] = hash_hmac(
      'md5',
      $hashQuery,
      static::getSecretKey()
    );

    $queryString = http_build_query($query);
    $url = $parsedUrl['scheme'].'://'
      . $parsedUrl['host']
      . $parsedUrl['path']
      . '?' . $queryString;

    return $url;
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
			__DIR__.'/../templates/2checkout_block.php',
			WDPRO_TEMPLATE_PATH.'pay_method_2checkout_block.php'
		);

    // 1 Метод оплаты
    return wdpro_render_php(
      WDPRO_TEMPLATE_PATH.'pay_method_2checkout_block.php',
      $data
    );
  }
  

  public static function getLabel() {
    return '2checkout';
  }


  public static function getName() {
    return '2checkout';
  }


  public static function isTestMode() {
    return wdpro_get_option('pay_method_' . static::getName() . '_test');
  }


  public static function getSecretKey() {
    return \wdpro_get_option('pay_method_' . static::getName() . '_secret_key');
  }


  public static function checkRequestHash($parameters) {
    // https://knowledgecenter.2checkout.com/API-Integration/Webhooks/06Instant_Payment_Notification_(IPN)/Calculate-the-IPN-HASH-signature

    $IPN_parameters = $parameters;
    unset($IPN_parameters['HASH']);

    
    $result = '';
    foreach ($IPN_parameters as $key => $val){
        $result .= static::ArrayExpand((array)$val);
    }

    $hash =  static::hmac($result);

    return $hash === $parameters['HASH'];
  }


  public static function getReturnHash($IPN_parameters, $date) {
    // https://knowledgecenter.2checkout.com/API-Integration/Webhooks/06Instant_Payment_Notification_(IPN)/Read-receipt-response-for-2Checkout
    $IPN_parameters_response = [];
    $IPN_parameters_response['IPN_PID'][0] = $IPN_parameters['IPN_PID'][0];
    $IPN_parameters_response['IPN_PNAME'][0] = $IPN_parameters['IPN_PNAME'][0];
    $IPN_parameters_response['IPN_DATE'] = $IPN_parameters['IPN_DATE'];
    $IPN_parameters_response['DATE'] = $date;

    $result_response = '';
    foreach ($IPN_parameters_response as $key => $val){
      $result_response .= static::ArrayExpand((array)$val);
    }
    $hash =  static::hmac($secret_key, $result_response);
    return $hash;
  }


  public static function ArrayExpand($array){
    $retval = "";
                foreach($array as $i => $value){
                                if(is_array($value)){
                                                $retval .= static::ArrayExpand($value);
                                }
                                else{
                                                $size        = strlen($value);
                                                $retval    .= $size.$value;
                                }
                }    
    return $retval;
  }


  public static function hmac ($data){
    $key = static::getSecretKey();

    $b = 64; // byte length for md5
    if (strlen($key) > $b) {
        $key = pack("H*",md5($key));
    }
    $key  = str_pad($key, $b, chr(0x00));
    $ipad = str_pad('', $b, chr(0x36));
    $opad = str_pad('', $b, chr(0x5c));
    $k_ipad = $key ^ $ipad ;
    $k_opad = $key ^ $opad;
    return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
  }
  
}