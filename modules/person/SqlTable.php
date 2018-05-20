<?php
namespace Wdpro\Person;

class SqlTable extends \Wdpro\BaseSqlTable {

	protected static $name = 'users';


	/**
	 * Включить автообновление структуры таблицы
	 *
	 * @returns bool
	 */
	public static function structureUpdateEnable() {

		return false;
	}


	/**
	 * Структура таблицы
	 *
	 * @return array array('sql'=>'CREATE TABLE ...', 'format'=>array('field_name'=>'%d')
	 */
	protected static function structure() {

		return [
			static::COLLS => [
				'ID'=>'bigint(20)',
				'user_login'=>'varchar(60)',
				'user_pass'=>'varchar(64)',
				'user_nicename'=>'varchar(50)',
				'user_email'=>'varchar(100)',
				'user_url'=>'varchar(100)',
				'user_registered'=>'datetime',
				'user_activation_key'=>'varchar(60)',
				'user_status'=>'int(11)',
				'display_name'=>'varchar(250)',
			],
		];
	}
}