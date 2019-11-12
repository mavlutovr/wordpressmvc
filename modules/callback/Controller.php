<?php
namespace Wdpro\Callback;

class Controller extends \Wdpro\BaseController {
	
	public static function init() {

		\Wdpro\Autoload::add('Wdpro\Callback', __DIR__);

		// Подключаем окошки
		\Wdpro\Modules::addWdpro('dialog');

		SqlTable::init();

		// Ajax
		wdpro_ajax('callback-form', function ()
		{
			$data = $_POST;
			if (!$data['form_text']) {
				$data['form_text'] = '<p>Имя: '.$data['name'].'</p>'
				                   .'<p>Телефон: '.$data['phone'].'</p>';
			}

			// Сохраняем
			$callback = new Entity();
			$callback->setData(array(
				'data'=>$data,
			));
			$callback->save();
			
			// Отправляем сообщение админам
			\Wdpro\AdminNotice\Controller::sendMessageHtml(
				'Заказ обратного звонка',
				$data['form_text']
			);
			
			// Отправляем событие о заказе звонка
			do_action('wdpro_callback', [
				'data'=>$data,
			]);

			return array(
				'message'=>wdpro_get_option('callback_message'),
				'title'=>wdpro_get_option('callback_title'),
			);
		});

	}




	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole() {

		// Кнопка меню
		\Wdpro\Console\Menu::add(array(
			'roll'=>ConsoleRoll::class,
			'icon'=>'dashicons-phone',
			'position'=>30,
			'showNew'=>true,
		));


		// Настройки
		\Wdpro\Console\Menu::addSettings('Заказ звонков', function ($form) {

			/** @var \Wdpro\Form\Form $form */

			$form->add([
				'name'=>'callback_title',
				'top'=>'Заголовок после отправки формы',
			]);

			$form->add(array(
				'name'=>'callback_message',
				'top'=>'Текст после отправки формы',
				'type'=>'ckeditor',
			));

			$form->add('submitSave');

			return $form;
		});
	}


}


return __NAMESPACE__;