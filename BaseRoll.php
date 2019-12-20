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


	protected static $templateExtraData = [];



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



	public function getKey($additionalKeyData=null) {
		
	}


	public function id() {

	}


	/**
	 * Возвращает тип объектов Entity
	 *
	 * @return string
	 */
	public static function getType() {
		$class = static::getEntityClass();

		return $class::getType();
	}


	/**
	 * Устанавливает значение для данных шаблона
	 *
	 * @param string $dataKey Ключ (имя) данных
	 * @param * $dataValue Данные
	 */
	public static function set($dataKey, $dataValue) {
		static::$templateExtraData[$dataKey] = $dataValue;
	}

}
