<?php
namespace Wdpro\Person;

class ExtraDataTable extends \Wdpro\BaseSqlTable {
	
	protected static $name = 'wdpro_user_extra_data';


	/**
	 * Структура таблицы
	 *
	 * @return array array('sql'=>'CREATE TABLE ...', 'format'=>array('field_name'=>'%d')
	 */
	protected static function structure() {

		return [
			static::COLLS => [
				'id',
				'params'=>'json',
			],
		];
	}


}