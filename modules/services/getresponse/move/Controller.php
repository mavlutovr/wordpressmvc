<?php
namespace Wdpro\Getresponse\Move;

class Controller extends \Wdpro\BaseController {
	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки:
	 * https://developer.wordpress.org/resource/dashicons/#forms
	 * http://fontawesome.io/icon/file-o/
	 */
	public static function initConsole()
	{
		\Wdpro\Console\Menu::add([
			'roll'=>ConsoleRoll::class,
			'n'=>100,
		]);
		\Wdpro\Console\Menu::add([
			'menu_title'=>'Getresponse тест',
			'open'=>function () {
				if ($campaigns = \Wdpro\Services\Getresponse\Controller::getCompaignsOptions()) {
					$form = new \Wdpro\Form\Form('test');
					$form->add([
						'name'=>'email',
						'top'=>'E-mail',
						'*'=>true,
					]);
					/*$form->add([
						'name'=>'from',
						'top'=>'Откуда переместить',
						'type'=>$form::SELECT,
						'options'=>$campaigns,
						'*',
					]);*/
					$form->add([
						'name'=>'to',
						'top'=>'Куда переместить',
						'type'=>$form::SELECT,
						'options'=>$campaigns,
						'*',
					]);
					$form->add($form::SUBMIT_SAVE);

					$form->onSubmit(function ($data) {
						\Wdpro\Services\Getresponse\Controller::moveEmailToCampaign(
							$data['email'], $data['to'], 0);
					});

					echo($form->getHtml());
				}

			}
		]);
	}

	
	/**
	 * Крон задание
	 */
	public static function cron()
	{
		if ($sel = SqlTable::select('WHERE enable=1 ORDER BY `sorting`')) {
			foreach($sel as $row) {

				// Получаем параметры перемещения
				$compaignFrom = $row['from'];
				$compaignTo = $row['to'];
				$daysPlus = $row['days'];

				// Если есть все необходимые параметры
				if ($compaignFrom && $compaignTo) {

					$api = \Wdpro\Services\Getresponse\Controller::api();

					// Получаем ID компании типа pF0of
					$conpaignFromID = $api->getCampaignByName($compaignFrom);

					// Определяем день последнего письма
					$lastDay = 0;


					$messages = (array)$api->getMessages($compaignFrom);
					foreach($messages as $message) {
						if ($conpaignFromID == $message->campaign) {
							if (isset($message->day_of_cycle))
								$lastDay = max($lastDay, $message->day_of_cycle);
						}
					}
					$lastDay += $daysPlus;

					// Определяем пользователей, которых необходимо переместить
					// Получаем всех пользователей компании
					$personsAll = \Wdpro\Services\Getresponse\Controller::getContacts($conpaignFromID);

					// Список пользователей, которых нужно переместить
					$persons = [];
					// Перечисляем пользователей
					foreach($personsAll as $key=>$person) {

						//print_r($person);

						//echo($conpaignFromID.' == '.$person->campaign."\n\n");
						//echo ($person->cycle_day.' >= '.$lastDay."\n\n");


						// Если это та компания и
						// Если подошло время переносить
						if ($conpaignFromID == $person->campaign
							&& $person->cycle_day >= $lastDay) {

							// Добавляем пользователя в список пользователей, которых нужно
							// переместить
							$persons[$person->email] = $person->email;
							//unset($existsEmails[$person->email]);
						}
					}

					// Перемещаем пользователей в другую компанию
					$n = 0;
					foreach($persons as $personEmail) {
						$n ++;

						/*echo $personEmail . ': ' . $compaignTo . PHP_EOL;

						if ($personEmail == 'mavlutovr@ya.ru') {
							\Wdpro\Services\Getresponse\Controller::moveEmailToCampaign(
								$personEmail, $compaignTo, 0);
						}*/
						\Wdpro\Services\Getresponse\Controller::moveEmailToCampaign(
							$personEmail, $compaignTo, 0);
					}
					/*sort($persons);
					print_r($persons);*/
				}
			}
		}
	}
}


return __NAMESPACE__;