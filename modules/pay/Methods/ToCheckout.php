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

      \file_put_contents(__DIR__.'/post/2checkout_ipn', print_r($_POST, 1));

			// Получаем объект оплаты
			if ($pay = \Wdpro\Pay\Controller::getPay($_POST['ORDERNO'])) {
				
				// Получаем данные поля
				$payData = $pay->getData();

				// Тестовый режим
				$testMode = get_option('pay_robokassa_test_mode');
				
				// Проверяем подпиись
				// Создаем проверочную подпись
				$signature = md5(
					$_POST['OutSum'].':'.$pay->id().':'
					. ($testMode ? get_option('pay_robokassa_pass2_demo')
						: get_option('pay_robokassa_pass2'))
				);
				
				// Если сумма и подпись верны
				if ($payData['cost'] == $_POST['OutSum'] 
					&& strtolower($signature) == strtolower($_POST['SignatureValue'])) {
					
					// Сохраняем данные $_POST и $_GET
					$pay->mergeInfo([
						'robokassa_result_data'=>[
							time().'_'.rand(1000, 10000) => [
								'get'=>$_GET,
								'post'=>$_POST,
							]
						]
					]);
					
					// Запускаем оплату
					$pay->confirm('robokassa', 1);
					
					echo('OK'.$_POST['InvId']);
					exit();
				}
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
    $url = $target->get2checkoutUrl();

    if (wdpro_get_option('pay_method_' . static::getName() . '_test')) {
      $url .= '&TEST=1';
    }

    $data['result_url'] = $url;

    return $data;



    
    $url = wdpro_get_option('pay_method_' . static::getName() . '_link');
    $parsedUrl = parse_url($url);
    \parse_str($parsedUrl['query'], $query);

    if (wdpro_get_option('pay_method_' . static::getName() . '_test')) {
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
      \wdpro_get_option('pay_method_' . static::getName() . '_secret_key')
    );

    $queryString = http_build_query($query);
    $url = $parsedUrl['scheme'].'://'
      . $parsedUrl['host']
      . $parsedUrl['path']
      . '?' . $queryString;

    $data['result_url'] = $url;

    return $data;
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

  
}