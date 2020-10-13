<?php
namespace Wdpro\Blog\Tags;

class Controller extends \Wdpro\BaseController {


  public static function getTagData($tag) {
    return SqlTable::getRow([
      'WHERE tag=%s LIMIT 1',
      [$tag],
    ]);
  }


  public static function getTagOfSlug($slug) {
    if ($row = SqlTable::getRow([
      'WHERE slug=%s LIMIT 1',
      [$slug],
    ])) {
      return $row['tag'];
    }

    return $slug;
  }


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

      $slug = \wdpro_text_to_file_name($tag, true);
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
        'slug'=>$slug,
        'tag'=>$tag,
      ]);
      $entity->save();
    }
  }
}


return __NAMESPACE__;