<?php
namespace Wdpro\AdminNotice;

class Controller extends \Wdpro\BaseController {

	/**
	 * Отправляет сообщения администраторам
	 * 
	 * @param string $subject Тема сообщения
	 * @param string $messageInHtmlFormat Текст сообщения в формате html
	 */
	public static function sendMessageHtml($subject, $messageInHtmlFormat) {

		if ($emails = static::getAdminEmails()) {
			foreach ($emails as $email) {
				\Wdpro\Sender\Controller::sendEmail(
					$email,
					$_SERVER['HTTP_HOST'].' - '.$subject,
					$messageInHtmlFormat);
			}
		}
	}


	public static function sendException($err) {
		\Wdpro\AdminNotice\Controller::sendMessageHtml(
			'Error: '.$err->getMessage(),
			'<h1>'.$err->getMessage().'</h1>'
			.'<pre>'.print_r($err, true).'</pre>'
		);
	}


	/**
	 * Возвращает массив ящиков для уведомлений
	 *
	 * Например для того, чтобы потом отправить на них письмо
	 *
	 * @return array
	 */
	public static function getAdminEmails() {

		if ($sel = SqlTable::select('ORDER BY sorting'))
		{
			$emails = [];

			foreach($sel as $row)
			{
				$emails[] = $row['email'];
			}

			return $emails;
		}
	}
	
	
	public static function run() {
		
		if (is_admin())
		{
			\Wdpro\Console\menu::add(array(
				'roll'=>ConsoleRoll::class,
				'icon'=>'dashicons-email',
				'position'=>'settings',
			));
		}
	}
}


return __NAMESPACE__;