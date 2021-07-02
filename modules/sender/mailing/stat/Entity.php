<?php
namespace Wdpro\Sender\Mailing\Stat;

/**
 * Основная сущность модуля
 */
class Entity extends \App\BaseEntity {

  public function incrementCount() {

    if (isset($this->data['count'])) {
      $this->data['count'] ++;
    }
    else {
      $this->data['count'] = 1;
    }
    $this->data['updated'] = time();
    $this->data['last_ip'] = $_SERVER['REMOTE_ADDR'];

    return $this;
  }

}