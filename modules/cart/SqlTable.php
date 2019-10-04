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
				'order_id',
				'cost_for_one'=>'int', // Стоимость за одну штуку
				'cost_for_all'=>'int', // Стоимость за все штуки
				'count'=>'int', // Количество штук
				'visitor_id'=>'int', // Посетитель
				'person_id'=>'int', // Пользователь
				'element_key',
				'data'=>'json', // Всякие дополнительные данные, типа величина скидки, другие штуки
			],

			static::INDEX => [
				'visitor_id',
				'person_id',
				'element_key',
				'order_id',
			],

			static::ENGINE => static::INNODB,
		];
	}


}