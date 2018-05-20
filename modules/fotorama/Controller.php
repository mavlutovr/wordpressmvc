<?php
namespace Wdpro\Fotorama;

class Controller extends \Wdpro\BaseController {

	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite() {

		wdpro_add_css_to_site_external('http://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.4/fotorama.css');
		wdpro_add_script_to_site_external('http://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.4/fotorama.js');
	}


}


return __NAMESPACE__;