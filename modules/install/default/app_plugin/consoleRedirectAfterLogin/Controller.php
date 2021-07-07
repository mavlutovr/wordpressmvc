<?php
namespace App\ConsoleRedirectAfterLogin;

class Controller extends \App\BaseController {

  public static function init() {
    \Wdpro\Modules::addWdpro('tools/consoleRedirectAfterLogin');
  }


  public static function initConsole() {

    \Wdpro\Tools\ConsoleRedirectAfterLogin\Controller::setUri(
      '/wp-admin/edit.php?post_type=app_menu_top'
    );

    \Wdpro\Tools\ConsoleRedirectAfterLogin\Controller::removeDashboard();
  }
}

return __NAMESPACE__;