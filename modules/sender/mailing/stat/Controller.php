<?php
namespace Wdpro\Sender\Mailing\Stat;

class Controller extends \App\BaseController {

  public static function add($data) {
    if ($row = SqlTable::getRow([
      'WHERE mailing_id=%d AND target_id=%d AND action=%s',
      [
        $data['mailing_id'],
        $data['target_id'],
        $data['action'],
      ]
    ])) {

      $entity = Entity::instance($row);
    }

    else {
      $entity = new Entity($data);
    }

    $entity->incrementCount()->save();
  }


  public static function getRowsCount($action, $mailingId) {
    return SqlTable::count([
      'WHERE mailing_id=%d AND action=%s',
      [$mailingId, $action]
    ]);
  }
}


return __NAMESPACE__;