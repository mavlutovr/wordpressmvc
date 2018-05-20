<?php
namespace Wdpro\Services\Getresponse;

/**
 * Api гетреспонза
 * 
 * Сама библиотека взята отсюда
 * https://github.com/robertstaddon/GetResponse-PHP-Wrapper/blob/670ed03d0fa5efafaff7a21541db3499a8736f60/PHPwrapper/GetResponseAPI.class.php
 * 
 * @package Wdpro\Services\Getresponse
 */
class Controller extends \Wdpro\BaseController {
	
	protected static $_api = null;
	protected static $_compaignOptions;


	/**
	 * Инициализация модуля
	 */
	public static function init() {

		wdpro_ajax('getresponse-subscribe', function () {
			
			$data = $_GET['data'];
			
			/*$fp =  fopen(__DIR__.'/log', 'a+');
			fwrite($fp,
				wdpro_current_url()."\n\n".print_r($data, 1)
				."\n\n--------------------------------\n\n"
			);
			fclose($fp);*/
			
			
			if ($data['email']) {

				$data = wdpro_extend([
					'name' => $data['email'],
				],
					$data);

				$api = static::api();

				// E-mail зарегистрирован
				if (static::existsEmail($data['email'])) {
					$status = 'Такой e-mail уже зарегистрирован.';

					$return = [
						'error' => wdpro_get_option('wdpro_getresponse_subscribe_email_exists',
							'Такой e-mail уже зарегистрирован.'),
					];
				}

				// E-mai Не зарегистрирован
				else {

					// Получаем ID компании
					$campaigns = (array)$api->getCampaigns();
					$campaignTo = get_option('getresponse_compaign_subscribe');
					$campaignToId = null;
					foreach ($campaigns as $campaignId => $campaign) {
						if ($campaign->name == $campaignTo) {
							$campaignToId = $campaignId;
							break;
						}
					}

					if ($campaignToId) {
						if ($api->addContact(
							$campaignToId,
							' ',
							$data['email']
						)) {
							$status = 'Добавлен в GetResponse';
							$return = [
								'url' => get_option('wdpro_getresponse_subscribe_thank_you'),
							];
						}

						else {
							$error = $api->getError();
							if ($error->code == -1 && $error->message == 'Contact already queued for target campaign') {
								$status = 'Контакт находится в неподтвержденных ';
								$return = [
									'error'=>'Указанный вами e-mail уже занесен в нашу 
									базу, но пока не подтвержден. Пожалуйста, подтвердите подписку с помощью ссылки, которая была отправлена на ваш e-mail.',
								];
							}
						}
					}

					else {
						$status = 'Не указана компания в настройках';
						$return = [
							'error' => 'В настройках сайта не указана компания, в 
							которую добавлять подписчиков',
						];
					}
				}

				// Добавляем E-mail базу
				SqlTable::insert([
					'email'=>$data['email'],
					'date'=>time(),
					'status'=>$status,
				]);

				return $return;
			}
			
		});

		// TEST
		wdpro_ajax('getresponse-test', function () {
			ini_set('display_errors', 'on');
			error_reporting(E_ALL);

			echo(123);

			\Wdpro\Services\Getresponse\Controller::moveEmailToCampaign(
				'roboteck@bk.ru',
				'seobit_after_sale',
				null,
				true
			);
		});

		//  Ajax
		// Проверка дубля
		wdpro_ajax('getresponse-is-exists-email', function ($data) {

			return [
				'exists' => static::existsEmail($data['email'])
			];
		});
	}

	
	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole() {

		// Настройки
		\Wdpro\Console\Menu::addSettings('GetResponse', function ($form) {

			/** @var $form \Wdpro\Form\Form */
			$form->add([
				'name'=>'wdpro_getresponse_api_key',
				'top'=>'API ключ с <a href="https://app.getresponse.com/manage_api.html" target="_blank">этой страницы</a>',
				'bottom'=>'<div style="color: red;">Возможно этот 
				ключ надо поменять и в других сайтах<div style="margin-left: 20px;">
				<div>1. Настройки - GetResponse - API ключ</div>
				<div>2. Thrive Dashboard - API Connections - Редактировать/Создать</div>
</div></div>'
			]);

			
			
			// Когда ключ установлен
			if (get_option('wdpro_getresponse_api_key')) {
				/** @var \Wdpro\Form\Form $form */
				$api = \Wdpro\Services\Getresponse\Controller::api();
				$campaigns = (array)$api->getCampaigns();
				$options = [
					''=>'',
				];
				if (is_array($campaigns)) {
					foreach ($campaigns as $campaign) {
						$options[$campaign->name] = $campaign->name;
					}
				}
				ksort($options);

				if ($campaigns) {

					$form->add([
						'name'=>'getresponse_compaign_subscribe',
						'top'=>'Компания, в которую добавлять подписчиков',
						'bottom'=>'Список компаний можно посмотреть 
				<a href="https://app.getresponse.com/campaign_list.html" 
				target="_blank">здесь</a>',
						'type'=>$form::SELECT,
						'options'=>$options,
					]);
					$form->add([
						'name'=>'wdpro_getresponse_subscribe_thank_you',
						'top'=>'Страница, которую открывать после подписки',
					]);
					$form->add([
						'name'=>'wdpro_getresponse_subscribe_email_exists',
						'top'=>'Сообщение, когда e-mail уже есть в базе',
					]);

					$form->add([
						'name'=>'getresponse_my_compaigns',
						'top'=>'Мои компании',
						'right'=>'Это те компании, в которые не подписывать повторно',
						'type'=>$form::SELECT,
						'options'=>$options,
						'multiple'=>true,
						'style'=>'height: 200px;'
					]);

					static::triggerStatic('settingsForm', $form);
				}
				else {
					$form->add([
						'type'=>$form::HTML,
						'html'=>'<p class="error">Не верный ключ</p>
							<p>При сохранении нового ключа, его надо сохранить 2 раза</p>',
					]);
				}
			}
			

			$form->add($form::SUBMIT_SAVE);
			return $form;
		});

		// Лог
		// Только когда что-то есть
		if (SqlTable::count('')) {
			\Wdpro\Console\Menu::add([
				'roll'=>ConsoleRoll::class,
				'n'=>100,
			]);
		}
	}


	/**
	 * Перемещение E-mail в другую компанию
	 *
	 * @param string   $email      E-mail, который надо переместить
	 * @param string   $campaignTo Имя компании, в котроую переместить пользователя
	 * @param null|int $day        День в компании
	 * @param bool     $autoAdd Добавлять без подтверждения, если нету
	 */
	public static function moveEmailToCampaign($email, $campaignTo, $day=null,
		$autoAdd=false, $campaignFrom=null) {

		$email = trim($email);
		$campaignTo = trim($campaignTo);

		$api = \Wdpro\Services\Getresponse\Controller::api();
		$campaigns = (array)$api->getCampaigns();

		// Получаем ID компании From
		// $campaignTo = get_option('getresponse_compaign_after_register');
		/*$campaignFromId = null;
		foreach($campaigns as $campaignId=>$campaign) {
			if ($campaign->name == $campaignFrom) {
				$campaignFromId = $campaignId;
				break;
			}
		}*/

		// Получаем ID компании To
		// $campaignTo = get_option('getresponse_compaign_after_register');
		$campaignToId = null;
		foreach($campaigns as $campaignId=>$campaign) {
			if ($campaign->name == $campaignTo) {
				$campaignToId = $campaignId;
				break;
			}
		}

		// Получаем ID пользователя
		$contactId = null;
		if ($contact = static::getContactFromMyCompaigns
		($email, $campaignFrom)) {
			$contactId = $contact->id;
		}

		// Если есть такая компания
		if ($campaignToId) {

			//echo("campaignToId: $campaignToId\n\n");
			//echo("contactId: $contactId\n\n");

			// Если есть такой пользователь
			if ($contactId) {
				$api->setContactCampaign($contactId, $campaignToId);

				if ($day !== null) {
					$api->setContactCycle($contactId, $day);
				}
			}

			// Нет пользователя
			else {
				if (get_option('getresponse_compaign_add_if_not_exists') == 1 || $autoAdd) {

					$api->addContact($campaignToId, '-', $email);
				}
			}
		}
	}


	/**
	 * Возвращает контакт из своих компаний, которые указаны в настрйках
	 *
	 * @param string $email E-mail
	 * @param null|atring   $campaign Дополнительная компания
	 *
	 * @return mixed
	 */
	public static function getContactFromMyCompaigns($email, $campaign=null) {

		// Api
		$api = static::api();

		$allCampaigns = (array)$api->getCampaigns();

		$compaignIds = [];

		// Получаем список компаний
		$myCompaigns = get_option('getresponse_my_compaigns');
		if (is_array($myCompaigns) && is_array($allCampaigns)) {
			foreach($allCampaigns as $compaignId => $compaign) {
				if (in_array($compaign->name, $myCompaigns)) {
					$compaignIds[$compaignId] = $compaignId;
				}
			}
		}
		foreach($allCampaigns as $compaignId => $compaign) {
			if ($compaign->name == $campaign) {
				$compaignIds[$compaignId] = $compaignId;
			}
		}

		/*echo '$compaignIds: ';
		print_r($compaignIds);*/

		// Получаем контакт
		$contacts = (array)$api->getContactsByEmail($email);
		if (count($contacts)) {

			// Если есть мои компании, когда проверяем по ним
			if (count($compaignIds)) {
				foreach($contacts as $contactId=>$contact) {
					if ($compaignIds[$contact->campaign]) {

						$contact->id = $contactId;

						return $contact;
					}
				}
			}

			// Нет моих компаний, тогда просто возвращаем первый контакт
			else {
				foreach($contacts as $contactId=>$contact) {
					$contact->id = $contactId;
					return $contact;
				}
			}
		}
	}


	/**
	 * Проверяет, есть ли контакт в компаниях
	 *
	 * @param $email string E-mail
	 * @return bool
	 */
	public static function existsEmail($email) {

		if ($contact = static::getContactFromMyCompaigns($email)) {
			return true;
		}

		return false;
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {

		/*$details = static::api()->getAccountInfo();
		var_dump($details);
		exit();*/
		
		/*$campaigns = (array)static::api()->getCampaigns();
		print_r($campaigns);

		exit();*/
	}


	/**
	 * Возвращает объект API
	 * 
	 * @return \GetResponse
	 */
	public static function api() {

		if (!static::$_api) {
			require_once(__DIR__.'/lib/GetResponseAPI.class.php');
			static::$_api = new \GetResponse(get_option('wdpro_getresponse_api_key'));
		}
		
		return static::$_api;
	}


	/**
	 * Возвращает массив компаний $companyName=>$companyName
	 * 
	 * @return array
	 */
	public static function getCompaignsOptions() {

		if (!isset($_compaignOptions)) {
			$api = static::api();
			$campaigns = (array)$api->getCampaigns();
			if ($campaigns && count($campaigns)) {

				$options = [
					'' => '',
				];
				foreach ($campaigns as $campaign) {
					$options[$campaign->name] = $campaign->name;
				}
				ksort($options, true);
				static::$_compaignOptions = $options;
			}
			else {
				static::$_compaignOptions = null;
			}
		}
		
		if (static::$_compaignOptions) {
			return static::$_compaignOptions;
		}
	}


	/**
	 * Возвращаеь
	 *
	 * @param array|null $compaigns Компании
	 * @return array
	 */
	public static function getContacts($compaigns=null) {

		if (is_string($compaigns)) {
			$compaigns = [$compaigns];
		}

		return (array)static::api()->getContacts($compaigns,
			'CONTAINS', '%', array('name' => false, 'email' => false));
	}


}


return __NAMESPACE__;