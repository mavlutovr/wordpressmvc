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
			'position'=>'settings',
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
	 *
	 * или
	 *
	 * <pre>[
	 * // Шаблон по-умолчанию
	 *  'default'=>function () { return [
	 *    'subject'=>'Тема письма',
	 *    'text'=>'Текст письма',
	 *    'info'=>'Информация',
	 *  ] },
	 * ]</pre>
	 *
	 * @return void|Entity
	 * @throws \Exception
	 */
	public static function getTemplate($name, $params=null) {

		if (isset($params['default'])) {

			if (is_callable($params['default'])) {
				$default = $params['default'];
				$params['default'] = function () use (&$name, &$default) {
					$return = $default();
					$return['name'] = $name;
					return $return;
				};
			}

			else if (is_array($params['default'])) {
				$params['default']['name'] = $name;
			}
		}
		
		// Получаем шаблон
		$templateData = SqlTable::getRow(
			[ 'WHERE name=%s ', [$name] ],
			'*',
			isset($params['default']) ? $params['default'] : null
		);

		if (!$templateData) {
			$templateData['id'] = SqlTable::insert($templateData);
		}

		return wdpro_object(Entity::class, $templateData);
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

		$data = apply_filters('wdpro_sender_templates_email_data', $data);
		
		if (is_array($emails)
			&& $template = static::getTemplate($name, $params)) {
			
			foreach($emails as $email) {
				
				$email = trim($email);
				$template->send($email, $data);
			}
		}
	}
}

return __NAMESPACE__;