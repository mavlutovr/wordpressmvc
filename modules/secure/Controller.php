<?php
namespace Wdpro\Secure;

class Controller extends \Wdpro\BaseController {

	/**
	 * Инициализация модуля
	 */
	public static function init()
	{
		// Блокировка
		if (wdpro_get_option('wdpro_secure_errors_block')) {
			$seconds = wdpro_get_option('wdpro_secure_errors_time', 60);
			$minSeconds = time() - $seconds;

			$maxErrors = wdpro_get_option('wdpro_secure_errors_n', 3);

			if (SqlTable::count([
				'WHERE time>%d AND ip=%s',
				[$minSeconds, wdpro_get_visitor_ip()]
			]) >= $maxErrors) {

				header('HTTP/1.0 403 Forbidden');
				echo '<h1>Ошибка безопасности</h1>
							<p>Подождите '.$seconds.' секунд. И затем повторите попытку.</p>';

				exit();
			}
		}

		// Вход в админку
		add_filter('login_errors', function ($message) {

			static::error('Вход в админку - Неверный пароль', $_POST['log'].'<BR>'.$_POST['pwd']);

			return $message;
		});
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole()
	{
		\Wdpro\Console\Menu::addToSettings(ConsoleRoll::class);
	}


	/**
	 * Добавляет ошибку
	 *
	 * Чтобы после определенного количества ошибок заблокировать ip
	 *
	 * @param string $message Сообщение
	 * @param string $text
	 * @throws \Wdpro\EntityException
	 */
	public static function error($message, $text='') {

		// Добавление ошибки в базу
		$entity = new Entity([
			'message'=>$message,
			'text'=>$text,
			'time'=>time(),
			'ip'=>wdpro_get_visitor_ip(),
		]);

		$entity->save();

		// Отправка сообщения админу
		\Wdpro\AdminNotice\Controller::sendMessageHtml(
			'Ошибка безопасности',
			$message
			.'<p>'.$text.'</p>'
		);
	}
}


return __NAMESPACE__;