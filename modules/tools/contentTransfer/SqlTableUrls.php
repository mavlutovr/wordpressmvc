<?php
namespace Wdpro\Tools\ContentTransfer;

/*
 * Основная Mysql таблица модуля
 */
class SqlTableUrls extends \Wdpro\BaseSqlTable {

	/**
	 * Имя таблицы
	 *
	 * @var string
	 */
	protected static $name = 'wdpro_cont_transfer';

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
				'parsed_block'=>'tinyint', // Отпарсено в блоке
				'parsed_time'=>'int', // Время парсинга
				'parent_id'=>'int', // Подительский id (для древовидной структуры)
				'has_children'=>'tinyint', // Есть ли доверние разделы
				'post_id'=>'int', // ID поста, который получился из этой ссылки
				'menu_order'=>'int', // Сортировка, чтобы по этому порядку парсить
				'namespace',
				'url',
				'text',
				'data'=>'json', // Собранные данные
			],

			static::ENGINE => static::INNODB,
		];
	}


}