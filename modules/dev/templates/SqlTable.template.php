<?php echo '<?php'.PHP_EOL; ?>
namespace <?php echo $namespace; ?>;

/*
 * Основная Mysql таблица модуля
 */
class SqlTable extends \<?=$data['parent_namespace']?>\BaseSqlTable {

	/**
	 * Имя таблицы
	 *
	 * @var string
	 */
	protected static $name = '<?php echo $SqlTable; ?>';

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
				'post_parent'=>'int', // Это и для страниц и для простых элементов
<?php if ($type == 'page'): ?>
				'menu_order'=>'int',
				'post_status',
				'post_title',
				'post_name',
<?php endif; ?>

			],

			static::ENGINE => static::INNODB,
		];
	}


}