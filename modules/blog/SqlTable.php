<?php
namespace Wdpro\Blog;

class SqlTable extends \Wdpro\BaseSqlTable {

	protected static $name = 'wdpro_blog';
	
	
	/**
	 * Структура таблицы
	 *
	 * @return array array('sql'=>'CREATE TABLE ...', 'format'=>array('field_name'=>'%d')
	 */
	protected static function structure() {

		return array(
			static::COLLS => array(
				'id',
				'in_menu'=>'tinyint',
				'menu_order'=>'int',
				'post_parent'=>'int',
				'post_status'=>'varchar(20)',
				'post_name',
				'post_title',
				'image',
				'anons'=>'text',
				'date_added'=>'int',
				'date_edited'=>'int',
			),
			
			static::INDEX => [
				['post_parent'],
				['post_status'],
			],
			
			static::ENGINE => static::INNODB,
		);
	}


	
}