<?php
namespace Wdpro\Pay\CryptAddresses;

/**
 * Основная сущность модуля
 */
class Entity extends \Wdpro\BaseEntity {


  public function getDataForClient() {
    return [
      'id'=>$this->data['id'],
      'address'=>$this->data['address'],
      'valid'=>$this->data['valid'],
    ];
  }
}