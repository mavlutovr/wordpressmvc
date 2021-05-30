<?php
namespace Wdpro\Pay\Methods\NowPayments;

class Entity extends \Wdpro\BaseEntity {

  public function prepareDataForCreate($data) {
    $data['hash'] = wdpro_get_random_hash();
    $data['created'] = strtotime($data['created_at']);
    $data['updated'] = strtotime($data['updated_at']);
    $data['valid_until'] = $data['created'] + 60 * 60 * 24;

    return $data;
  }


  public function getUrl() {
    return home_url().'/cryptpayment?id='.$this->data['hash'];
  }


  public function getValidUntil() {
    return $this->data['valid_until'];
  }


  public function getTemplateData() {
    $data = $this->data;

    $data['order'] = $this->getPay()->getComment();

    $data['query'] = $this->getQuery($data);

    return $data;
  }


  public function getQuery($data) {

    $currency = \Wdpro\Pay\Methods\NowPayments::getCurrencyByName($data['pay_currency']);
    $query = $currency['query'];
    $query = str_replace('[address]', $data['pay_address'], $query);
    $query = str_replace('[amount]', $data['pay_amount'], $query);
    $query = str_replace('[message]', urlencode($data['order']), $query);
    $query = str_replace('[time]', $data['created'], $query);

    return $query;
  }


  public function getPay() {
    return \Wdpro\Pay\Entity::instance($this->data['pay_id']);
  }


  public function getStatus() {
    return $this->data['payment_status'];
  }


  public function updateStatus($req= null) {

    if ($this->data['completed']) return;
    
    try {
      if (!$req) {
        $req = \Wdpro\Pay\Methods\NowPayments::request(
          'https://api.nowpayments.io/v1/payment/'.$this->data['payment_id']
        );
      }

      if (!empty($req['payment_status'])) {
        $this->data['payment_status'] = $req['payment_status'];
        $this->data['updated'] = strtotime($req['updated_at']);

        if ($this->data['payment_status'] === 'finished') {
          $this->getPay()->setResultPost($this->data);
          $this->getPay()->confirm($this->data['pay_currency']);
        }

        if (
          $this->data['payment_status'] === 'finished' ||
          $this->data['payment_status'] === 'failed' ||
          $this->data['payment_status'] === 'refunded' ||
          $this->data['payment_status'] === 'expired'
        ) {

          $this->data['completed'] = 1;
        }

        $this->save();
      }
    }

    catch(\Exception $err) {
      // print_r($err);
      // exit();
    }
  }


  public function getTitle() {
    return mb_strtoupper($this->data['pay_currency'])
      .' invoice #'.$this->data['payment_id'];
  }


  public function isCompleted() {
    return !!$this->data['completed'];
  }
}