<?php
namespace Wdpro\Blog\Tags;

class Controller extends \Wdpro\BaseController {

  public static function runConsole() {
    
    \Wdpro\Console\Menu::addToSettings(
      ConsoleRoll::class
    );
  }


  public static function onTagSave($tag) {

    if (0 === SqlTable::count([
      'WHERE tag=%s',
      [$tag],
    ])) {

      \wdpro_create_post([
        'post_name'=>\wdpro_text_to_file_name($tag),
        'post_title'=>$tag,
        'tag'=>$tag,
        'post_type'=>Entity::getType(),
        'post_status'=>'publish',
      ]);
    }
  }
}


return __NAMESPACE__;