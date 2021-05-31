<?php
namespace Wdpro\Pay\CryptAddresses;

/*
 * Основная Mysql таблица модуля
 */
class SqlTable extends \Wdpro\BaseSqlTable {

	/**
	 * Имя таблицы
	 *
	 * @var string
	 */
	protected static $name = 'app_cryptaddresses';

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
				'person_id'=>'int', // Это и для страниц и для простых элементов
				'valid'=>'int',
				'currency'=>'varchar(10)',
				'address',
			],

			static::INDEX => [
				'person_id',
				'currency',
				'valid',
				'address',
			],

			static::ENGINE => static::INNODB,
		];
	}


}