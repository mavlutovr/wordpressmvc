<?php
namespace Wdpro\Sender\Mailing;

/*
 * Основная Mysql таблица модуля
 */
class SqlTable extends \Wdpro\BaseSqlTable {

	/**
	 * Имя таблицы
	 *
	 * @var string
	 */
	protected static $name = 'wdpro_mailing';

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
				'date_started' =>'int',
				'date_updated'=>'int',
				'menu_order'=>'int',
				'sended_count'=>'int',
				'sended_last_id'=>'int',
				'status',
				'label',
				'subject',
				'text'=>'longtext',

			],

			static::ENGINE => static::INNODB,
		];
	}


}