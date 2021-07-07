<?php
namespace Wdpro\Tools\CookieNotice;

class Controller extends \Wdpro\BaseController {


  public static function runSite() {

    wdpro_default_file(
      __DIR__.'/default/cookieNotice.site.soy',
      WDPRO_TEMPLATE_PATH.'soy/cookieNotice.site.soy'
    );
  }
}

return __NAMESPACE__;