<?php
namespace Wdpro\Services\Thrive\Timer;

class SqlTable extends \Wdpro\BaseSqlTable {

	protected static $name = 'wdpro_thrive_timer';


	/**
	 * Структура таблицы
	 *
	 * @return array array('sql'=>'CREATE TABLE ...', 'format'=>array('field_name'=>'%d')
	 */
	protected static function structure() {

		return [
			static::COLLS => [
				'id',
				'started'    => 'int',
				'finish'     => 'int',
				'visitor_id' => 'varchar(128)',
				'price'      => 'int',
				'demo'       => 'tinyint',
				'thrive_id'=>'int',
			],

			static::INDEX => [
				['visitor_id'],
			],

			static::ENGINE => static::INNODB,
		];
	}
}