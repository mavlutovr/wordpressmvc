<?php
namespace Wdpro\Extra\JqueryUiStyle;

class Controller extends \Wdpro\BaseController {
	/**
	 * Инициализация модуля
	 */
	public static function init()
	{
		wdpro_add_css_to_site(__DIR__.'/jquery-ui.css');
	}


}

return __NAMESPACE__;