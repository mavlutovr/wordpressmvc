<?php
namespace Wdpro\Contacts;

class SqlTable extends \Wdpro\BaseSqlTable {

	protected static $name = 'wdpro_contacts';
	
	/**
	 * Структура таблицы
	 *
	 * @return array array('sql'=>'CREATE TABLE ...', 'format'=>array('field_name'=>'%d')
	 */
	protected static function structure() {

		return array(
			static::COLLS => array(
				'id',
				'text'=>'text',
				'map'=>'text',
				'sorting'=>'int',
			),
		);
	}


}