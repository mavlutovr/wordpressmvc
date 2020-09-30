<?php
namespace Wdpro;

$sqlFieldsTypes = array();


/**
 * Базовый класс Sql таблиц
 *
 * @package Wdpro
 */
abstract class BaseSqlTable
{
	use Tools;

	const COLLS = 'colls';
	const SQL = 'sql';
	const FORMAT = 'format';
	const INDEX = 'index';
	const UNIQUE = 'unique';
	const FULLTEXT = 'fulltext';
	const ENGINE = 'engine';
	const MYISAM = 'MyISAM';
	const INNODB = 'InnoDB';

	protected static $test = false;


	/**
	 * Типы полей
	 *
	 * @var array
	 */
	protected static $types = array(
		//'json'=>SqlTypeJson::class,
		'json'=>'\Wdpro\\SqlTypeJson',
	);


	/** @var string */
	protected static $name;
	//protected static $fieldsFormat;
	//protected static $fieldsTypes = array();


	/**
	 * Возвращает массив данных из таблицы по Where запросу
	 *
	 * @param string|array $where Параметры запроса или сам запрос
	 * 'WHERE a="A"'
	 * array('WHERE a=%s', 'A')
	 * array('a'=>'A')
	 * @param string $fields Поля, которые выбрать из таблицы
	 * @return array
	 */
	public static function select ($where, $fields = '*') {
		global $wpdb;

		static::check();

		if (static::$test) {
			echo 'TEST Where: '.PHP_EOL;
			print_r($where);
			echo PHP_EOL.PHP_EOL;
		}

		if ( is_array($where) ) {
			$where = static::prepare($where);
		}

		$query = 'SELECT ' . $fields . ' FROM ' . static::getNameWithPrefix() . ' ' . $where;

		$query = \Wdpro\Lang\Data::replaceLangShortcode($query);

		if (static::$test) {
			echo 'TEST Query: '.PHP_EOL;
			print_r($query);
			echo PHP_EOL.PHP_EOL;
		}

		$results = $wpdb->get_results($query, ARRAY_A);
		$results = static::sqlToValueList($results);

		return $results;
	}


	/**
	 * Возвращает одну строку по Where запросу
	 *
	 * @param string|array $where Параметры запроса или сам запрос
	 * 'WHERE a="A"'
	 * array('WHERE field=%s', 'value')
	 * array('field'=>'value')
	 * @param string $fields Поля, которые выбрать из таблицы
	 * @param null|array|callable $insertIfNotExistsThisData Данные по-умолчанию
	 * Они добавяться в таблицу, если строка будет не найдена
	 * @return array
	 */
	public static function getRow($where, $fields='*', $insertIfNotExistsThisData=null)
	{
		static::check();

		if ($list = static::select($where, $fields))
	    {
	        return $list[0];
	    }

		// По-умолчанию
		else if ($insertIfNotExistsThisData) {

			// Когда данные по-умолчанию это каллбэк
			if (is_callable($insertIfNotExistsThisData)) {
				$insertIfNotExistsThisData = $insertIfNotExistsThisData();
			}

			// Сортировка
			if (isset($insertIfNotExistsThisData['sortingWhere'])) {

				$sortingField = isset($insertIfNotExistsThisData['sortingField']) ?
					$insertIfNotExistsThisData['sortingField'] : 'sorting';

				$insertIfNotExistsThisData[$sortingField] = 10;

				if ($lastSorting = static::getRow(
					$insertIfNotExistsThisData['sortingWhere'],
					$sortingField
				)){
					$insertIfNotExistsThisData[$sortingField] += $lastSorting[$sortingField];
				}
			}

			// Сохранение
			$id = static::insert($insertIfNotExistsThisData);

			$idFieldName = 'id';
			if (static::isField('ID')) $idFieldName = 'ID';
			return static::getRow(
				['WHERE `'.$idFieldName.'`=%d', [$id]],
				$fields
			);
		}
	}


	/**
	 * Вставляет данные в таблицу
	 *
	 * @param array $data Данные
	 * @param string|array $format Форматы вставляемых данных
	 * array('%s', '%d', '%f');
	 * @returns number ID вставленной записи
	 */
	public static function insert($data, $format=null)
	{
		global $wpdb;

		static::check();

		$data = static::valueToSqlRow($data);

		$fields = static::getFieldsNames();
		foreach ( $fields as $field ) {
			if (!isset($data[$field])) {
				$data[$field] = '';
			}
		}

		if (!$format) $format = static::getFormatForDataKeys($data);

		/*echo("\n\n\n\n-------".static::getName()."------------\n\n");
		echo "format: "; print_r($format);
		echo "data: "; print_r($data);*/
	    $wpdb->insert(static::getNameWithPrefix(), $data, $format);

	  /*  global $wpdb;
		echo '$wpdb->insert_id: '.($wpdb->dbh->error).PHP_EOL; exit();*/

		if (!$wpdb->insert_id) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				echo $wpdb->dbh->error.PHP_EOL;
				exit();
			}
			throw new \Exception($wpdb->dbh->error);
		}

		return $wpdb->insert_id;
	}


	/**
	 * Обновление данных
	 *
	 * @param array $data Новые данные
	 * @param array|string $where Параметры запроса (Тут надо по-особенному задавать where)
	 * array('id'=>3)
	 * @param string|array $format Форматы обновляемых данных
	 * array('%s', '%d', '%f');
	 * @param string $whereFormat Параметры WHERE запроса
	 * Когда WHERE запрос в формате array('a'=>'A')
	 * array('%s', '%d', '%f');
	 */
	public static function update($data, $where, $format=null, $whereFormat=null)
	{
		global $wpdb;

		static::check();

		$data = static::valueToSqlRow($data);

		if (!$format)
		{
			$format = static::getFormatForDataKeys($data);
		}

		if (!$whereFormat)
		{
			$whereFormat = array();
			foreach($where as $key=>$value)
			{
				$whereFormat[] = static::getFieldFormat($key);
			}
		}


		$wpdb->update(static::getNameWithPrefix(), $data, $where, $format, $whereFormat);
	}


	/**
	 * Удаление
	 *
	 *     ::delete( 'table', array( 'ID' => 1 ) )
	 *     ::delete( 'table', array( 'ID' => 1 ), array( '%d' ) )
	 *
	 * @param array $where array('ID'=>1)
	 * @param array|string|null $format array( '%d' )
	 */
	public static function delete($where, $format=null)
	{
		global $wpdb;
		/** @var \wpdb $wpdb */

		static::check();

		// Обычный формат Where
		if (isset($where[0]) && is_string($where[0])) {

			$where = static::prepare($where);
			$wpdb->query('DELETE FROM `'.static::getNameWithPrefix().'` '.$where);
		}

		// Формат array('id'=>123)
		else {
			$wpdb->delete(static::getNameWithPrefix(), $where, $format);
		}

	}


	/**
	 * Выполнение произвольного запроса
	 *
	 * @param string$query Запрос
	 */
	public static function query($query) {

		global $wpdb;

		$query = str_replace('{table}', static::getNameWithPrefix(), $query);

		$wpdb->query($query);
	}


	/**
	 * Возвращает формат для данных
	 *
	 * @param array $data Данные
	 * @return array
	 */
	protected static function getFormatForDataKeys($data)
	{
		$format = array();

		foreach($data as $key=>$value)
		{
			$format[$key] = static::getFieldFormat($key);
		}

		return $format;
	}


	/**
	 * Возвращает количество записей по запросу
	 *
	 * @param array|string $where Параметры запроса или сам запрос
	 * 'WHERE a="A"'
	 * array('WHERE a=%s', 'A')
	 * array('a'=>'A')
	 * @return null|number
	 */
	public static function count($where)
	{
		global $wpdb;

		static::check();

		$where = static::prepare($where);

		$return = $wpdb->get_var(
			'SELECT count(*) FROM `'.static::getNameWithPrefix().'` '.$where
		);

		return (int) $return;
	}


	/**
	 * Возвращает безопасный запрос по параметрам запроса
	 *
	 * @param string|array $query Параметры запроса или сам запрос.
	 * Когда сам запрос, то возвратиться просто сам запрос.
	 * Когда параметры: array('SELECT * FROM my_table WHERE id=%d', 10)
	 * @return string
	 */
	public static function prepare ($query) {
		global $wpdb;

		// Когда данные в формате array('where %d %s', 1, 'a')
		// или array('where %d %s', array (1, 'a') )
		if ( is_array($query) ) {

			// Если есть первый элемент (Where)
			if (isset($query[0])) {
				// Когда второй параметр, это массив
				if (!isset($query[1])) {
					return $query[0];
				}
				if ( is_array($query[1]) ) {
					return static::prepareByData($query[0], $query[1]);
				}

				// Когда второй параметр это элемент
				else {
					// Есть данные, которые надо вставить в запрос
					if ( isset($query[1]) ) {
						$data = [];
						for ( $i = 1; $i < count($query); $i ++ ) {
							$data[] = $query[ $i ];
						}

						return static::prepareByData($query[0], $data);
					}
				}
			}

			else {
				return '';
			}
		}

		// Строка
		else {
			return $query;
		}


		return $query;
	}


	/**
	 * Обработка запроса с помощью 2-х параметров: запроса и данных
	 *
	 * @param string $query Запрос
	 * @param array $data Данные
	 *
	 * @return mixed
	 */
	public static function prepareByData($query, $data) {
		global $wpdb;

		if (count($data)) {
			$query = $wpdb->prepare($query, $data);
		}

		return $query;
	}


	/**
	 * @param bool $checkInited Проверить инициированность
	 * @throws TableException
	 */
	protected static function check($checkInited=true) {

		if (!isset(static::$name)) {

			throw new TableException(
				'У таблицы '.get_called_class().' не указано имя: '
				.'protected static $name'
			);
		}

		if ($checkInited) {
			static::init();

			if (!static::issetStatic('sqlTablesInited'))
			{
				throw new TableException(
					'Таблица '.get_called_class().' не подготовлена. Ее необходимо
				подготовить перед использованием с помощью запуска метода '.
					get_called_class().'::init().
				 Это желательно делать в {moduleName}.init.php файле модуля.'
				);
			}
		}
	}


	/**
	 * Проверяет, изменился ли файл таблицы с прошлого раза
	 *
	 * @return int
	 */
	protected static function isStructureFileUpdated() {

		//
		if (!static::issetStatic('isStructureFileUpdated'))
		{
			// Если версия таблицы поменялась, обновляем ее структуру
			$lastEditedTime = get_option( 'sqlTableVersion:' . static::$name );
			$reflector = new \ReflectionClass( get_called_class() );
			$currentFileTime = filemtime( $reflector->getFileName() );

			if ($lastEditedTime < $currentFileTime)
			{
				static::setStatic('isStructureFileUpdated', $currentFileTime);
			}
		}

		return static::getStatic('isStructureFileUpdated');
	}


	/**
	 * Проверяет, поменялась ли структура языков
	 */
	protected static function isLangStructureUpdated() {
		if (!static::issetStatic('isStructureLangUpdated'))
		{
			// Если версия таблицы поменялась, обновляем ее структуру
			$lastEditedTime = get_option( 'sqlTableLangVersion:' . static::$name );
			$currentFileTime = \Wdpro\Lang\Data::getLastUpdateTime();

			if ($lastEditedTime < $currentFileTime)
			{
				static::setStatic('isStructureLangUpdated', $currentFileTime);
			}
			else {
				static::setStatic('isStructureLangUpdated', false);
			}
		}

		return static::getStatic('isStructureLangUpdated');
	}


	/**
	 * Обновление структуры таблицы
	 *
	 * @throws TableException
	 */
	public static function init()
	{
		global $wpdb;

		static::check(false);

		if (!static::getStatic('sqlTablesInited')) {

			static::setStatic('sqlTablesInited', true);

			if ($structure = static::structure()) {

				$deltaQuery = null;
				$structureCollsParams = null;
				$format = null;
				$fields = array();

				if (isset($structure[static::FORMAT])) {
					$format = $structure[static::FORMAT];
				}


				if ($structure[static::COLLS]) {

					$format = array();

					foreach ($structure[static::COLLS] as $fieldName => $fieldParams) {

						$field = array(
							'format'=>'%s',
							'params'=>$fieldParams,
							'name'=>$fieldName,
						);

						if ($fieldParams == 'id') {
							$field['params'] = 'AUTO_INCREMENT PRIMARY KEY';
							$field['name'] = 'id';
							$field['format'] = '%d';
							$field['type'] = 'int(11)';

							$fields[$field['name']] = $field;
							$format[$field['name']] = $field['format'];
						}

						else {

							if (is_numeric( $fieldName )) {
								$field['name'] = $fieldParams;
								$field['params'] = 'varchar';
							}

							if ($field['params'] === 'int') {
								$field['params'] = 'int(11)';
							}
							else if ($field['params'] === 'varchar') {
								$field['params'] = 'varchar(255)';
							}
							else if ($field['params'] === 'tinyint') {
								$field['params'] = 'tinyint(1)';
							}

							$parseParams = explode(' ', $field['params']);
							$field['type'] = array_shift($parseParams);
							$field['params'] = implode(' ', $parseParams).' NOT NULL';

							if (strstr( $field['type'], 'int' )
							) {
								$field['format'] = '%d';
							}
							else if (strstr($field['type'], 'float')
							|| strstr($field['type'], 'double')) {
								$field['format'] = '%f';
							}

							// Добавление типа поля
							$addType = function (&$field) {

								// Добавляем тип поля
								static::addFieldType( $field['name'], $field['type'] );

								// Если тип добавился и удалось его получить
								if ($type = static::getFieldType($field['name']))
								{
									// Обновляем параметры поля (sql запрос на создание поля)
									$field['params'] =
										$type::getParams($field['params']);
									$field['type'] =
										$type::getType($field['type']);
								}
							};


							// Языковые поля
							if (strstr($field['name'], '[lang]')) {
								$fieldsLang = static::getStatic('fieldsLang');
								if (!is_array($fieldsLang)) $fieldsLang = [];
								$fieldsLang[] = str_replace('[lang]', '', $field['name']);
								static::setStatic('fieldsLang', $fieldsLang);

								foreach (\Wdpro\Lang\Data::getUris() as $lang) {

									$field2 = $field;

									$fieldName = str_replace(
										'[lang]',
										\Wdpro\Lang\Data::getSuffix($lang),
										$field2['name']
									);
									$field2['name'] = $fieldName;

									$addType($field2);

									$fields[$fieldName] = $field2;
									$format[$fieldName] = $field2['format'];
								}
							}

							// Без языков
							else {
								$addType($field);

								$fields[$field['name']] = $field;
								$format[$field['name']] = $field['format'];
							}

						}

					}
				}

				// Если файл таблицы поменялся, обновляем ее структуру
				if (static::structureUpdateEnable()
				    && (static::isStructureFileUpdated()
				    || static::isLangStructureUpdated()))
				{
					$currentFileTime = static::isStructureFileUpdated();
					$currentLangTime = static::isLangStructureUpdated();


					// Wordpress (стандартный метод)
					if (isset($structure[static::SQL]) && $structure[static::SQL])
					{
						require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
						dbDelta( $structure['sql'] );
					}


					// Wdpro метод
					if ($structure[static::COLLS])
					{
						$sqlFields = @$wpdb->get_results(
							'SHOW COLUMNS FROM `'
							. static::getNameWithPrefix() . '`',
							ARRAY_A
						);


						// Создание новой таблицы
						if (!$sqlFields)
						{
							// Поля
							$collsQuery = '';
							foreach($fields as $field)
							{
								if ($collsQuery != '') $collsQuery .= ', ';

								$collsQuery .= '`'.$field['name'].'` '
								.$field['type'].' '.$field['params'];
							}

							// Тип таблицы
							if (!isset($structure[static::ENGINE]))
							{
								$structure[static::ENGINE] = static::MYISAM;
							}

							$wpdb->query(
								'CREATE TABLE `'.static::getNameWithPrefix() .'` ('
								.$collsQuery.') ENGINE = '.$structure[static::ENGINE]
							);
						}


						// Редактирование существующей таблицы
						else
						{
							// Список колонок для удаления
							$remove = array();
							foreach($sqlFields as $sqlField) {

								$remove[$sqlField['Field']] = $sqlField;
							}

							$lastFieldName = null;

							// Перебираем новые поля
							foreach($fields as $fieldN=>$field) {

								// Если такое поле есть
								if (isset($remove[$field['name']]))
								{
									$sqlField = $remove[$field['name']];
									unset($remove[$field['name']]);

									// Если тип не совпадает с текущими
									if ($sqlField['Type'] != $field['type'])
									{
										// Обновляем поле
										$wpdb->query(
											'ALTER TABLE
											`'.static::getNameWithPrefix().'`
											CHANGE `'.$field['name'].'`
											`'.$field['name'].'` '
											.$field['type']
											.' '.$field['params']
											//.' NOT NULL'
										);
									}
								}

								// Такого поля нет (создаем)
								else
								{
									$query = 'ALTER TABLE `'
										.static::getNameWithPrefix().'` '
										.' ADD '.$field['name']
										.' '.$field['type'].' '.$field['params'];

									if ($lastFieldName)
									{
										$query .= ' AFTER `'.$lastFieldName.'`';
									}
									else
									{
										$query .= ' FIRST';
									}

									$wpdb->query($query);
								}

								$lastFieldName = $field['name'];
							}


							// Удаление
							if (wdpro_get_option('wdpro_sql_structure_drop_available')) {
								foreach($remove as $field)
								{
									$wpdb->query(
										'ALTER TABLE `'
										. static::getNameWithPrefix()
										. '` DROP COLUMN ' . $field['Field']
									);
								}
							}
						}
					}


					// Индексы
					// INDEX
					if (isset($structure[static::INDEX])) {
						static::makeIndexes( 'index', $structure[static::INDEX] );
					}

					// UNIQUE
					if (isset($structure[static::UNIQUE])) {
						static::makeIndexes( 'unique', $structure[static::UNIQUE] );
					}

					// FULLTEXT
					if (isset($structure[static::FULLTEXT])) {
						static::makeIndexes( 'fulltext',
							$structure[static::FULLTEXT] );
					}


					isset($currentFileTime)
					&& $currentFileTime
					&& update_option(
						'sqlTableVersion:' . static::$name,
						$currentFileTime );

					isset($currentLangTime)
					&& $currentLangTime
					&& update_option(
						'sqlTableLangVersion:'.static::$name,
						$currentLangTime);
				}


				// Формат данных
				static::setFieldsFormat( $format );
			}
		}
	}


	/**
	 * Проверяет наличие колонки
	 *
	 * Аналог isColl
	 *
	 * @param string $fieldName Название колонки
	 * @return bool
	 */
	public static function isField($fieldName)
	{
		static::check();

		global $sqlFieldsFormat;
		return isset($sqlFieldsFormat[static::getName()][$fieldName]);
	}


	/**
	 * Проверяет наличие колонки
	 *
	 * Аналог isField
	 *
	 * @param string $fieldName Название колонки
	 * @return bool
	 */
	public static function isColl($collName) {
		return static::isField($collName);
	}


	/**
	 * Возвращает массик имен полей
	 *
	 * @return array
	 */
	public static function getFieldsNames() {

		global $sqlFieldsFormat;
		return array_keys($sqlFieldsFormat[static::getName()]);
	}


	/**
	 * Приводит индексы к виду, который указан в списке индексов
	 *
	 * @param string $type Тип индексов
	 * @param array $indexes Список индексов
	 * @throws Exception
	 */
	protected static function makeIndexes($type, $indexes)
	{
		global $wpdb;

		// Массив текущих индексов
		$current_index_arr = array();

		// Запрос информации о текущих индексах
		$current_index_sel = $wpdb->get_results(
			'SHOW INDEX FROM `'.static::getNameWithPrefix() .'`', ARRAY_A
		);

		foreach($current_index_sel as $current_index_row)
		{
			// Если это не первичный ключ (он есть всегда, его учитывать бессмыслено)
			if (($current_index_row['Key_name'] != 'PRIMARY') && (
					(($type == 'index') && ($current_index_row['Non_unique'] == 1)) || // Это индекс типа INDEX и мы обрабатываем индекс типа INDEX
					(($type == 'unique') && ($current_index_row['Non_unique'] == 0)) || // Это индекс типа UNIQUE и мы обрабатываем индекс типа UNIQUE
					($type == 'fulltext' && $current_index_row['Index_type'] == 'FULLTEXT')
				))
			{
				// Добавляем элемент индекса в массив индексов
				$current_index_arr[$current_index_row['Key_name']][$current_index_row['Seq_in_index']] = $current_index_row['Column_name'];
			}
		}


		// Перебираем индексы из параметров
		foreach($indexes as $index_name => $index_colls)
		{
			if (!is_array($index_colls)) {
				$index_colls = [$index_colls];
			}

			// Если имя индекса не указано
			if (is_numeric($index_name))
			{
				$index_name = $type.'_'.$index_name;
			}


			// Нужно создать индекс
			$need_to_create = false;


			// Индекс существует
			if (isset($current_index_arr[$index_name]) && is_array($current_index_arr[$index_name]))
			{
				// Получаем строки полей для сравнения
				// Текущий индекс
				$current_string = implode(',', $current_index_arr[$index_name]);
				// Новый индекс
				$params_string = implode(',', $index_colls);

				// Если строки не совпадают
				if ($current_string != $params_string)
				{
					// Удаление индекса
					$wpdb->query(
						'ALTER TABLE `'.static::getNameWithPrefix().'` DROP INDEX `'.$index_name .'`'
					);

					// Нужно создать индекс заново
					$need_to_create = true;
				}
			}

			// Индекс не существует
			else
			{
				$need_to_create = true;
			}


			// Если нужно создать индекс
			if ($need_to_create)
			{
				// Запрос
				$create_index_query = '';

				// Перебираем поля индекса
				foreach($index_colls as $index_coll)
				{
					// Запятая между полями
					if ($create_index_query != '') { $create_index_query .= ', '; }

					// Добавляем поле в запрос
					$create_index_query .= '`'.$index_coll.'`';
				}

				// Если не удалось добавить индекс
				$create_index_query = 'ALTER TABLE `'.static::getNameWithPrefix().'` ADD '.$type.' `'.$index_name.'` ( '.$create_index_query.' )';
				if (!($wpdb->query($create_index_query)))
				{
					// Выводим ошибку
					throw new Exception($create_index_query . $wpdb->print_error());
				}
			}


			// Удаляем индекс из текущих, чтобы потом его не удалять в удалении оставшихся необработанных
			unset($current_index_arr[$index_name]);
		}


		// Перебираем оставшиеся индексы
		if (is_array($current_index_arr))
		{
			foreach($current_index_arr as $index_name=>$index_params)
			{
				$wpdb->query(
					'ALTER TABLE `'.static::getNameWithPrefix().'` DROP INDEX `' .$index_name.'`'
				);
			}
		}
	}


	/**
	 * Возвращает имя таблицы с префиксом
	 *
	 * @return string
	 */
	public static function getNameWithPrefix()
	{
		global $wpdb;
		return $wpdb->base_prefix.static::$name;
	}


	/**
	 * Возвращает имя таблицы без префикса
	 *
	 * @return string
	 */
	public static function getName()
	{
		return static::$name;
	}


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

	}


	/**
	 * Включить автообновление структуры таблицы
	 *
	 * @returns bool
	 */
	public static function structureUpdateEnable() {

		return true;
	}


	/**
	 * Добавляет тип поля
	 *
	 * @param string $fieldName Имя поля
	 * @param string $type Класс типа поля
	 */
	protected static function addFieldType($fieldName, $type)
	{
		global $sqlFieldsTypes;
		if (isset(static::$types[$type]))
		{
			if (!isset($sqlFieldsTypes[static::getName()]))
				$sqlFieldsTypes[static::getName()] = array();
			$sqlFieldsTypes[static::getName()][$fieldName] = static::$types[$type];
		}

		else
		{
			//$sqlFieldsTypes[static::getName()][$fieldName] = SqlTypeBase::class;
		}
	}


	/**
	 * Возвращает класс типа поля для поля
	 *
	 * @param string $fieldName Имя поля
	 * @return SqlTypeBase
	 */
	protected static function getFieldType($fieldName)
	{
		global $sqlFieldsTypes;

		if (isset($sqlFieldsTypes[static::getName()][$fieldName]))
		{
			return $sqlFieldsTypes[static::getName()][$fieldName];
		}
	}


	/**
	 * Преобразует данные из базы для использования в php
	 *
	 * @param string $fieldName Имя поля
	 * @param mixed $fieldValue Значение
	 * @return mixed
	 */
	protected static function sqlToValue($fieldName, $fieldValue)
	{
		global $sqlFieldsTypes;

		if (isset($sqlFieldsTypes[static::getName()][$fieldName]))
		{
			$class = $sqlFieldsTypes[static::getName()][$fieldName];
			return $class::sqlToValue($fieldValue);
		}

		return $fieldValue;
	}


	/**
	 * Преобразует данные для сохранения в базе
	 *
	 * @param string $fieldName Имя поля
	 * @param mixed $fieldValue Значение
	 * @return mixed
	 */
	protected static function valueToSql($fieldName, $fieldValue)
	{
		global $sqlFieldsTypes;
		if (isset($sqlFieldsTypes[static::getName()][$fieldName]))
		{
			$class = $sqlFieldsTypes[static::getName()][$fieldName];
			return $class::valueToSql($fieldValue);
		}

		return $fieldValue;
	}


	/**
	 * Преобразует строку данных из базы для использования в php
	 *
	 * @param array $row Строка с данными
	 * @return array
	 */
	protected static function sqlToValueRow($row)
	{
		foreach($row as $fieldName=>$fieldValue)
		{
			$row[$fieldName] = static::sqlToValue($fieldName, $fieldValue);
		}

		// Метка о том, что эти данные из базы
		// По этой метке потом можно понять, что ни не новые
		// Это нужно например для того, чтобы при сохранении объектов проверять новые
		// они или нет. Бывает, что для новых объектов нужно установить при сохранении
		// дату создания или другой параметр по-умолчанию, которые необходимо
		// установить только при создании
		$row['_from_db'] = true;

		return $row;
	}


	/**
	 * Преобразует строку данных для сохранения в базе
	 *
	 * @param array $row Строка с данными
	 * @return array
	 */
	protected static function valueToSqlRow($row)
	{
		if (!is_array($row) && WP_DEBUG) {
			echo '$row: ';
			var_dump($row);
			throw new Exception('Invalid argument supplied for foreach');
		}

		foreach($row as $fieldName=>$fieldValue)
		{
			if (static::isField($fieldName)) {
				$row[$fieldName] = static::valueToSql($fieldName, $fieldValue);
			}
			else {
				unset($row[$fieldName]);
			}
		}

		return $row;
	}


	/**
	 * Преобразует список строк из базы для использования в php
	 *
	 * @param array $list Список с данными
	 * @return array
	 */
	protected static function sqlToValueList($list)
	{
		foreach($list as $n=>$row)
		{
			$list[$n] = static::sqlToValueRow($row);
		}

		return $list;
	}


	/**
	 * Преобразует список строк с данными для сохранения в базе
	 *
	 * @param array $list Список с данными
	 * @return array
	 */
	protected static function valueToSqlList($list)
	{
		foreach($list as $n=>$row)
		{
			$list[$n] = static::valueToSqlRow($row);
		}

		return $list;
	}


	/**
	 * Возвращает данные для вставки в \Wdpro\Form\Element\Select
	 *
	 * в качестве параметра 'options',
	 * добавляя таким образом список option
	 *
	 * @param array $params Параметры
	 * @return array
	 */
	public static function getOptions($params)
	{
		if (!$params['start'])
		{
			$params['start'] = array();
		}

		if ($sel = static::select($params['where'], $params['fields']))
		{
			foreach($sel as $row)
			{
				unset($row['_from_db']);
				$row = array_values($row);
				$text = '';

				if (isset($row[1])) {
					$i = 1;
					while(isset($row[$i]))
					{
						if ($text != '')
						{
							$text .= ' - ';
						}
						$text .= $row[$i];

						$i ++;
					}
				}
				else {
					$text = $row[0];
				}
				$params['start'][$row[0]] = $text;
			}
		}

		return $params['start'];
	}


	/**
	 * Сохраняет формат полей
	 *
	 * @param array $format
	 */
	protected static function setFieldsFormat($format)
	{
		global $sqlFieldsFormat;
		$sqlFieldsFormat[static::getName()] = $format;
	}


	/**
	 * Возвращает формат полей
	 *
	 * @return array
	 */
	protected static function getFieldsFormat()
	{
		global $sqlFieldsFormat;
		return $sqlFieldsFormat[static::getName()];
	}


	/**
	 * Возвращает формат одного поля
	 *
	 * @param $key
	 * @return mixed
	 */
	protected static function getFieldFormat($key)
	{
		global $sqlFieldsFormat;
		if (isset($sqlFieldsFormat[static::getName()][$key]))
		{
			return $sqlFieldsFormat[static::getName()][$key];
		}
		else
		{
			return '%s';
		}
	}


	/**
	 * Возвращает поля, которые имеют языки
	 *
	 * Этот массив содержит поля таблицы вместе с [lang], чтобы потом можно было
	 * определить, какие данные преобразовывать к текущему языку. Например, открывается
	 * страница на английском. И чтобы у нее post_title, post_content и другие штуки были
	 * наанглийском, нужно определить, какие данные имеют перевод. И как раз для этого
	 * нуженэтот массив
	 *
	 * @return array
	 */
	public static function getLangsFields() {
		$langFields = static::getStatic('fieldsLang');
		if (!$langFields) $langFields = [];
		return $langFields;
//		return static::$fieldsLang;
	}

}


$sqlFieldsFormat = array();


class TableException extends \Wdpro\Exception
{

}


/**
 * Тип поля по-умолчанию
 *
 * Class TypeDefault
 * @package Wdpro
 */
class SqlTypeBase
{
	/**
	 * Преобразование значения для сохранения в базе
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public static function valueToSql($value)
	{
		return $value;
	}


	/**
	 * Преобразование значения из базы для использования в php
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public static function sqlToValue($value)
	{
		return $value;
	}


	public static function getType($fieldType)
	{
		return $fieldType;
	}


	public static function getParams($fieldParams)
	{
		return $fieldParams;
	}
}


/**
 * Тип Json
 *
 * @package Wdpro
 */
class SqlTypeJson extends SqlTypeBase
{
	public static function valueToSql($value)
	{
		return wdpro_json_encode($value);
	}


	public static function sqlToValue($value)
	{
		return wdpro_json_decode($value);
	}


	public static function getType($fieldType)
	{
		return 'longtext';
	}


	public static function getParams($fieldParams)
	{
		return '';
	}
}
