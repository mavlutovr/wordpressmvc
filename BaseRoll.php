<?php
namespace Wdpro;

/**
 * Базовый список объектов
 * 
 * @package Wdpro
 */
abstract class BaseRoll
{
	use Tools;



	/**
	 * Возвращает объект сущности списка
	 *
	 * @param int|array|null|bool $entityIdOrData IШвили данные объекта
	 * @return \Wdpro\BasePage|void
	 * @throws \Exception
	 */
	public static function getEntity($entityIdOrData=null)
	{
		if ($entityIdOrData)
		{
			if ($entityIdOrData === true)
			{
				$entityIdOrData = null;
			}
		}

		return wdpro_object(static::getEntityClass(), $entityIdOrData);
	}


	/**
	 * Возвращает класс сущностей данного списка
	 *
	 * @return \Wdpro\BaseEntity
	 */
	public static function getEntityClass()
	{
		return static::getNamespace().'\\Entity';
	}



	public function getKey() {
		
	}


	public function id() {

	}

}
