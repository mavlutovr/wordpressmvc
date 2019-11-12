<?php
namespace Wdpro\Man;

class Controller extends \Wdpro\BaseController {


	protected static $buttonText;


	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки: https://developer.wordpress.org/resource/dashicons/#forms
	 */
	public static function initConsole() {

		\Wdpro\Console\Menu::add([
			'roll'=>ConsoleRoll::class,
			'n'=>-10,
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


	/**
	 * Задает текст кнопки справки в меню админки
	 *
	 * Чтобы стандартный текст кнопки "Справка по ТЕКУЩИЙ_ДОМЕН" можно было поменять на другой
	 *
	 * @param string $buttonText Текст кнопки
	 */
	public static function setButtonText($buttonText) {
		static::$buttonText = $buttonText;
	}


	/**
	 * Возвращает текст для кнопки меню в админке "Справка по сайту"
	 *
	 * @return string
	 */
	public static function getButtonText() {
		if (isset(static::$buttonText) && static::$buttonText)
			return static::$buttonText;

		return 'Справка по '.$_SERVER['HTTP_HOST'];
	}
}

return __NAMESPACE__;