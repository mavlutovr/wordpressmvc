<?php
namespace Wdpro\Cart\Order;

/*
 * Основная Mysql таблица модуля
 */
class SqlTable extends \Wdpro\BaseSqlTable {

	/**
	 * Имя таблицы
	 *
	 * @var string
	 */
	protected static $name = 'wdpro_order';

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
				'time'=>'int',
				'status'=>'varchar(32)',
				'secret'=>'char(32)',
				'email',
				'form'=>'json',
			],


			static::INDEX => [
				'status',
			],


			static::ENGINE => static::INNODB,
		];
	}


}