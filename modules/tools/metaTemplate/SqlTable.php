<?php
namespace Wdpro\Tools\MetaTemplate;

/*
 * Основная Mysql таблица модуля
 */
class SqlTable extends \Wdpro\BaseSqlTable {


	/**
	 * Имя таблицы
	 *
	 * @var string
	 */
	protected static $name = 'wdpro_meta_template';


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
				'menu_order'=>'int',
				'post_name',
				'title[lang]',
				'description[lang]',
				'h1[lang]',
			],

			static::ENGINE => static::INNODB,
		];
	}


}