<?php
namespace Wdpro\Counters;

class SqlTable extends \Wdpro\BaseSqlTable {
	
	protected static $name = 'wdpro_counters';
	
	public static function structure() {
		
		return array(
			
			static::COLLS => array(
				'id',
				'sorting'=>'int',
				'code'=>'text',
			),
			
			static::ENGINE => static::INNODB,
		);
	}
}