<?php
namespace Wdpro\Cart;

/*
 * Основная Mysql таблица модуля
 */
class SqlTable extends \Wdpro\BaseSqlTable {

	/**
	 * Имя таблицы
	 *
	 * @var string
	 */
	protected static $name = 'wdpro_cart';

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
				//'post_parent'=>'int', // Это и для страниц и для простых элементов
				'order_id'=>'int',
				'cost_for_one'=>'decimal(11,2)', // Стоимость за одну штуку
				'cost_for_all'=>'decimal(11,2)', // Стоимость за все штуки
				'discount_for_one'=>'decimal(11,2)', // Скидка за одну штуку
				'discount_for_all'=>'decimal(11,2)', // Скидка за все штуки
				'count'=>'int', // Количество штук
				'visitor_id', // Посетитель
				'person_id'=>'int', // Пользователь
				'key',
				'data'=>'json', // Всякие дополнительные данные, типа величина скидки, другие штуки
			],

			static::INDEX => [
				'visitor_id',
				'person_id',
				'key',
				'order_id',
			],

			static::ENGINE => static::INNODB,
		];
	}


}