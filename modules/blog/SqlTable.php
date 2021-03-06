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

		$structure = array(
			static::COLLS => array(
				'id',
				'in_menu'=>'tinyint',
				'menu_order'=>'int',
				'post_parent'=>'int',
				'date_added'=>'int',
				'date_edited'=>'int',
				'content_transfer_url_id'=>'int',
				'post_status'=>'varchar(20)',
				'post_name',
				'post_title[lang]',
				'image',
				'breadcrumbs_label[lang]',
				'anons[lang]'=>'text',
				'tags[lang]'=>'json',
			//	'tags_string[lang]'=>'text',
				'post_content[lang]'=>'longtext',
			),
			
			static::INDEX => [
				['post_parent'],
				['post_status'],
			],
			
			static::ENGINE => static::INNODB,
		);

		$structure = \apply_filters('blog_console_sqltable_structure', $structure);

		return $structure;
	}


	
}