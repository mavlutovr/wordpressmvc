<?php
namespace Wdpro\Dev;

class Controller extends \Wdpro\BaseController {

	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки: https://developer.wordpress.org/resource/dashicons/#forms
	 */
	public static function initConsole() {


		if (wdpro_get_option('wdpro_dev_mode') ||
		    (defined('WDPRO_DEV_MODE') &&  WDPRO_DEV_MODE))
		{
			\Wdpro\Console\Menu::add([
				'roll'=>ConsoleRoll::class,
				'n'=>0,
			]);
		}
	}


}


return __NAMESPACE__;