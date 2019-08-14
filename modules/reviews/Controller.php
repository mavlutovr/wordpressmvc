<?php
namespace Wdpro\Reviews;

class Controller extends \Wdpro\BaseController {

	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки: https://developer.wordpress.org/resource/dashicons/#forms
	 */
	public static function initConsole() {

		/*\Wdpro\Console\Menu::add([
			'roll'=>ConsoleRoll::class,
			'position'=>40,
		]);*/
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite()
	{
		wdpro_default_file(
			__DIR__.'/default/reviews_list.php',
			WDPRO_TEMPLATE_PATH.'reviews_list.php'
		);
	}


}

return __NAMESPACE__;