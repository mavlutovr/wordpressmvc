<?php
namespace Wdpro\Extra\OwlCarousel;


class Controller extends \Wdpro\BaseController {
	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite()
	{
		wdpro_add_script_to_site(__DIR__.'/owl.carousel.min.js');
		wdpro_add_css_to_site(__DIR__.'/assets/owl.carousel.min.css');
		//wdpro_add_css_to_site(__DIR__.'/assets/owl.theme.default.css');
	}


}

return __NAMESPACE__;