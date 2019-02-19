<?php
namespace Wdpro\Secure;

/*
 * Основная Mysql таблица модуля
 */
class SqlTable extends \Wdpro\BaseSqlTable {

	/**
	 * Имя таблицы
	 *
	 * @var string
	 */
	protected static $name = 'wdpro_secure';

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
				'time'=>'int', // Это и для страниц и для простых элементов
				'ip',
				'message',
				'text',
			],

			static::INDEX => [
				'time',
				'ip',
			],

			static::ENGINE => static::INNODB,
		];
	}


}