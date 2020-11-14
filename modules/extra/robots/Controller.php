<?php
namespace Wdpro\Extra\Robots;

class Controller extends \Wdpro\BaseController {


  public static function init() {

    wdpro_ajax('robots-generate', function () {
      try {

        $robotsContent = static::getRobotsContent();
        \file_put_contents(WDPRO_PATH.'../../../robots.txt', $robotsContent);

        return [
          'html'=>'<p>Done</p>'
        ];
      }

      catch(\Exception $err) {
        return [
          'error'=>$err->getMessage(),
        ];
      }
    });
  }


  public static function runConsole() {

    \Wdpro\Console\Menu::addToSettings(ConsoleRoll::class);
  }


  public static function getRobotsContent() {
    $data = [
      'site_url'=>home_url(),
      'sitemap_url'=>'',
    ];

    $pluginsDir = WDPRO_PATH.'../';

    if (is_dir($pluginsDir.'google-sitemap-generator')) {
      $data['sitemap_url'] = home_url().'/sitemap.xml';
    }

    return wdpro_render_php(
      __DIR__.'/templates/robots.txt.php',
      $data
    );
  }
}

return __NAMESPACE__;