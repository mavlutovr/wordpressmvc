<?php
namespace Wdpro\Blog;

class Roll extends \Wdpro\Site\Roll {

	/**
	 * Необходимые для списка поля
	 *
	 * @return string
	 * @example return "ID, post_title";
	 */
	public static function sqlFields__DELETE() {

		return '`ID`, `post_name`, `post_title`, `post_date`, `anons`';
	}


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
	 * Возвращает объект постраничности для использования в списке
	 *
	 * @return void|\Wdpro\Tools\Pagination
	 */
	public static function pagination() {

		return new \Wdpro\Tools\Pagination();
	}


}