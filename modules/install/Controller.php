<?php
namespace Wdpro\Install;

class Controller extends \Wdpro\BaseController {

	public static function runConsole () {

		if (wdpro_get_option('wdpro_create_app')) {

			// Приложение app
			if (!is_dir(__DIR__.'/../../../app')) {

			}

		}

		\Wdpro\Console\Menu::addSettings([
			'label'=>'Wordpress MVC2',
			'open'=>function () {
			}
		]);
	}


}

return __NAMESPACE__;