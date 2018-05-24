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
		
		if ($sel = SqlTable::select('ORDER BY sorting'))
		{
			foreach($sel as $row)
			{
				\Wdpro\Sender\Controller::sendEmail(
					$row['email'],
					$_SERVER['HTTP_HOST'].' - '.$subject,
					$messageInHtmlFormat);
			}
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