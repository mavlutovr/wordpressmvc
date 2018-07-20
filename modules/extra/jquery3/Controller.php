<?php
namespace Wdpro\Extra\Jquery3;

class Controller extends \Wdpro\BaseController {

	public static function init() {

		function replace_core_jquery_version() {
		    wp_deregister_script( 'jquery' );
		    wp_register_script( 'jquery', WDPRO_URL.'modules/extra/jquery3/jquery-3.3.1.min.js', array(), '3.1.1' );
		}
		add_action( 'wp_enqueue_scripts', 'replace_core_jquery_version' );
		add_action( 'admin_enqueue_scripts', 'replace_core_jquery_version' );
	}
}

return __NAMESPACE__;