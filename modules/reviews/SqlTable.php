<?php
namespace Wdpro\Reviews;

class SqlTable extends \Wdpro\BaseSqlTable {
	
	protected static $name = 'wdpro_reviews';

	/**
	 * Структура таблицы
	 *
	 * @return array array('sql'=>'CREATE TABLE ...', 'format'=>array('field_name'=>'%d')
	 */
	protected static function structure() {

		return [
			static::COLLS => [
				'id',
				'sorting'=>'int',
				'section_type'=>'varchar(40)',
				'section_id'=>'int',
				'name',
				'text'=>'text',
			],
			
			static::ENGINE => static::INNODB,
		];
	}


}