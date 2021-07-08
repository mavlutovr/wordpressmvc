<?php
namespace Wdpro\Sender\Mailing;

/**
 * Основная сущность модуля
 */
class Entity extends \Wdpro\BaseEntity {


  public function getTestTargets() {
    throw new \Exception('Необходимо переопределить метод getTestTargets()');
  }


  public function getNextTarget() {
    throw new \Exception('Необходимо переопределить метод getNextTarget()');
  }


  public function getTargetsCountAll() {
    throw new \Exception('Необходимо переопределить метод getTargetsCount()');
  }


  public function start() {
    $this->data['status'] = 'play';
    $this->data['date_started'] = time();
    $this->save();
  }


  public function sendNext() {
    try {
      $target = $this->getNextTarget();
      $this->data['sended_last_id'] = $target->id();
      $this->data['sended_count'] ++;
      $this->send($target);
    }
    catch (\Exception $err) {
      $this->data['status'] = 'completed';

      // Рассылка уведомления об окончании
      $targets = $this->getTestTargets();
      foreach($targets as $target) {
        $this->send(
          $target,
          'Рассылка завершена',
          '<p><a href="'.home_url().'/wp-admin/admin.php?page=App.Mailing.ConsoleRoll">Посмотреть результаты</a>'
        );
      }
    }
    $this->data['date_updated'] = time();
    $this->save();

  }


  public function send($target) {

    $email = $target->getMailingEmail();

    $data = $this->data;

    $templateData = $target->getMailingData($data);
    if (empty($templateData['signature'])) {
      $templateData['signature'] = wdpro_get_option('mailing_signature');
    }
    if (empty($templateData['unsubscribe_link'])) {
      $templateData['unsubscribe_link'] = home_url().'/mailing-unsubscribe?i='.$target->id().'&h='.$target->getMailingHash();
    }
    if (empty($templateData['utm'])) {
      $templateData['utm'] = 'utm_medium=email&utm_term='.$this->id().'_'.$target->id().'_'.$target->getMailingHash();
    }
    if (empty($templateData['pixel_src'])) {
      $templateData['pixel_src'] = wdpro_ajax_url([
        'action'=>'mailing_image',
        'mailing_id'=>$this->id(),
        'target_id'=>$target->id(),
        'target_hash'=>$target->getMailingHash(),
      ]);
    }

    $subject = wdpro_render_text($this->data['subject'], $templateData);

    $html = wdpro_render_php(
      WDPRO_TEMPLATE_PATH.'mailing-template.php',
      [
        'content'=>$this->data['text'],
        'signature'=>wdpro_get_option('mailing_signature'),
      ]
    );
    $html = wdpro_render_text($html, $templateData);

    \Wdpro\Sender\Controller::sendEmail(
      $email,
      $subject,
      $html,
      null, null, null,
      true
    );
  }


  public function sendTest() {
    $targets = $this->getTestTargets();
    
    foreach($targets as $target) {
      $this->send($target);
    }
  }

}