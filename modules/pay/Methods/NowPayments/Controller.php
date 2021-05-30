<?php
namespace Wdpro\Pay\Methods\NowPayments;

class Controller extends \Wdpro\BaseController {

  protected static $payment;
  

  public static function add($data, $pay) {
    
    $data['pay_id'] = $pay->id();
    $data['person_id'] = $pay->getData('person_id');

    $entity = new Entity($data);
    $entity->save();

    return $entity;
    
  }


  public static function init() {

    // Load status (for payment card)
    wdpro_ajax('nowpayments_get_payment_status', function () {

      if (wdpro_local()) {
        sleep(1); // TODO
      }

      $entity = static::getEntityByHash($_GET['id']);
      
      return [
        'status'=>$entity->getStatus(),
        'update'=>!$entity->isCompleted(),
      ];

      exit();
    });
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
        // static::$payment->updateStatus();

        $title = static::$payment->getTitle();

        add_filter('wdpro_title', function () use ($title) {
          return $title;
        });
        add_filter('wdpro_h1', function () use ($title) {
          return $title;
        });
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

    throw new \Exception('Can\'t find the payment by hash '.$hash);
  }


  public static function getEntityByPaymentId($paymentId) {
    if ($row = SqlTable::getRow([
      'WHERE payment_id=%d ORDER BY id DESC LIMIT 1',
      [$paymentId]
    ])) {
      return Entity::instance($row);
    }

    throw new \Exception('Can\'t find the payment by payment_id '.$paymentId);
  }
}

return __NAMESPACE__;