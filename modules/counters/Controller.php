<?php
namespace Wdpro\Counters;

class Controller extends \Wdpro\BaseController {

	/**
	 * Инициализация модуля
	 */
	public static function init()
	{
		wdpro_ajax('countersTest', function () {
			\Wdpro\AdminNotice\Controller::sendMessageHtml('Юзер агент',
				print_r($_GET, 1).PHP_EOL.PHP_EOL
			.print_r($_POST, 1));
		});
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole() {

		\Wdpro\Console\Menu::add(array(
			'roll'=>ConsoleRoll::class,
			'position'=>'settings',
			'settings'=>true,
		));
	}


	/**
	 * Возвращает Html код счетчиков
	 * 
	 * @return string
	 */
	public static function getCountersHtml() {

		$counters = '';
		$page = wdpro_current_page();
		$counters .= $page->getData('counters');


		if ($sel = SqlTable::select('ORDER BY sorting')) {

			foreach($sel as $row) {
				$counters .= $row['code'];
			}
		}

		if ($counters) {
			return $counters;
			//return '<noindex>'.$counters.'</noindex>';
		}
	}
}


return __NAMESPACE__;