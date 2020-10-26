<?php
namespace Wdpro\Pay\Methods;

class Demo extends Base  implements MethodInterface {


	/**
	 * Инициализация метода
	 */
	public static function init() {

		// Страница по-умолчанию
		\Wdpro\Page\Controller::defaultPage('pay_demo', function () {
			return require __DIR__.'/../default/demo.page.php';
		});
		
		wdpro_on_uri_content('pay_demo', function ($content) {
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
					$pay->gotoAftersaleUrl();
				}

				// Пользователь не подтвердил оплату
				else
				{
					// Отменяем транзакцию
					$pay->cancel('demo');

					// Переход на страницу, где была нажата кнопка "Оплатить"
					$pay->goToErrorUrl();
				}
			}

			// Форма подтверждения еще не отправлена
			else
			{
				\wdpro_default_file(
					__DIR__.'/../templates/demo_confirm.php',
					WDPRO_TEMPLATE_PATH.'pay_method_demo_confirm.php'
				);

				// Выводим форму подтверждения
				return $content.wdpro_render_php(
					WDPRO_TEMPLATE_PATH.'pay_method_demo_confirm.php',
					array(
						'referer'=>$_SERVER['HTTP_REFERER'],
						'post'=>$_POST,
					)
				);
			}
		});
		
		add_shortcode('pay_demo', function () {

			
		});
	}
	

	/**
	 * Возвращает имя метода оплаты
	 *
	 * @return string
	 */
	public static function getName() {

		return 'demo';
	}


	/**
	 * Возвращает форму для начала оплаты
	 *
	 * @param \Wdpro\Pay\Entity $pay Транзакция
	 * @return string
	 */
	public static function getBlock( $pay ) {

		\wdpro_default_file(
			__DIR__.'/../templates/demo_block.php',
			WDPRO_TEMPLATE_PATH.'pay_method_demo_block.php'
		);

		return wdpro_render_php(
			WDPRO_TEMPLATE_PATH.'pay_method_demo_block.php',
			$pay->getData()
		);
	}


	/**
	 * Запускается в админке
	 *
	 * В этом методе можно добавиьт например какие-нибудь кнопки в меню админки
	 */
	public static function runConsole() {
		\Wdpro\Console\Menu::addSettings('Demo метод оплаты', function ($form) {

			/** @var \Wdpro\Form\Form $form */

			$form->add([
				'name'      => 'pay_method_' . static::getName() . '_enabled',
				'right'     => 'Включить метод оплаты',
				'type'      => 'check',
				'autoWidth' => false,
			]);
			
			$form->add($form::SUBMIT_SAVE);
			
			return $form;
		});
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {
		if (get_option('pay_method_demo_enabled') == 1) {
			wdpro_add_script_to_site(__DIR__.'/../templates/demo.js');
		}
	}

	/**
	 * Возвращает название метода русскими буквами для использования во всяких текстах
	 *
	 * @return mixed
	 */
	public static function getLabel() {

		return 'Демо';
	}

}