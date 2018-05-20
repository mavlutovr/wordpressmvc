<?php
namespace Wdpro\Libs\Bootstrap;

class Controller extends \Wdpro\BaseController {

	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite() {

		wdpro_add_css_to_site(__DIR__.'/css/bootstrap.min.css');
		wdpro_add_script_to_site(__DIR__.'/js/bootstrap.min.js');
	}


}


return __NAMESPACE__;