<?php
namespace Wdpro\Extra\QrCodeJs;

class Controller extends \Wdpro\BaseController {

  // public static function runSite() {
  //   wdpro_add_script_to_site(__DIR__.'/qrcode.min.js');
  // }


  public static function requireScript() {
    wdpro_add_script_to_site(__DIR__.'/qrcode.min.js');
  }
}

return __NAMESPACE__;