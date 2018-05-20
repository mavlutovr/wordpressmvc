<?php
namespace Wdpro\Sender\Templates\Email;

class SqlTable extends \Wdpro\BaseSqlTable {
	
	protected static $name = 'wdpro_sender_templates_email';


	/**
	 * Структура таблицы
	 *
	 * @return array array('sql'=>'CREATE TABLE ...', 'format'=>array('field_name'=>'%d')
	 */
	protected static function structure() {

		return array(
			static::COLLS => array(
				'id',
				'name',
				'subject',
				'text'=>'text',
				'info'=>'text',
				'sorting',
			),
		);
	}


}