<?php
namespace Wdpro\Blog\Tags;

class Controller extends \Wdpro\BaseController {

  public static function runConsole() {
    
    \Wdpro\Console\Menu::addToSettings(
      ConsoleRoll::class
    );
  }


  public static function onTagSave($tag) {

    $tag = trim($tag);
    if (!$tag) {
      return false;
    }

    if (0 === SqlTable::count([
      'WHERE tag=%s',
      [$tag],
    ])) {

      $slug = \wdpro_text_to_file_name($tag);
      $slugOrig = $slug;
      $slugN = 1;

      while (SqlTable::count([
        'WHERE slug=%s',
        [$slug],
      ])) {
        $slugN ++;
        $slug = $slugOrig . '-' . $slugN;
      }

      $entity = Entity::instance([
        'slug'=>\wdpro_text_to_file_name($tag),
        'tag'=>$tag,
      ]);
      $entity->save();
    }
  }
}


return __NAMESPACE__;