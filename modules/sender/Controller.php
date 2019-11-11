<?php
namespace Wdpro\Sender;

register_activation_hook(__FILE__, array( __NAMESPACE__.'\\Killa', 'cron' ) );

class fakemailer {
	public function Send() {

	}
}

/**
 * Отправкаа сообщения на e-mail и в перспективе на другие штуки, типа sms
 * 
 * @package Wdpro\Sender
 */
class Controller extends \Wdpro\BaseController
{
	protected static $n=1;
	protected static $debug = false;
	
	public static function init() {
		
		// Включение отправки писем через SMTP
		add_action('phpmailer_init', function ($phpmailer) {
			
			/** @var \PHPMailer $phpmailer */

			// Отправка на почту
			if (strstr($phpmailer->Subject, 'CCRROONN_SSEENNDD::')
			    || get_option('wdpro_send_email_by_cron') != 1) {

				$smtpSetted = false;

				$phpmailer->Subject = str_replace('CCRROONN_SSEENNDD::', '',
					$phpmailer->Subject);

				// Не стандартный SMTP
				// Другой SMTP
				$smtp = null;
				if (strstr($phpmailer->Subject, ':::SMTP:')) {
					preg_match('~'
					           .preg_quote(':::SMTP:')
					           .'([0-9]+)'
					           .preg_quote(':::')
					           .'~', $phpmailer->Subject,
						$arr);

					if ($arr[1]) {
						$phpmailer->Subject = str_replace(':::SMTP:'.$arr[1].':::', '', $phpmailer->Subject);
						global $wdproTempSmtp;
						$smtp = $wdproTempSmtp[$arr[1]];

						$email = $smtp['mail'];
						$server = $smtp['server'];
						$port = $smtp['port'];
						$login = $smtp['login'];
						$pass = $smtp['pass'];
						$protocol = $smtp['protocol'];
						$fromName = $smtp['from_name'];
						$smtpSetted = true;
					}
				}

				$arr = [];
				// Странный не доделанный SMTP, наверное он здесь не нужен
				if (strstr($phpmailer->Subject, '::SMTP_FROM_START::')) {
					$arr = [];
					if ( preg_match('~' . preg_quote('::SMTP_FROM_START::')
					                . '([\s\S.]+)'
					                . preg_quote('::SMTP_FROM_END::')
					                . '~',
						$phpmailer->Subject,
						$arr
					) ) {
						$fromId = $arr[1];

						// Убираем из тебы лишнее
						$phpmailer->Subject = str_replace(
							'::SMTP_FROM_START::'
							. $fromId
							. '::SMTP_FROM_END::',
							'',
							$phpmailer->Subject
						);

						// Другой SMTP
						if ($smtp) {
							// Получаем данные SMTP
							$email = $smtp['mail'];
							$server = $smtp['server'];
							$port = $smtp['port'];
							$login = $smtp['login'];
							$pass = $smtp['pass'];
							$protocol = $smtp['protocol'];
							$fromName = $smtp['from_name'];
							$smtpSetted = true;
						}

						// Получаем объект SMTP
						else if ($fromSmtp = \Wdpro\Sender\Smtp\Entity::getEntity
						($fromId)) {
							// Получаем данные SMTP
							$email = $fromSmtp->data['mail'];
							$server = $fromSmtp->data['server'];
							$port = $fromSmtp->data['port'];
							$login = $fromSmtp->data['login'];
							$pass = $fromSmtp->data['pass'];
							$protocol = $fromSmtp->data['protocol'];
							$fromName = $fromSmtp->data['from_name'];
							$smtpSetted = true;
						}
					}
				}

				// SMTP
				if (get_option('wdpro_smtp_enable') == 1) {

					if (!$smtpSetted) {
						$email = get_option('wdpro_smtp_email');
						$server = get_option('wdpro_smtp_server');
						$port = get_option('wdpro_smtp_port');
						$login = get_option('wdpro_smtp_login');
						$pass = get_option('wdpro_smtp_pass');
						$protocol = get_option('wdpro_smtp_protocol');
						$fromName = get_option('wdpro_smtp_from_name');
					}

					if ($server && $port && $login && $email) {

						$phpmailer->setFrom($email);
						$phpmailer->addReplyTo($email);
						$phpmailer->Sender = $email;
						$phpmailer->From = $email;
						$phpmailer->FromName = $fromName;
						//$phpmailer->SMTPDebug = true;

						if (static::$debug) {
							print_r([
								'email'=>$email,
								'server'=>$server,
								'port'=>$port,
								'login'=>$login,
								//'pass'=>$pass,
								'protocol'=>$protocol,
							]);
							print_r($phpmailer);
						}

						$phpmailer->isHTML(true);
						$phpmailer->SMTPAuth = true;
						$phpmailer->Host = $server;
						$phpmailer->Port = $port;
						$phpmailer->Username = $login;
						$phpmailer->Password = $pass;
						$phpmailer->SMTPDebug = static::$debug;
						if ($protocol) $phpmailer->SMTPSecure = $protocol;
						$phpmailer->isSMTP();

					}
				}

			}

			// Cron
			else {

				// To
				$to = '';
				foreach($phpmailer->getToAddresses() as $toArr) {
					$to .= $to ? ',' : '' ;
					$to .= $toArr[0];
				}

				// Subject
				$subject = 'CCRROONN_SSEENNDD::'.$phpmailer->Subject;

				// Attachments
				$attachments = null;
				$attachmentsArr = $phpmailer->getAttachments();
				if (count($attachmentsArr)) {
					$attachments = [];
					foreach ($attachmentsArr as $attachment) {
						$attachments[] = $attachment;
					}
				}

				// Другой SMTP
				$smtp = null;
				if (strstr($subject, ':::SMTP:')) {
					preg_match('~'
					           .preg_quote(':::SMTP:')
					           .'([0-9]+)'
					           .preg_quote(':::')
					           .'~', $subject,
						$arr);
					if ($arr[1]) {
						$subject = str_replace(':::SMTP:'.$arr[1].':::', '', $subject);
						global $wdproTempSmtp;
						$smtp = $wdproTempSmtp[$arr[1]];
					}
				}

				SqlTableCron::insert(array(
					'to'=>$to,
					'subject'=>$subject,
					'message'=>$phpmailer->Body,
					'headers'=>$phpmailer->getCustomHeaders(),
					'atachments'=>$attachments,
					'smtp'=>$smtp,
				));

				$phpmailer = new fakemailer();
			}
			
		});
		
		
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (общая часть)
	 */
	public static function run() {

		// Cron
		if (defined('DOING_CRON') && DOING_CRON) {

			add_action('plugins_loaded', function () {

				if ($sel = SqlTableCron::select(
					'WHERE sended=0 ORDER BY id'
				)) {
					foreach ($sel as $row) {

						print_r($row);

						if ($row['smtp']) {
							global $wdproTempSmtp, $wdproTempSmtpN;
							$wdproTempSmtpN ++;
							$wdproTempSmtp[$wdproTempSmtpN] = $row['smtp'];
							$row['subject'] .= ':::SMTP:'.$wdproTempSmtpN.':::';
						}

						\wp_mail( $row['to'],
							$row['subject'],
							$row['message'],
							'Content-type: text/html; charset=iso-8859-1' . "\r\n".
							$row['headers'],
							$row['atachments'] );

						SqlTableCron::update(
							array(
								'sended' => 1,
							),
							array(
								'id' => $row['id'],
							)
						);
					}
				}
			});
		}
	}


	/**
	 * Сохраняет письмо в файл для проверки отправки писем на локальной машине
	 *
	 * Для работы этого метода должно быть указано WP_DEBUG: true и
	 * WDPRO_MAIL_TEST_DIR: папка, куда сохранять файлы
	 *
	 * @param $subject
	 * @param $messageInHtmlFormat
	 * @internal param $phpmailer
	 */
	protected static function saveTestFile( $subject, $messageInHtmlFormat ) {
		
		if (defined('WDPRO_MAIL_TEST_DIR') && WDPRO_MAIL_TEST_DIR)
		{
			$text = '<p style="font-weight: bold">Тема: '.$subject.'</p>';

			$text .= $messageInHtmlFormat;
			
			static::$n ++;

			$filename = get_bloginfo('name', 'display');
			$filename .= ' - '.date('Y.m.d - H:i:s').'-'.static::$n.'.html';
			
			if (!is_dir(WDPRO_MAIL_TEST_DIR))
			{
				mkdir(WDPRO_MAIL_TEST_DIR, 0777, true);
			}
			
			file_put_contents(WDPRO_MAIL_TEST_DIR.'/'.$filename, $text);
		}
	}


	/**
	 * Отправляет письма точно так же как и wp_mail, только в формате html с CRON
	 *
	 * @param string|string[]      $to                  E-mail получателя
	 * @param string|null          $subject             Тема
	 * @param string|null          $messageInHtmlFormat Текст в формате html
	 * @param string|string[]|null $headers             Заголовки
	 * @param string|string[]|null $attachments Прикрепляемые файлы (полные пути до файлов)
	 * @param null|string          $smtp SMTP настройки
	 *
	 * @return bool
	 */
	public static function sendEmail( $to,
		$subject = null,
		$messageInHtmlFormat = null,
		$headers = null,
		$attachments = null,
		$smtp=null)
	{
		if (is_array( $headers )) {
			$headers[] = 'content-type: text/html';
		}
		else {
			if ($headers === null) $headers = '';
			$headers .= 'content-type: text/html' . "\r\n";
		}

		static::saveTestFile( $subject, $messageInHtmlFormat );

		if ($smtp) {
			global $wdproTempSmtp, $wdproTempSmtpN;
			$wdproTempSmtpN ++;
			$wdproTempSmtp[$wdproTempSmtpN] = $smtp;
			$subject .= ':::SMTP:'.$wdproTempSmtpN.':::';
		}

		return wp_mail( $to,
			$subject,
			$messageInHtmlFormat,
			$headers,
			$attachments );

		// Cron
		/*if (get_option('wdpro_send_email_by_cron') == 1) {
			
			SqlTableCron::insert(array(
				'to'=>$to,
				'subject'=>$subject,
				'message'=>$messageInHtmlFormat,
				'headers'=>$headers,
				'atachments'=>$attachments,
			));
		}
		
		// Сразу
		else {
			return wp_mail( $to,
				$subject,
				$messageInHtmlFormat,
				$headers,
				$attachments );
		}*/
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole() {

		// Настройки
		\Wdpro\Console\Menu::addSettings('Настройка отправки писем', function ($form) {
			
			/** @var \Wdpro\Form\Form $form */
			
			$form->add(array(
				'type'=>'html',
				'html'=>'<h3>SMTP</h3> 
				<p>Вы можете зарегистрировать почтовый ящик на 
				<a href="http://www.yandex.ru/" target="_blank">yandex.ru</a> 
				или 
				<a href="http://gmail.com/" target="_blank">gmail.com</a>
				и указать настройки этого ящика здесь. <BR>
				Тогда сайт будет подключаться 
				к этому почтовому ящику и отправлять письма с него. <BR>
				Таким образом 
				письма будут лучше доходить до адресатов.</p>
				<p>В зависимости от того, где вы зарегистрировали ящик, эти настройки
				могут быть разными. <BR>Чтобы их узнать, вы можете набрать в Яндексе
				 название вашего сервиса, где вы регистрировали язик + smtp, например:<BR>
				&quot;яндекс smtp&quot; или &quot;gmail smtp&quot;</p>
				<p>Перед тем, как подключать ящик, зайдите в него, 
				например, в Яндексе надо завершать регистрацию ящика перед его 
				испванием.</p>',
			));
			$form->add(array(
				'name'=>'wdpro_smtp_enable',
				'type'=>'check',
				'right'=>'Включить отправку писем через SMTP',
				'autoWidth'=>false,
			));
			$form->add(array(
				'name'=>'wdpro_smtp_email',
				'left'=>'E-mail *',
				'center'=>'Например, your.name@yandex.ru',
			));
			$form->add(array(
				'name'=>'wdpro_smtp_from_name',
				'left'=>'Имя',
				'center'=>'Например, Мавлютов Роман',
			));
			$form->add(array(
				'name'=>'wdpro_smtp_server',
				'left'=>array('text'=>'Адрес сервера *', 'nowrap'=>true),
				'center'=>'Например, smtp.yandex.ru',
			));
			$form->add(array(
				'name'=>'wdpro_smtp_port',
				'left'=>'Порт *',
				'center'=>'Например, 465',
			));
			$form->add(array(
				'name'=>'wdpro_smtp_login',
				'left'=>'Логин *',
				'center'=>'Обычно то, что написано до знака @',
			));
			$form->add(array(
				'name'=>'wdpro_smtp_pass',
				'left'=>'Пароль',
				'type'=>'pass',
			));
			$form->add(array(
				'name'=>'wdpro_smtp_protocol',
				'left'=>'Протокол',
				'type'=>'select',
				'options'=>array(
					''=>'',
					'ssl'=>'SSL',
					'tsl'=>'TSL',
				),
			));
			$form->add('submitSave');
			
			$form->add(array(
				'type'=>'html',
				'html'=>'
				<h3>Cron</h3>
				<p>Эта функция включает отправку писем в фоне.<BR>
				Когда она включена, то письма отправляются с задержкой в 1 минуту.<BR>
				Это ускоряет загрузку страниц, когда пользователь, например, 
				<BR>оформляет заказ и об этом заказе рассылаются письма.<BR>
				В этом случае отправка писем осуществляется немного попозже <BR>
				и пользователь сразу видит сообщение типа &quot;Ваш заказ оформлен&quot;.</p>
				<p>Для работы этой функции необходимо настроить cron-задания<BR>
				на сервере (хостинге) на файл wp-cron.php. <BR>Для этого добавьте
				следующую на сервере (хостинге) cron-задачу: <BR>
				<span style="color: red;">* * * * * wget -O /dev/null  --no-check-certificate -q 
				'
				        .site_url().'/wp-cron.php</span></p>'
			));
			$form->add(array(
				'autoWidth'=>false,
				'name'=>'wdpro_send_email_by_cron',
				'right'=>'Включить отправку писем через Cron',
				'type'=>'check',
			));
			$form->add('submitSave');
			
			return $form;
		});

		// Тестовое письмо
		\Wdpro\Console\Menu::addSettings([
			'menu_title'=>'Тестовое письмо',

			// Страница
			'open'=>function () {

				// Форма
				$form = new \Wdpro\Form\Form('test');
				$form->add([
					'name'=>'email',
					'center'=>'E-mail',
					'*'=>1,
				]);
				$form->add([
					'type'=>$form::SUBMIT,
					'value'=>'Отправить',
					'class'=>WDPRO_BUTTON_CSS_CLASS,
				]);

				// Отправка
				$message = '<p>Укажите свой ящик, на который отправить письмо</p>';
				$form->onSubmit(function ($data) use (&$message) {

					if ($data['email']) {

						static::$debug = true;
						echo '<pre style="white-space: pre-wrap">';
						\wp_mail($data['email'],
							'CCRROONN_SSEENNDD::'
							.$_SERVER['HTTP_HOST'].' - Тестовое письмо',
							'<p>Тестовое письмо</p>',
							['content-type: text/html'],
							[WDPRO_URL.'modules/sender/test-attachment.txt']
						);
						echo '</pre>';

						$message = '<p>Тестовое письмо отправлено</p>';
					}
				});

				return '<h1>Отправка тестового письма</h1>'
				.$message
				.$form->getHtml();
			}
		]);
	}


	
}

return __NAMESPACE__;