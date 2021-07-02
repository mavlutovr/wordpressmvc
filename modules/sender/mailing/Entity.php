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
      $this->sendToTarget($target);
    }
    catch (\Exception $err) {
      $this->data['status'] = 'completed';

      // Рассылка уведомления об окончании
      $targets = $this->getTestTargets();
      foreach($targets as $target) {
        \Wdpro\Sender\Controller::sendEmail(
          $target->getMailingEmail(),
          'Рассылка завершена',
          '<p><p href="'.home_url().'/wp-admin/admin.php?page=App.Mailing.ConsoleRoll">Посмотреть результаты</p>',
        );
      }
    }
    $this->data['date_updated'] = time();
    $this->save();

  }


  public function sendTest() {
    $targets = $this->getTestTargets();
    
    foreach($targets as $target) {
      $this->sendToTarget($target);
    }
  }


  public function sendToTarget($target) {
    $data = $this->data;


    $email = $target->getMailingEmail();
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

    foreach($data as $key => $value) {
      $data[$key] = wdpro_render_text($value, $templateData);
    }

    \Wdpro\Sender\Controller::sendEmail(
      $email,
      $data['subject'],
      $data['text']
    );
  }

}