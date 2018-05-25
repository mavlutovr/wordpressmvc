<?php
namespace Wdpro\Lang;

class Controller extends \Wdpro\BaseController {



	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole () {
		\Wdpro\Console\Menu::add([
			'position'=>'settings',
			'roll'=>ConsoleRoll::class,
			'n'=>1000,
		]);
	}


	/**
	 * Обновление данных о языка
	 */
	public static function updateData() {
		if ($sel = SqlTable::select('ORDER BY sorting')) {
			Data::setData($sel);
		}
	}

}


return __NAMESPACE__;