<?php
namespace Wdpro\Page\Other;

class SqlTable extends \Wdpro\BaseSqlTable {

	protected static $name = 'wdpro_pages';
	
	/**
	 * Структура таблицы
	 *
	 * @return array array('sql'=>'CREATE TABLE ...', 'format'=>array('field_name'=>'%d')
	 */
	protected static function structure() {

		return array(
			static::COLLS => array(
				'id',
				'post_parent'=>'tinyint',
				'post_status'=>'varchar(20)',
				'post_name',
				'template', // Выбранный шаблон
				'menu_order'=>'int',
				'post_title',
			),
			
			static::ENGINE => static::INNODB,
		);
	}


}