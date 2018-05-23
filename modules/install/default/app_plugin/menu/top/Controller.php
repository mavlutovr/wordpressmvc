<?php
namespace App\Menu\Top;

class Controller extends \Wdpro\BaseController {
	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole () {
		\Wdpro\Console\Menu::add([
			'roll'=>ConsoleRoll::class,
			'n'=>30,
		]);

	}

	/**
	 * Возвращает массив с данными кнопок для меню
	 *
	 * @return array
	 */
	public static function getMenuData() {
		return SqlTable::select(
			'WHERE `post_status` = "publish" AND `post_parent`=0
			ORDER BY `menu_order`',
			'`post_title`, `post_name`, id'
		);
	}
}


return __NAMESPACE__;