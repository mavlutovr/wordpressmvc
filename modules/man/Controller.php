<?php
namespace Wdpro\Man;

class Controller extends \Wdpro\BaseController {


	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки: https://developer.wordpress.org/resource/dashicons/#forms
	 */
	public static function initConsole() {

		\Wdpro\Console\Menu::add([
			'roll'=>ConsoleRoll::class,
			'n'=>100,
		]);


		// http://localhost/giraffes.ru/wp-admin/admin.php?page=Wdpro.Man.ConsoleRoll
		// http://localhost/giraffes.ru/wp-admin/admin.php?page=Wdpro.Man.ConsoleRoll&id=4&action=form
	}


	/**
	 * Возвращает Options с шаблонами для Select
	 *
	 * @return array
	 */
	public static function getTemplatesOptions() {
		$options = [''=>''];

		$files = scandir(__DIR__.'/templates');
		foreach ($files as $file) {

			if ($fileData = static::getTemplateData($file)) {
				$options[$file] = $fileData['name'];
			}
		}

		return $options;
	}


	/**
	 * Возвращает данные шаблона
	 *
	 * @param string $file Имя файла
	 * @return array
	 */
	public static function getTemplateData($file) {
		$filePath = __DIR__.'/templates/'.$file;

		if (is_file($filePath)) {
			return require($filePath);
		}
	}
}

return __NAMESPACE__;