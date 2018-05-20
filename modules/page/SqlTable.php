<?php
namespace Wdpro\Page;

class SqlTable extends \Wdpro\BaseSqlTable
{
	protected static $name = 'posts';


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
				'ID'=>'int',
				'post_author'=>'int',
				'post_date',
				'post_date_gmt',
				'post_content',
				'post_title'=>'text',
				'post_excerpt'=>'text',
				'post_status',
				'comment_status',
				'ping_status',
				'post_password',
				'post_name',
				'to_ping'=>'text',
				'pinged'=>'text',
				'post_modified',
				'post_modified_gmt',
				'post_content_filtered'=>'text',
				'post_parent'=>'int',
				'guid',
				'menu_order'=>'int',
				'post_type',
				'post_mime_type',
				'comment_count'=>'int',
			],
		];
	}


	/**
	 * Включить автообновление структуры таблицы
	 *
	 * @returns bool
	 */
	public static function structureUpdateEnable() {

		return false;
	}


}