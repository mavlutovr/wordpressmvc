<?php
namespace Wdpro\Extra\IframePing;


class Controller extends \Wdpro\BaseController {
	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite()
	{
		add_shortcode('iframe_ping', function ($params) {

			return '<div style="position: absolute; left: -1000px; width: 1px; overflow: hidden"><iframe src="'.$params['src'].'"></iframe></div>';
		});
	}


}

return __NAMESPACE__;