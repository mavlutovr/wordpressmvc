<?php
namespace Wdpro\Callback;

class Entity extends \Wdpro\BaseEntity
{
	public static function getSqlTableClass()
	{
		return SqlTable::class;
	}
	
	protected function prepareDataForCreate($data)
	{
		$data['time'] = time();
		
		return $data;
	}


}