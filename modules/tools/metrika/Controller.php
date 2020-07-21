<?php
namespace Wdpro\Tools\Metrika;

class Controller extends \Wdpro\BaseController {

  /**
   * Console run
   */
  public static function runConsole() {

    // Settings
    \Wdpro\Console\Menu::addSettings('Яндекс.Метрика', function ($form) {

      /** @var \Wdpro\Form\Form $form */

      $form->add([
        'name'=>'wdpro_metrika_id',
        'top'=>'ID счетчика Яндекс.Метрики',
        'bottom'=>'<a href="https://i.imgur.com/7hzAiYC.png" target="_blank">Откуда взять ID счетчика?</a>',
      ]);

      $form->add($form::SUBMIT_SAVE);

      return $form;
    });
  }


  /**
   * Site run
   */
  public static function runSite() {
    $metrikaId = wdpro_get_option('wdpro_metrika_id');

    if ($metrikaId) {
      \add_action('wp_footer', function () use (&$metrikaId) {
        echo '<script>if (window.wdpro) {wdpro.yandexMetrikaAddId('.$metrikaId.');}</script>';
      });
    }
  }
}


return __NAMESPACE__;
