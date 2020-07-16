<?php
namespace Wdpro\Page\PostNamePrefix;

class SqlTable extends \Wdpro\BaseSqlTable {

	protected static $name = 'wdpro_pn_prefix';


	/**
	 * Table structure
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
	protected static function structure()
	{
		return [

			static::COLLS => [
				'id',
				'post_id',
				'prefix',
				'post_name',
				'final_post_name',
			],

			static::UNIQUE => [
				'post_id',
			],
		];
	}


}