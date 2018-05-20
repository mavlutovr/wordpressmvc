<?php
namespace Wdpro\Pay\Methods;

/**
 * Метод оплаты robokassa.ru
 * 
 * Настройка сайта в робокассе
 * Result Url (По этому адресу робокасса отправляет запрос, чтобы уведомить о платеже)
 * /secure_wp/wp-admin/admin-ajax.php?action=wdpro&wdproAction=robokassaResult
 * 
 * Метод отсылки данных по Result Url: POST
 * 
 * Success Url (Страница, которая открывается после успешного платежа)
 * /payed
 * 
 * Fail Url (Страница, которая открывается после неудачного платежа)
 * /pay-error
 * 
 * @package Wdpro\Pay\Methods
 */
class Robokassa extends Base  implements MethodInterface {


	/**
	 * Инициализация метода
	 */
	public static function init() {
		
		// Метка pay_robokassa
		add_shortcode('pay_robokassa', function () {

			// Если форма подтверждения отправлена
			if (isset($_POST['confirm']))
			{
				// Данные формы
				$post = $_POST['confirm'];

				// Создаем объект транзакции
				/** @var \Wdpro\Pay\Entity $pay */
				$pay = wdpro_object(\Wdpro\Pay\Entity::class, $post['id']);

				// Если пользователь подтвердил оплату
				if ($post['confirm'] == 1)
				{
					// Подтверждаем транзакцию
					$pay->confirm('demo');

					// Переход на страницу, где была нажата кнопка "Оплатить"
					$pay->goToReferer();
				}

				// Пользователь не подтвердил оплату
				else
				{
					// Отменяем транзакцию
					$pay->cancel('demo');

					// Переход на страницу, где была нажата кнопка "Оплатить"
					$pay->goToReferer();
				}
			}

			// Форма подтверждения еще не отправлена
			else
			{
				// Выводим форму подтверждения
				return wdpro_render_php(
					__DIR__.'/../templates/demo_confirm.php',
					array(
						'referer'=>$_SERVER['HTTP_REFERER'],
						'post'=>$_POST,
					)
				);
			}
		});
		
		// Result URL
		wdpro_ajax('robokassa.result', function ($data) {

			// Получаем объект оплаты
			if ($pay = \Wdpro\Pay\Controller::getPay($_POST['InvId'])) {
				
				// Получаем данные поля
				$payData = $pay->getData();

				// Тестовый режим
				$testMode = get_option('pay_robokassa_test_mode');
				
				// Проверяем подпиись
				// Создаем проверочную подпись
				$signature = md5(
					$_POST['OutSum'].':'.$pay->id().':'
					. ($testMode ? get_option('pay_robokassa_pass2_demo')
						: get_option('pay_robokassa_pass2'))
				);
				
				// Если сумма и подпись верны
				if ($payData['cost'] == $_POST['OutSum'] 
					&& strtolower($signature) == strtolower($_POST['SignatureValue'])) {
					
					// Сохраняем данные $_POST и $_GET
					$pay->mergeInfo([
						'robokassa_result_data'=>[
							time().'_'.rand(1000, 10000) => [
								'get'=>$_GET,
								'post'=>$_POST,
							]
						]
					]);
					
					// Запускаем оплату
					$pay->confirm('robokassa', 1);
					
					echo('OK'.$_POST['InvId']);
					exit();
				}
			}
		});
	}


	/**
	 * Запускается в админке
	 *
	 * В этом методе можно добавиьт например какие-нибудь кнопки в меню админки
	 */
	public static function runConsole() {

		// Настройки
		\Wdpro\Console\Menu::addSettings('Robokassa', function ($form) {
			
			/** @var \Wdpro\Form\Form $form */

			$form->add([
				'name'      => 'pay_method_' . static::getName() . '_enabled',
				'right'     => 'Включить метод оплаты',
				'type'      => 'check',
				'autoWidth' => false,
			]);

			$form->add($form::SUBMIT_SAVE);

			$form->add(array(
				'type'=>'check',
				'right'=>'Тестовый режим',
				'name'=>'pay_robokassa_test_mode',
			));
			$form->add(array(
				'name'=>'pay_robokassa_sMerchantLogin',
				'top'=>'Идентификатор магазина на Robokassa',
			));
			$form->add(array(
				'name'=>'pay_robokassa_pass1',
				'top'=>'Пароль №1 для оплаты',
				'type'=>'pass',
			));
			$form->add(array(
				'name'=>'pay_robokassa_pass2',
				'top'=>'Пароль №2 для оплаты',
				'type'=>'pass',
			));

			// Параметры проведения тестовых платежей
			$form->add([
				'type'=>$form::HTML,
				'html'=>'<h2>Параметры проведения тестовых платежей</h2>',
			]);
			$form->add([
				'type'=>'pass',
				'name'=>'pay_robokassa_pass1_demo',
				'top'=>'Пароль №1 для оплаты (тестовый)',
			]);
			$form->add([
				'type'=>'pass',
				'name'=>'pay_robokassa_pass2_demo',
				'top'=>'Пароль №2 для оплаты (тестовый)',
			]);

			// Адреса
			$resultUrl = wdpro_ajax_url([
				'action'=>'robokassa.result',
			]);
			
			$form->add(array(
				'type'=>'html',
				'html'=>'
				<h2>Настройки Робокассы</h2>
				<p><b>Result Url:</b> '.$resultUrl.'</p>
				<p><b>Метод отсылки данных по Result Url:</b> POST</p>
				<p><b>Success Url:</b> '.home_url().'/aftersale</p>
				<p><b>Fail Url:</b> '.home_url().'/pay-error</p>',
			));
			
			$form->add('submitSave');
			
			return $form;
		});
	}


	/**
	 * Возвращает имя метода оплаты
	 *
	 * @return string
	 */
	public static function getName() {

		return 'robokassa';
	}


	/**
	 * Возвращает форму для начала оплаты
	 *
	 * @param \Wdpro\Pay\Entity $pay Транзакция
	 * @return string
	 */
	public static function getBlock( $pay ) {

		$data = $pay->getData();
		
		$data['testMode'] = get_option('pay_robokassa_test_mode');
		
		if ($data['testMode']) {
			$data['action'] = 'https://auth.robokassa.ru/Merchant/Index.aspx?IsTest=1';
			//$data['action'] = 'http://test.robokassa.ru/Index.aspx';
		}
		else {
			$data['action'] = 'https://auth.robokassa.ru/Merchant/Index.aspx';
		}
		
		$data['MerchantLogin'] = get_option('pay_robokassa_sMerchantLogin');
		$data['OutSum'] = $pay->getCost();
		$data['InvId'] = $pay->id();
		$data['Desc'] = $pay->getText();
		$data['SignatureValue'] = (
			$data['MerchantLogin'].':'
			.$data['OutSum'].':'
			.$data['InvId'].':'
			. ($data['testMode'] ? get_option('pay_robokassa_pass1_demo')
				: get_option('pay_robokassa_pass1'))
		);
		$data['SignatureValueMd5'] = md5($data['SignatureValue']);
		$data['IncCurrLabel'] = '';
		$data['Culture'] = 'ru';
		
		return wdpro_render_php(
			__DIR__.'/../templates/robokassa_block.php',
			$data
		);
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {
	}


	/**
	 * Возвращает название метода русскими буквами для использования во всяких текстах
	 *
	 * @return mixed
	 */
	public static function getLabel() {

		return 'Robokassa';
	}

}