<?php
namespace Wdpro\Form;

class Controller extends \Wdpro\BaseController {

  public static function loadHtmlFormJs() {
    \wdpro_add_script_to_site(__DIR__.'/html-form.js');
  }
}

return __NAMESPACE__;