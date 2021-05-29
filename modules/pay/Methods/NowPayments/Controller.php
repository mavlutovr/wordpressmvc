<?php
namespace Wdpro\Pay\Methods\NowPayments;

class Controller extends \Wdpro\BaseController {

  protected static $payment;
  

  public static function add($data, $pay) {
    
    $data['pay_id'] = $pay->id();
    $data['person_id'] = $pay->getData('person_id');

    $entity = new Entity($data);
    $entity->save();

    return $entity->getUrl();
    
  }


  public static function runSite() {

    // Default
    wdpro_default_page('cryptpayment', function () {
      return require __DIR__.'/default/page-cryptpayment.php';
    });
    \wdpro_default_file(
      __DIR__.'/default/template-cryptpayment.php',
      WDPRO_TEMPLATE_PATH.'nowpayment-payment.php'
    );


    // Content Init
    wdpro_on_uri('cryptpayment', function ($page) {
      try {
        static::$payment = static::getEntityByHash($_GET['id']);
        static::$payment->updateStatus();
      }
      catch(\Exception $err) {
        wdpro_data('wdpro_title', $err->getMessage());
        wdpro_data('wdpro_h1', $err->getMessage());

        add_filter('wdpro_title', function () use ($err) {
          return $err->getMessage();
        });
        add_filter('wdpro_h1', function () use ($err) {
          return $err->getMessage();
        });
      }
    });


    // Content Run
    wdpro_on_uri_content('cryptpayment', function ($content) {

      if (!empty(static::$payment)) {

        $payment = wdpro_render_php(
          WDPRO_TEMPLATE_PATH.'nowpayment-payment.php',
          static::$payment->getTemplateData()
        );
      }

      else {
        $payment = '';
      }

      wdpro_replace_or_append(
        $content,
        '[payment]',
        $payment
      );

      return $content;
    });
  }


  public static function getEntityByHash($hash) {
    if ($row = SqlTable::getRow([
      'WHERE hash=%s ORDER BY id DESC LIMIT 1',
      [$hash]
    ])) {
      return Entity::instance($row);
    }

    throw new \Exception('Can\'t find the payment');
  }
}

return __NAMESPACE__;