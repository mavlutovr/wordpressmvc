<?php
namespace Wdpro\Pay\Methods\NowPayments;

class SqlTable extends \Wdpro\BaseSqlTable {
	
	protected static $name = 'wdpro_nowpayments';

	/**
	 * Структура таблицы
	 *
	 * @return array array('sql'=>'CREATE TABLE ...', 'format'=>array('field_name'=>'%d')
	 */
	protected static function structure() {

		return [
			static::COLLS => [
				'id',
				'payment_id'=>'bigint',
        'completed'=>'tinyint',
        'payment_status',
        'pay_address',
        'price_amount',
        'price_currency',
        'pay_amount',
        'pay_currency',
        'pay_id'=>'int',
        'created'=>'int',
        'valid_until'=>'int',
        'updated'=>'int',
        'purchase_id'=>'bigint',
        'person_id'=>'int',
        'hash',
			],

      static::INDEX => [
        'payment_id',
        'payment_status',
        'pay_address',
        'pay_id',
        'person_id',
      ],
			
			static::ENGINE => static::INNODB,
		];
	}


}