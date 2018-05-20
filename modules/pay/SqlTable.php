<?php
namespace Wdpro\Pay;

class SqlTable extends \Wdpro\BaseSqlTable {

	protected static $name = 'wdpro_pay';
	
	/**
	 * Структура таблицы
	 *
	 * @return array array('sql'=>'CREATE TABLE ...', 'format'=>array('field_name'=>'%d')
	 */
	protected static function structure() {

		return array(
			static::COLLS => array(
				'id',
				'hash_id',
				'confirm'=>'tinyint', // Подтверждение транзакации (1 - подтверждена, -1 - отменена)
				'method_name'=>'varchar(20)', // Имя способа оплаты
				'target_key', // Ключ целевого объекта
				'cost'=>'float(11,2)',
				'person_id'=>'int',
				'visitor_id'=>'int',
				'created'=>'int', // Дата создания транзакции
				'text', // Описание транзакции
				'signature', // Подпись
				'result_post'=>'json',
				'secret'=>'char(32)',
				'params'=>'json', // Параметры транзакации
				'info'=>'json', // Всякая дополнительная информация, например, типы
				// оплат могут там хранить всякие свои параметры
			),
			
			static::INDEX => [
				['hash_id'],
			],
			
			static::ENGINE => static::INNODB,
		);
	}
}
