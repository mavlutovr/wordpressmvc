<?php
namespace Wdpro\Sender\Mailing\Stat;

/*
 * Основная Mysql таблица модуля
 */
class SqlTable extends \App\BaseSqlTable {

	/**
	 * Имя таблицы
	 *
	 * @var string
	 */
	protected static $name = 'wdpro_mailing_stat';

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
				'mailing_id'=>'int',
				'target_id'=>'int',
				'count'=>'int',
				'updated'=>'int',
				'last_ip',
				'action',
			],


			static::INDEX => [
				'mailing_id',
				'target_id',
				'last_ip',
				'action',
			],

			
			static::ENGINE => static::INNODB,
		];
	}


}