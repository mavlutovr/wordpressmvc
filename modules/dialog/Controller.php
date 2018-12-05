<?php
namespace Wdpro\Dialog;

class Controller extends \Wdpro\BaseController {

	public static function init() {
		//wp_enqueue_script("jquery-effects-core");
		add_action('wp_enqueue_scripts', function ()
		{
			wp_enqueue_script("jquery-ui-draggable");
		});
	}
}

return __NAMESPACE__;