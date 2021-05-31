<?php
namespace Wdpro\Pay\CryptAddresses;

class Controller extends \Wdpro\BaseController {


  public static function getWallet($currency, $creator) {

    // Exists
    if ($row = SqlTable::getRow([
      'WHERE person_id=%d AND currency=%s ORDER BY id DESC LIMIT 1',
      [
        static::getPersonId(),
        $currency,
      ]
    ])) {
      
      return Entity::instance($row);
    }

    // Create
    $data = array(
      'wallet_id'=>static::getWalletId(),
      'sign'=>md5(static::getWalletId().static::getApiKey()),
      'action'=>static::getGetWalletAction($currency),
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.fkwallet.ru/api_v1.php');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = trim(curl_exec($ch));
    $c_errors = curl_error($ch);
    curl_close($ch);

    print_r($data);
    print_r($result); exit();
    $walletData = json_decode($result, true);
    print_r($walletData);

    $date = \DateTime::createFromFormat('Y-m-d H:i:s', $walletData['data']['valid']);
    $time = $date->getTimestamp();

    $wallet = new Entity([
      'currency' => $currency,
      'person_id'=>static::getPersonId(),
      'address'=>$walletData['data']['address'],
      'valid'=>$time,
    ]);

    $wallet->save();

    return $wallet;
  }


  public static function getPersonId() {
    return wdpro_person_auth_id();
  }

  public static function getWalletId() {
    return \Wdpro\Pay\Methods\Fkwallet::getWalletId();
  }

  public static function getApiKey() {
    return \Wdpro\Pay\Methods\Fkwallet::getApiKey();
  }

  public static function getGetWalletAction($currency) {
    return 'get_'. mb_strtolower($currency).'_address';
  }
}


return __NAMESPACE__;