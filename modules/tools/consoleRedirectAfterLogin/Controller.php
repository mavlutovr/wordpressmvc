<?php
namespace Wdpro\Tools\ConsoleRedirectAfterLogin;

class Controller extends \Wdpro\BaseController {

  protected static $uri='';

  public static function setUri($uri) {
    static::$uri = $uri;
  }


  public static function removeDashboard() {
    add_action('admin_menu', function () {
      remove_menu_page('index.php');
    });
  }


  public static function initConsole() {
    $ref = $_SERVER['HTTP_REFERER'];
    $whlUri = get_option('whl_page');

    $dashboard = site_url().'/wp-admin/' === wdpro_current_url_with_proto();

    if (empty($_GET['logged']) && static::$uri 
    &&(
      $dashboard 
      || strstr($ref, '/wp-login.php') 
      || ($whlUri && strstr($ref, '/'.$whlUri.'/'))
      ) 
    ) {
      $url = home_url() . static::$uri;
      $url = wdpro_replace_query_params_in_url($url, ['logged'=>1]);
      wdpro_location($url);
    }
  }

}

return __NAMESPACE__;