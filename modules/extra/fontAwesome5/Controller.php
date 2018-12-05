<?php
namespace Wdpro\Extra\FontAwesome5;

class Controller extends \Wdpro\BaseController {
	
	public static function runSite() {

		if (wdpro_get_option('wdpro_font_awesome_5_site'))
			wdpro_add_css_to_site(__DIR__.'/css/all.min.css');
	}




	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки:
	 * https://developer.wordpress.org/resource/dashicons/#forms
	 * https://fontawesome.com/v4.7.0/icons/
	 */
	public static function initConsole()
	{
		// Подключение шрифта к админке
		if (wdpro_get_option('wdpro_font_awesome_5_console', 1)) {
			wdpro_add_css_to_console(__DIR__.'/css/all.min.css');
		}


		// Настройки
		\Wdpro\Console\Menu::addSettings('Font Awesome 5', function ($form) {

			/** @var \Wdpro\Form\Form $form */

			$form->add([
				'name'=>'wdpro_font_awesome_5_site',
				'right'=>'Подключить к сайту',
				'type'=>$form::CHECK,
			]);

			$form->add([
				'name'=>'wdpro_font_awesome_5_console',
				'right'=>'Подключить к админке',
				'type'=>$form::CHECK,
			]);

			$form->add($form::SUBMIT_SAVE);

			return $form;
		});
	}


}

return __NAMESPACE__;