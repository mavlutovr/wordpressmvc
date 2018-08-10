<?php
namespace Wdpro\Extra\FontAwesome5;

class Controller extends \Wdpro\BaseController {
	
	public static function runSite() {
		
		wdpro_add_css_to_site(__DIR__.'/css/all.min.css');
	}
}

return __NAMESPACE__;