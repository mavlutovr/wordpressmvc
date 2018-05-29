<?php
namespace Wdpro\Contacts;


class Roll extends \Wdpro\Site\Roll {

	/**
	 * Дополнительная обработка данных для шаблона
	 *
	 * @param array $row Строка из базы
	 * @return array Строка для шаблона
	 */
	public static function prepareDataForTemplate( $row ) {

		return parent::prepareDataForTemplate( $row );
	}


	/**
	 * Возвращает адрес php файла
	 *
	 * @return string
	 * @example return WDPRO_TEMPLATE_PATH.'catalog_list.php';
	 */
	public static function getTemplatePhpFile() {

		return WDPRO_TEMPLATE_PATH.'contacts_list.php';
	}


	/**
	 * Класс таблицы, из которой происходит выборка
	 *
	 * @return \Wdpro\BaseSqlTable
	 */
	public static function sqlTable() {

		return SqlTable::class;
	}


	/**
	 * Необходимые для списка поля
	 *
	 * @return string
	 * @example return "ID, post_title";
	 */
	public static function sqlFields () {
		return 'id, text[lang] as text, map';
	}


}