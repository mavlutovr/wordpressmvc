<?php
namespace Wdpro\Blog;

class Roll extends \Wdpro\Site\Roll {

	protected static $paginationParams;


	/**
	 * Необходимые для списка поля
	 *
	 * @return string
	 * @example return "ID, post_title";
	 */
	public static function sqlFields () {
		return 'id, post_name, tags[lang] as tags, post_title[lang] as post_title, anons[lang] as anons, date_added, date_edited, image';
	}


	/**
	 * Возвращает адрес php файла
	 *
	 * @return string
	 * @example return WDPRO_TEMPLATE_PATH.'catalog_list.php';
	 */
	public static function getTemplatePhpFile() {

		return WDPRO_TEMPLATE_PATH.'blog_list.php';
	}


	/**
	 * Дополнительная обработка данных для шаблона
	 *
	 * @param array $row Строка из базы
	 *
	 * @return array Строка для шаблона
	 */
	public static function prepareDataForTemplate ($row) {

		if (is_array($row['tags'])
		    && (!count($row['tags']) || !isset($row[0]) || !$row[0])) {
			$row['tags'] = null;
		}

		return $row;
	}


	/**
	 * Возвращает объект постраничности для использования в списке
	 *
	 * @return void|\Wdpro\Tools\Pagination
	 */
	public static function pagination() {

		return new \Wdpro\Tools\Pagination(static::$paginationParams);
	}


	/**
	 * Установить параметры пагинации
	 *
	 * @param array $params Параметры
	 */
	public static function setPaginationParams($params) {
		static::$paginationParams = $params;
	}


}