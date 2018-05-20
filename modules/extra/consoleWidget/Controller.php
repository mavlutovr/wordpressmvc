<?php
namespace Wdpro\Extra\ConsoleWidget;

class Controller extends \Wdpro\BaseController {

	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки: https://developer.wordpress.org/resource/dashicons/#forms
	 */
	public static function initConsole() {

		add_action('wp_dashboard_setup',
			function () {

				wp_add_dashboard_widget('wdpro_info_widget',
					'Коллекция крутых плагинов',
					function () {

						echo wdpro_render_php(__DIR__ 
							. '/templates/plugins.template.php');
					});
			});

	}


}


return __NAMESPACE__;