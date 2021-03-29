<?php
namespace Wdpro\Extra\InputMask;

class Controller extends \Wdpro\BaseController {

  public static function runSite() {
    \wdpro_add_script_to_site(__DIR__.'/inputmask.min.js');
    \wdpro_add_script_to_site(__DIR__.'/jquery.inputmask.min.js');
    \wdpro_add_script_to_site(__DIR__.'/jquery.inputmask-multi.js');

    if (!is_dir(WDPRO_TEMPLATE_PATH.'data'))
      mkdir(WDPRO_TEMPLATE_PATH.'data');
    \wdpro_default_file(
      __DIR__.'/phones-ru.json',
      WDPRO_TEMPLATE_PATH.'data/phones-ru.json'
    );
  }
}

return __NAMESPACE__;
