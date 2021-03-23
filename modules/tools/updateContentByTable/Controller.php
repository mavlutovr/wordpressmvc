<?php
namespace Wdpro\Tools\UpdateContentByTable;

class Controller extends \Wdpro\BaseController {

  protected static $errors = [];

  public static function runConsole() {
    \Wdpro\Console\Menu::add(ConsoleRoll::class);
  }


  public static function getLastColls($key=null) {
    $json = wdpro_get_option('updateContentByTable-colls');
    if (!$json) throw new \Exception('Скопируйте данные вместе с заголовками, чтобы можно было определить структуру данных.');

    $list = \json_decode($json, true);

    if ($key) {
      foreach($list as $i => $element) {
        $list[$i] = $element[$key];
      }
    }

    return $list;
  }


  public static function updateData($tableContent) {
    $rows = \explode('
', $tableContent);

    foreach($rows as $rowContent) {
      static::updateRow($rowContent);
    }
  }


  public static function updateRow($rowContent) {

    $rowContent = \apply_filters('updateContentByTable-rowContent', $rowContent);
    if (!$rowContent) return false;

    $rowData = \explode('	', $rowContent);
    $rowData = \apply_filters('updateContentByTable-rowData', $rowData);

    if (!$rowData) return;

    $collsData = static::rowDataToCollsData($rowData);
    if (!$collsData) return;

    static::updatePost($collsData);
  }


  public static function updatePost($data) {
    
    $data['uri'] = wdpro_url_to_uri($data['url']);
    $data = \apply_filters('updateContentByTable-rowData', $data);
    if (!is_array($data)) return;
    
    $post_name = preg_replace('~(/)$~', '', $data['uri']);
    $post_name = preg_replace('~^(/)~', '', $post_name);

    $post = \wdpro_get_post_by_name($post_name);
    if (!$post || !$post->loaded()) {
      static::$errors[] = 'Не найдена страница с адресом: '.$post_name;
      return;
    }
    $post->mergeMeta($data);
    $post->mergeData($data);
    $post->save();
  }


  /**
   * Преобразует массив столбиков строки в данные для обновления
   *
   * @param [] $rowData
   * @return void
   */
  public static function rowDataToCollsData($rowData) {
    $colls = [];
    $keyTexts = static::getCollsKeyTexts();

    $urlHeaaderFinded = false;
    $headers = [];

    foreach($rowData as $collI => $collText) {
      $collText2 = \mb_strtolower($collText);
      $collText2 = trim($collText2);

      foreach($keyTexts as $collKey => $texts) {
        foreach($texts as $text) {
          if ($collText2 === \mb_strtolower($text)) {
            $headers[$collI] = [
              'fieldName'=>$collKey,
              'label'=>$collText,
            ];
            if ($collKey === 'url') $urlHeaaderFinded = true;
            break;
          }
        }
      }
    }

    if ($urlHeaaderFinded) {
      $headersJson = \json_encode($headers);
      update_option('updateContentByTable-colls', $headersJson);
      return false;
    }

    $colls = static::getLastColls('fieldName');

    $update = [];
    foreach($rowData as $i => $fieldValue) {
      if (empty($colls[$i])) continue;
      $fieldName = $colls[$i];
      $update[$fieldName] = $fieldValue;
    }

    return $update;
  }


  /**
   * Возвращает ключевые слова, по которым находятся колонки
   *
   * @return void
   */
  public static function getCollsKeyTexts() {
    return \apply_filters('updateContentByTable-keyTexts', [
      'url'=>[
        'address',
      ],
      'title'=>[
        'title',
      ],
      'h1'=>[
        'h1',
      ],
      'breadcrumbs_label'=>[
        'Хлебная крошка',
      ],
      'description'=>[
        'description',
      ],
      'keywords'=>[
        'keywords',
      ],
    ]);
  }


  public static function getErrors() {
    return static::$errors;
  }

}

return __NAMESPACE__;