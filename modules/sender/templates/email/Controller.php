<?php
namespace Wdpro\Sender\Templates\Email;


/**
 * Шаблоны E-mail писем
 * 
 * @package Wdpro\Sender\Templates\Email
 */
class Controller extends \Wdpro\BaseController {
	
	
	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole() {

		// Список шаблонов
		\Wdpro\Console\Menu::add(array(
			'n'=>100,
			'roll'=>ConsoleRoll::class,
		));
	}


	/**
	 * Возвращает шаблон по имени
	 *
	 * @param string $name Имя шаблона
	 * @param null $params Всякие параметры, например,
	 * <pre>[
	 * // Шаблон по-умолчанию
	 * 'default'=>[
	 *  'subject'=>'Тема письма',
	 *  'text'=>'Текст письма',
	 *  'info'=>'Информация',
	 * ]]</pre>
	 * @return void|Entity
	 * @throws \Exception
	 */
	public static function getTemplate($name, $params=null) {

		if (isset($params['default'])) {
			
			$params['default']['name'] = $name;
		}
		
		// Шаблон из базы
		if ($templateData = SqlTable::getRow(
			['WHERE name=%s ', [$name]],
			'*',
			isset($params['default']) ? $params['default'] : null
		)) {

			return wdpro_object(Entity::class, $templateData);
		}
	}


	/**
	 * Отправка письма
	 *
	 * @param string $name Имя шаблона
	 * @param array|string $emails Ящики, на которые надо отправить письмо
	 * Может быть массив, а может быть строкой, где ящики через запятую
	 * @param null|array $data Данные для письма
	 * @param null $params Всякие параметры, например, 
	 * <pre>[
	 * // Шаблон по-умолчанию
	 * 'default'=>[
	 *  'subject'=>'Тема письма',
	 *  'text'=>'Текст письма',
	 *  'info'=>'Информация',
	 * ]]</pre>
	 */
	public static function send($name, $emails, $data=null, $params=null) {
		
		if (is_string($emails)) {
			$emails = explode(',', $emails);
		}
		
		if ($template = static::getTemplate($name, $params)) {
			
			foreach($emails as $email) {
				
				$email = trim($email);
				$template->send($email, $data);
			}
		}
	}
}

return __NAMESPACE__;