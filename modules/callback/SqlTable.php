<?php
namespace Wdpro\Callback;

class SqlTable extends \Wdpro\BaseSqlTable
{
	protected static $name = 'app_callback';
	
	protected static function structure()
	{
		return array(
			static::COLLS => array(
				'id',
				'time'=>'int',
				'data'=>'json',
			)
		);
	}
}



















