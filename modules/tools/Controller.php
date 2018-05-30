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

			// Страницы
			if (isset($_POST['change']['row'])) {
				if ( isset($get['post_type']) ) {
					$entity = wdpro_get_entity_class_by_post_type($get['post_type']);
					/** @var $entity \App\BasePage */

					$roll = $entity::getConsoleRoll();

					return [
						'update' => $roll->updateSorting($_POST),
					];
				}

				// Элементы
				else if (isset($get['page'])) {
					$roll = wdpro_get_roll_by_get_page($get['page']);

					return [
						'update' => $roll->updateSorting($_POST),
					];
				}
			}

		});
	}


	/**
	 * Возвращает заголовок для колонки сортировки
	 *
	 * @return string
	 */
	public static function getOrderColumnHeader () {
		return '<span class="js-wdpro-sorting"></span>';
	}


	/**
	 * Возвращает данные для колонки сортировки (Страницы)
	 *
	 * @param number $postId ID поста
	 */
	public static function getOrderColumnRowPost ($postId) {
		echo '<div class="js-wdpro-sorting-number wdpro-sorting-number"
data-id="' . $postId . '">'
		     . get_post_field('menu_order', $postId) . '</div>';
	}


	/**
	 * Возвращает данные для колонки сортировки (Простые элементы)
	 *
	 * @param array $data Данные
	 *
	 * @return string
	 */
	public static function getOrderColumnRowElement ($data) {
		return '<div class="js-wdpro-sorting-number wdpro-sorting-number"
data-id="' . $data['id'] . '">'
		       . $data['sorting'] . '</div>';
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole () {
		wdpro_add_script_to_console(__DIR__ . '/sorting/sorting.console.js');
	}


}

return __NAMESPACE__;