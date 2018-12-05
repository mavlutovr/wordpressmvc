<?php
namespace Wdpro\Man;

class SqlTable extends \Wdpro\BaseSqlTable {

	protected static $name = 'wdpro_man';


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
	protected static function structure()
	{
		return [
			static::COLLS => [
				'id',
				'sorting'=>'int',
				'template',
				'name',
				'text'=>'text',
			],

			static::ENGINE => static::INNODB,
		];
	}


}