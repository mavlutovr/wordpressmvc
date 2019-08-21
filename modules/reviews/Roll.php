<?php

namespace Wdpro\Reviews;

class Roll extends \Wdpro\Site\Roll {

	// Файл шаблона списка
	public static function getTemplatePhpFile()
	{
		return WDPRO_TEMPLATE_PATH.'reviews_list.php';
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
		return 'id, text[lang] as text, name, avatar';
	}


}