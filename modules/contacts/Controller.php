<?php
namespace Wdpro\Contacts;

class Controller extends \Wdpro\BaseController {
	
	/** @var  \Wdpro\Form\Form */
	protected static $backFormClass = BackForm::class;

	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole() {

		// Меню - Контакты
		\Wdpro\Console\Menu::add(array(
			'roll'=>ConsoleRoll::class,
			'n'=>95,
			'icon'=>'dashicons-location',
		));
		
		// Настройки
		\Wdpro\Console\Menu::addSettings('Страница &quot;Контакты&quot;', function ($form) {
			
			/** @var \Wdpro\Form\Form $form */
			$form->add(array(
				'name'=>'contacts_form_sended',
				'top'=>'Текст после отправки формы',
				'type'=>'ckeditor',
			));
			$form->add('submitSave');
			
			return $form;
		});
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {

		add_shortcode('contacts_list', function ($params) {
			
			return Roll::getHtml('ORDER BY sorting');
		});
		
		add_shortcode('contacts_form', function () {
			
			/** @var \Wdpro\Form\Form $form */
			$form = new static::$backFormClass();
			
			return $form->getHtml();
		});
	}


	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function init() {

		// Отправка формы
		wdpro_ajax('contactsBack', function ($data) {

			/** @var \Wdpro\Form\Form $form */
			$form = new static::$backFormClass();
			
			$form->setData($data);
			$form->sendToAdmins('Форма обратной связи');
			
			return array(
				'message'=>get_option('contacts_form_sended'),
			);
		});
	}


}

return __NAMESPACE__;