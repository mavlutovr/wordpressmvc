<?php
namespace Wdpro\Extra\Scrollto;

class Controller extends \Wdpro\BaseController {
	/**
	 * Инициализация модуля
	 */
	public static function init () {
		wdpro_add_script_to_site(__DIR__.'/../../../js/jquery.scrollTo.js');
	}


}

return __NAMESPACE__;