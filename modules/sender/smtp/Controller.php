<?php
namespace Wdpro\Sender\Smtp;

class Controller extends \Wdpro\BaseController {
	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки:
	 * https://developer.wordpress.org/resource/dashicons/#forms
	 * https://fontawesome.com/v4.7.0/icons/
	 */
	public static function initConsole () {
		\Wdpro\Console\Menu::add([
			'roll'=>ConsoleRoll::class,
			'n'=>100,
		]);
	}


	/**
	 * Возвращает options для select c ящиками отправителей
	 *
	 * @return array
	 */
	public static function getOptions() {
		$options = [''=>''];

		if ($sel = SqlTable::select('ORDER BY sorting')) {
			foreach ( $sel as $item ) {
				$options[$item['id']] = $item['mail'];
			}
		}

		return $options;
	}

}


return __NAMESPACE__;