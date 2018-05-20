<?php
namespace Wdpro\Uppod;

class Controller extends \Wdpro\BaseController {

	public static function initSite() {

		//wdpro_add_script_to_site(__DIR__.'/../../js/swfobject.js');
		wdpro_add_script_to_site(__DIR__.'/js/uppod-0.13.04.js');
	}


	/**
	 * Возвращает url файла uppod.swf
	 *
	 * @return string
	 */
	public static function getUppodFileUrl() {

		return WDPRO_URL.'modules/uppod/uppod.swf';
	}
}


return __NAMESPACE__;
