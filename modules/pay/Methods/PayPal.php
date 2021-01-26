<?php

// install
// from this dir:
// git clone https://github.com/paypal/ipn-code-samples.git

namespace Wdpro\Pay\Methods;

class PayPal extends Base  implements MethodInterface {


  public static function init() {


		// IPN Listene
		wdpro_ajax('paypal_ipn', function () {
      
      require __DIR__.'/ipn-code-samples/php/PaypalIPN.php';

      $ipn = new PaypalIPN();

      if (static::isSandbox()) {
        \file_put_contents(__DIR__.'/PayPal.post', \json_encode($_POST, JSON_PRETTY_PRINT));
        $ipn->useSandbox();
      }

      $verified = $ipn->verifyIPN();
      if ($verified) {
          
      }

      // Reply with an empty 200 response to indicate to paypal the IPN was received correctly.
      header("HTTP/1.1 200 OK");
      exit();
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

			$form->add(array(
				'name'=>'paypal_live_mode',
				'right'=>'Боевой режим (Live)',
				'type'=>'check',
				'autoWidth'=>false,
			));
      
      // Код валюты
      $form->add([
        'name'=>'paypal_currency_code',
        'top'=>'<a href="https://developer.paypal.com/docs/api/reference/currency-codes/" traget="_blank">Код валюты</a>',
      ]);

			$form->add($form::SUBMIT_SAVE);

			// $form->add([
			// 	'name'=>'paypal_presentation_brandname',
			// 	'left'=>'Название компании',
			// ]);
			// $form->add([
			// 	'name'=>'paypal_presentation_logo',
			// 	'left'=>'Логотип',
			// 	'type'=>$form::IMAGE,
			// ]);

			// $form->addHtml('<p>Надо создать приложение на
			// 	<a href="https://developer.paypal.com/developer/applications/" 
			// 	target="_blank">этой странице</a> 
			// 	и получить его Client ID и Secret.</p>');

			// $form->addHtml('<h2>Sandbox</h2>');
			// $form->add([
			// 	'name'=>'paypal_sandbox_clientid',
			// 	'left'=>'Client ID',
			// ]);
			// $form->add([
			// 	'name'=>'paypal_sandbox_secret',
			// 	'left'=>'Secret',
			// ]);

			// $form->addHtml('<h2>Live</h2>');
			// $form->add([
			// 	'name'=>'paypal_live_clientid',
			// 	'left'=>'Client ID',
			// ]);
			// $form->add([
			// 	'name'=>'paypal_live_secret',
			// 	'left'=>'Secret',
      // ]);
      
      $form->addHeader('Sandbox');

      $form->add([
        'name'=>'paypal_receiver_email_sandbox',
        'top'=>'E-mail получателя (куда поступают средства)',
      ]);
      
      $form->addHeader('Live');

      $form->add([
        'name'=>'paypal_receiver_email_live',
        'top'=>'E-mail получателя (куда поступают средства)',
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
    
    // {"target":{"id":"5","tariff_name":"completed","amount":0.7,"payed":"0","pay_id":"0","payed_time":"0","secret":"605358","person_id":"11","email":"mavlutovr@ya.ru","firstname":"Roman","lastname":"Mavlutov","_from_db":true},"aftersale":"http://localhost/ioctopus.online/aftersale/?i=5&se=605358","pay-error":"http://localhost/ioctopus.online/pay-error/","referer":"http%3A%2F%2Flocalhost%2Fioctopus.online%2Fpricing"}

    $target = $pay->target();
    // $data['item_name'] = $target->getPayItemName();
    $data['item_name'] = $pay->getComment();
    $data['items_quanity'] = 1;
    $data['button_url'] = static::getButtonUrl();
    $data['email'] = static::getOption('paypal_receiver_email');
    $data['custom_data'] = $pay->getCustomData();

    $data['return_url'] = static::getReturnUrl($pay);

    $data['currency_code'] = wdpro_get_option('paypal_currency_code', 'USD');

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
    return get_option('paypal_live_mode') != 1;
  }
  

  protected static function getModeKey() {
    return static::isSandbox() ? 'sandbox' : 'live';
  }


  protected static function getOption($optionName) {
    $modeKey = static::getModeKey();
    return \get_option($optionName.'_'.$modeKey);
  }


  protected static function getButtonUrl() {
    return static::isSandbox() ? 'https://www.sandbox.paypal.com/cgi-bin/websc' : '';
  }
}