<?php
namespace App\Menu;

class Controller extends \Wdpro\BaseController {

	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки: https://developer.wordpress.org/resource/dashicons/#forms
	 */
	public static function initConsole() {

		// Настройки
		\Wdpro\Console\Menu::addSettings('Страницы', function ($form) {
			
			/** @var \Wdpro\Form\Form $form */

			$form->add([
				'name'=>'slogan',
				'top'=>'Слоган в шапке',
			]);
			
			$form->add($form::SUBMIT_SAVE);
			
			return $form;
		});
		
		// Меню в админке
		\Wdpro\Console\Menu::add([
			'roll'=>ConsoleRoll::class,
			'n'=>50
		]);
	}


}


return __NAMESPACE__;