<?php
namespace Wdpro\Tools;

class Controller extends \Wdpro\BaseController {


	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки:
	 * https://developer.wordpress.org/resource/dashicons/#forms
	 * http://fontawesome.io/icon/file-o/
	 */
	public static function initConsole () {

		// Обновление сортировки
		wdpro_ajax('wdpro_sorting', function () {

			$get = $_POST['get'];

			if (isset($get['post_type']) && isset($_POST['change']['row'])) {
				$entity = wdpro_get_entity_class_by_post_type($get['post_type']);
				/** @var $entity \App\BasePage */

				$roll = $entity::getConsoleRoll();

				return [
					'update'=>$roll->updateSorting($_POST)
				];
			}
		});
	}


	public static function getOrderColumnHeader() {
		return '<span class="js-wdpro-sorting"></span>';
	}


	public static function getOrderColumnRow($postId) {
		echo '<div class="js-wdpro-sorting-number wdpro-sorting-number"
data-id="'.$postId.'">'
		     .get_post_field( 'menu_order', $postId ) . '</div>';
	}

	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole () {
		wdpro_add_script_to_console(__DIR__.'/sorting/sorting.console.js');
	}


}

return __NAMESPACE__;