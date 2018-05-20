<?php
namespace Wdpro\AdminNotice;

class SqlTable extends \Wdpro\BaseSqlTable
{
	protected static $name = 'wdpro_admin_notice_contacts';
	
	public static function structure()
	{
		return array(
			static::COLLS => array(
				'id',
				'email',
				'enabled'=>'tinyint',
				'sorting'=>'int',
			),
			
			static::ENGINE => static::INNODB,
		);
	}
}