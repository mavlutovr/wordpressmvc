<?php
namespace Wdpro\Services\Getresponse;

class SqlTable extends \Wdpro\BaseSqlTable {

	protected static $name = 'wdpro_getresponse_log';

	/**
	 * Структура таблицы
	 *
	 * <pre>
	 * return [
	 *  static::COLLS => [
	 *      'section_type'=>'varchar(40)',
	 *      'section_id'=>'int',
	 *  ],
	 * ];
	 * </pre>
	 *
	 * @return array array('sql'=>'CREATE TABLE ...', 'format'=>array('field_name'=>'%d')
	 */
	protected static function structure() {
		return [
			static::COLLS => [
				'id',
				'email',
				'date'=>'int',
				'status',
			],

			static::ENGINE => static::INNODB,
		];
	}


}