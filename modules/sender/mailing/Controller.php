<?php
namespace Wdpro\Sender\Mailing;

class Controller extends \Wdpro\BaseController {

  protected static $statuses = [
    ''=>'Pause',
    'play'=>'Play',
    'completed'=>'Completed',
  ];


  public static function init() {

    \Wdpro\Modules::add(__DIR__.'/stat');

    // Test Letter
    wdpro_ajax('mailing-send-test-letter', function () {
      try {
        $entity = static::getEntityClass()::instance($_GET['id']);
        $entity->sendTest();
        return [
          'ok'=>true,
        ];
      }
      catch(\Exception $err) {
        return [
          'error'=>$err->getMessage(),
        ];
      }
    });


    // Start
    wdpro_ajax('mailing-start', function () {
      try {
        $entity = static::getEntityClass()::instance($_GET['id']);
        $entity->start();
        return [
          'ok'=>true,
        ];
      }
      catch(\Exception $err) {
        return [
          'error'=>$err->getMessage(),
        ];
      }
    });


    // Pixel
    wdpro_ajax('mailing_image', function () {

      if (!empty($_GET['mailing_id']) && !empty($_GET['target_id']) && !empty($_GET['target_hash'])) {

        $target = static::getTargetById($_GET['target_id']);
        if ($target->isCorrectMailingHash($_GET['target_hash'])) {

          $data = [
            'mailing_id'=>(int)$_GET['mailing_id'],
            'target_id'=>(int)$_GET['target_id'],
            'action'=>'view',
          ];

          do_action('mailing-stat', $data);
          Stat\Controller::add($data);
          print_r($data); exit();

          $image = new \Imagick();
          $image->newImage(1, 1, new \ImagickPixel('white'));
          $image->setImageFormat('png');
          header('Content-type: image/png');
          echo $image;
        }
      }

    });
  }


  public static function initSite() {
    if (!empty($_GET['utm_medium']) && $_GET['utm_medium'] === 'email'
      && !empty($_GET['utm_term'] && preg_match('~([0-9]+)_([0-9]+)_([a-z0-9]+)~', $_GET['utm_term'], $arr))
    ) {

      $mailingId = $arr[1];
      $targetId = $arr[2];
      $targetHash = $arr[3];

      $target = static::getTargetById($targetId);
      if ($target->isCorrectMailingHash($targetHash)) {

        $data = [
          'mailing_id'=>$mailingId,
          'target_id'=>$targetId,
          'action'=>'visit',
        ];

        do_action('mailing-stat', $data);
        Stat\Controller::add($data);

      }
    }
  }


  public static function runSite() {

    // print_r($_SERVER);

    // Unsubscribe default page
    wdpro_default_page('mailing-unsubscribe', function () {
			return require __DIR__.'/default/page-unsubscribe.php';
    });

    // Mail Template
    \wdpro_default_file(
      __DIR__.'/default/mailing-template.php',
      WDPRO_TEMPLATE_PATH.'mailing-template.php'
    );


    // Unsubscription
    wdpro_on_uri('mailing-unsubscribe', function () {

      $throwErrorUrl = function () {
        throw new \Exception('Error: Wrong unsubscription url: '.wdpro_current_url(null, true));
      };

      try {
        if (empty($_GET['i'])) {
          $throwErrorUrl();
        }

        $target = static::getTargetById($_GET['i']);
        if (!$target->isCorrectMailingHash($_GET['h'])) {
          $throwErrorUrl();
        }

        $target->mailingUnsubscribe();
      }
      catch(\Exception $err) {
        do_action('mailing-exceprion', $err);

        \Wdpro\AdminNotice\Controller::sendException($err);
        throw $err;
    }
    });
  }


  public static function cron() {
    try {
      set_time_limit(50);
      $tries = static::getSendingCountForOneCron();
      $sleep = static::getSleepBetweenSending();

      for($i = 0; $i < $tries; $i++) {
        if ($i) sleep($sleep);

        $mailing = static::getActiveMailing();
        $mailing->sendNext();
      }
    }
    catch(\Exception $err) {}
  }


  public static function getActiveMailing() {
    if ($row = static::SqlTable()::getRow([
      'WHERE status="play" ORDER BY menu_order LIMIT 1',
    ])) {
      return static::getEntityClass()::instance($row);
    }

    throw new \Exception('Нету активных рассылок');
  }


  public static function getStatuses() {
    return static::$statuses;
  }


  public static function getStatusLabel($status) {
    return static::$statuses[$status];
  }


  public static function getSendingCountForOneCron() {
    return wdpro_get_option('mailing-mails-per-cron', 5);
  }


  public static function getSleepBetweenSending() {
    return wdpro_get_option('mailing-sleep-between-mails', 3);
  }


  public static function getConsoleFormTextInfo() {
    return '
      <p><code>[signature]</code> - Подпись</p>
      <p><code>[unsubscribe_link]</code> - Ссылка для отписки</p>
      <p><code>[utm]</code> - utm для ссылок http://site.ru/page?[utm], для статистики.</p>
      <p><code>[pixel_src]</code> - Адрес пикселя для подсчета открываемости (можно добавить в подпись, работает не точно)</p>
    ';
  }


  public static function getTargetById($id) {
    throw new \Exception('Необходимо переопределить метод getTargetById');
  }


  public static function initSettingsForm($form) {
    $form->add([
      'name'=>'mailing-form-info',
      'top'=>'Информация для форм подписок',
      'type'=>$form::CKEDITOR,
    ]);

    $form->add([
      'name'=>'mailing-mails-per-cron',
      'top'=>'Количество писем, отправляемых за один запуск (раз в минуту)',
    ]);

    $form->add([
      'name'=>'mailing-sleep-between-mails',
      'top'=>'Пауза между письмами (сек)',
    ]);
  }
}


return __NAMESPACE__;