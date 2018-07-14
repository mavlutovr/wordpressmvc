<?php
namespace Wdpro\Dialog;

class Controller extends \Wdpro\BaseController {

	public static function init() {
		//wp_enqueue_script("jquery-effects-core");
		add_action('wp_enqueue_scripts', function ()
		{
			wp_enqueue_script("jquery-ui-draggable");
			//wp_enqueue_script("jquery-ui-core");
			//wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		});
	}
}

return __NAMESPACE__;