<?php
namespace Wdpro\Person;

class Controller extends \Wdpro\BaseController {

	/**
	 * Класс пользователя
	 * 
	 * @var Entity
	 */
	protected static $personClass = Entity::class;
	

	/**
	 * Инициализация модуля
	 */
	public static function init() {

		// Подключаем шаблоны писем
		\Wdpro\Modules::addWdpro('sender/templates/email');
		
		// Сохранение параметров пользователя
		wdpro_ajax('personSaveParams', function () {
			
			$person = static::current();
			$person->mergeParams($_POST['params'])->save();
		});
	}


	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite() {

		// Страница входа
		wdpro_on_uri('login', function () {

			$form = new \Wdpro\Form\Form('auth');
			$form->add(array(
				'name'=>'email',
				'center'=>'E-mail',
				'*'=>true,
				'containerClass'=>'_email _w_100'
			));
			$form->add(array(
				'type'=>'pass',
				'name'=>'pass',
				'center'=>'Пароль',
				'*'=>true,
				'containerClass'=>'_pass _w_100'
			));
			$form->add(array(
				'type'=>'submit',
				'value'=>'Войти',
				'containerClass'=>'_submit _w_100'
			));
			$form->add(
				array(
					'name'=>'remember',
					'right'=>'Запомнить',
					'type'=>'check',
					'containerClass'=>'_remember'
				),
				array(
					'autoLeft'=>false,
					'type'=>'html',
					'html'=>'<div class="g-float-right _forgot">
						<a href="'.home_url().'/lostpassword">Забыли пароль?</a>
					</div>',
				)
			);


			// Запуск формы
			$form->onSubmit(function ($formData) use (&$template) {

				$user = wp_signon(array(
					'user_login'=>$formData['email'],
					'user_password'=>$formData['pass'],
					'remember'=>$formData['remember'],
				));

				// Авторизация удалась
				if (!is_wp_error($user)) {

					wdpro_location(home_url());
				}

				// Авторизация не удалась
				else {
					$template['message'] = $user->get_error_message();

					$template['message'] = preg_replace(
						'~<a href="(.+)">~',
						'<a href="lostpassword">',
						$template['message']
					);
				}
			});
			
			wdpro_content(function () use (&$form) {

				$template = array();

				$template['form'] = $form->getHtml();

				return wdpro_render_php(
					WDPRO_TEMPLATE_PATH.'login_form.php',
					$template
				);
			});
		});
		
		
		// Страница восстановления пароля
		wdpro_on_uri_content('lostpassword', function () {

			$template = array();
			
			
			// Завершение восстановление
			if (isset($_GET['user'])) {
				
				// Проверяем ключ
				if ($_GET['key'] == get_user_meta($_GET['user'], 'password_reset_key', 
						true)) {
					
					/*// Создаем новый пароль
					$password = wdpro_generate_password();
					
					// Выводим новый пароль
					$template['message'] = 'Ваш новый пароль: '.$password;*/
					
					$form = new \Wdpro\Form\Form('pass');
					$form->add(array(
						'name'=>'pass',
						'center'=>'Новый пароль',
						'type'=>'pass',
						'*'=>true,
						'containerClass' => '_pass _w_100'
					));
					$form->add(array(
						'name'=>'pass2',
						'center'=>'Повторите пароль',
						'type'=>'pass',
						'*'=>true,
						'containerClass' => '_pass _w_100'
					));
					$form->add(array(
						'type'=>'submit',
						'value'=>'Сохранить пароль',
						'containerClass' => '_submit _w_100'
					));
					
					
					// Отображать форму
					$showForm = true;
					
					// Запуск формы
					$form->onSubmit(function ($data) 
					use (&$form, &$template, &$showForm) {
						
						if (!$data['pass']) {
							
							$template['message'] = 'Вы не указали пароль';
							return false;
						}
						
						if ($data['pass'] != $data['pass2']) {
							
							$template['message'] = 'Вы указали разные пароли, укажите 
							одинаковые';
							return false;
						}

						
						// Получаем данные пользователя
						$person = wp_get_current_user();

						// Отправляем письмо
						\Wdpro\Sender\Templates\Email\Controller::send(
							'send_password_change_email',
							$person->user_email,
							array(
								'first_name'=>$person->user_firstname,
								'last_name'=>$person->user_lastname,
								'email'=>$person->user_email,
								'pass'=>$data['pass'],
							),
							[
								'default'=>
									require(__DIR__.'/default/passwordChangeEmail.php'),
							]
						);

						// Отключаем стандартное письмо о смене пароля
						add_filter( 'send_password_change_email', '__return_false');

						
						// Меняем пароль
						wp_set_password($data['pass'], $_GET['user']);
						$template['message'] = 'Ваш пароль изменен, теперь попробуйте 
						<a href="login">войти</a>';
						$showForm = false;
					});
					
					if ($showForm) {
						$template['form'] = $form->getHtml();
					}
				}
				
				// Ключ не верный
				else {
					
					$template['message'] = 'Не получилось восстановить пароль.';
				}
			}
			
			
			// Отправка запроса на восстановление
			else {


				// Форма
				$form = new \Wdpro\Form\Form( 'lostpassword' );
				$form->add( array(
					'name'           => 'email',
					'center'         => 'E-mail',
					'*'              => true,
					'containerClass' => '_email _w_100'
				) );
				$form->add( array(
					'type'           => 'submit',
					'value'          => 'Получить новый пароль',
					'containerClass' => '_submit _w_100'
				) );
				$form->add( array(
					'type' => 'html',
					'html' => '<div class="g-float-left _forgot"><a href="login">&larr; Назад</a></div><BR>'
				) );


				// Запуск восстановления
				$form->onSubmit( function ( $data ) use ( &$form, &$template ) {

					// Получаем e-mail
					$email = trim( $data['email'] );

					// Находим такого пользователя
					if ($personData = SqlTable::getRow(
						['WHERE `user_email`=%s LIMIT 1', [$email]]
					)
					) {
						// Данные для шаблона письма
						$emailData = [
							'first_name' => get_user_meta( $personData['ID'],
								'first_name',
								true ),
							'last_name'  => get_user_meta( $personData['ID'],
								'last_name',
								true ),
							'user_login' => $personData['user_login'],
						];

						// Создаем ссылку для восстановления пароля
						// Получаем код
						$key = md5( rand( 10000, 100000 ) + time() );

						// Запоминаем код
						update_user_meta(
							$personData['ID'],
							'password_reset_key',
							$key
						);

						// Создаем саму ссылку
						$emailData['link'] = home_url() . '/lostpassword?user='
							. $personData['ID'] . '&key=' . $key;

						// Отправляем пиисьмо
						\Wdpro\Sender\Templates\Email\Controller::send(
							'lostpassword',
							$personData['user_email'],
							$emailData,
							[
								'default' => require __DIR__ . '/default/lostpasswordEmail.php',
							]
						);

						$template['message'] = 'На Ваш E-mail были отправлены дальнейшие 
					инструкции';
					}

					// Нет такого пользователя
					else {
						$template['message'] = 'Не удалось найти пользователя с таким E-mail';
					}
				} );


				$template['form'] = $form->getHtml();
			}

			return wdpro_render_php(
				WDPRO_TEMPLATE_PATH.'login_form.php',
				$template
			);
		});
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {

		// Профиль
		wdpro_on_uri_content('person-profile', function ($content) {
			
			$form = new \Wdpro\Form\Form('profile');
			$form->add(array(
				'name'=>'first_name',
				'top'=>'Имя',
				'*'=>true,
			));
			$form->add(array(
				'name'=>'last_name',
				'top'=>'Фамилия',
				'*'=>true,
			));
			$form->add(array(
				'type'=>'submit',
				'value'=>'Сохранить',
			));
			
			// Сохранение
			$form->onSubmit(function ($data) {
				
				foreach($data as $key=>$value) {
					
					update_user_meta(get_current_user_id(), $key, $value);
				}
			});
			
			$form->setData(array(
				'first_name'=>get_user_meta(get_current_user_id(), 'first_name', true),
				'last_name'=>get_user_meta(get_current_user_id(), 'last_name', true),
			));
			
			$content .= $form->getHtml();
			
			return $content;
		});
		
		// Смена пароля
		wdpro_on_uri('person-profile-password', function ($content) {

			$form = new \Wdpro\Form\Form('profile');
			$form->add(array(
				'type'=>'pass',
				'name'=>'current_pass',
				'top'=>'Текущий пароль',
				'*'=>true,
			));
			$form->add(array(
				'type'=>'pass',
				'name'=>'new_pass',
				'top'=>'Новый пароль',
				'*'=>true,
			));
			$form->add(array(
				'type'=>'pass',
				'name'=>'confirm_pass',
				'top'=>'Повторите пароль',
				'*'=>true,
			));
			$form->add(array(
				'type'=>'submit',
				'value'=>'Сохранить',
			));

			// Сохранение
			$form->onSubmit(function ($data) use (&$content, &$form) {

				// Проверка текущего пароля
				$user = get_user_by('ID', get_current_user_id());
				if (!($user && wp_check_password($data['current_pass'], 
						$user->data->user_pass, $user->ID))) {
					$form->showErrorMessage('Вы указали не верный текущий пароль');
					return false;
				}

				// Не верный новый пароль
				if (!$data['new_pass']) {

					$form->showErrorMessage('Вы указали не верный новый пароль');
					return false;
				}

				// Проверка что пароли совпадают
				if ($data['new_pass'] != $data['confirm_pass']) {
					
					$form->showErrorMessage('Вы указали разные пароли. 
					Пожалуйста, повторите попытку.');
					return false;
				}
				
				// Изменение пароля
				$form->showMessage('Пароль успешно изменен.');
				wp_set_password($data['new_pass'], get_current_user_id());

				$user = wp_signon(array(
					'user_login'=>$user->user_login,
					'user_password'=>$data['new_pass'],
					//'remember'=>$formData['remember'],
				));
				
				// Отправка письма на почту
				\Wdpro\Sender\Templates\Email\Controller::send(
					'send_password_change_email',
					$user->user_email,
					[
						'pass'=>$data['new_pass'],
						'first_name'=>$user->first_name,
						'last_name'=>$user->last_name,
						'email'=>$user->user_email,
					],
					[
						'default'=>require(__DIR__.'/default/passwordChangeEmail.php'),
					]
				);
			});
			
			wdpro_content(function ($content) use (&$form) {

				$content .= $form->getHtml();
				
				return $content;
			});

			return $content;
		});
		
		// Выход
		wdpro_get_post(function ($post) {
			
			// Если это страница выхода
			if ($post && $post->post_name == 'logout') {
				
				// Выходим
				wp_logout();
				
				// Переходим на главную
				wdpro_location(home_url());
			}
		});
	}


	/**
	 * Убирает из меню консоли стандартную кнопку пользователей
	 * 
	 * http://screenshot3.seobit.ru/roma.2015.12.18___11:18:1450426721.png
	 */
	public static function removeStandartButtonFromConsole() {

		add_action('admin_menu', function () {

			remove_menu_page( 'users.php' );
		});
	}


	/**
	 * Возвращает текущего авторизованного пользователя
	 * 
	 * @return Entity
	 */
	public static function current() {
		
		$id = get_current_user_id();
		
		if ($id) {
			
			return static::getById($id);
		}
	}


	/**
	 * Возвращает пользователя по ID
	 * 
	 * @param int|array $dataOrId ID или данные пользователя
	 * @return Entity
	 * @throws \Exception
	 */
	public static function getById($dataOrId) {
		
		return wdpro_object(static::$personClass, $dataOrId);
	}


	/**
	 * Проверяет наличие пользователя с таким E-mail
	 *
	 * @param string $email E-mail
	 * @return Entity|void
	 */
	public static function getByEmail($email) {

		if ($data = SqlTable::getRow(['WHERE `user_login`=%s', [$email]])) {

			return wdpro_object(static::$personClass, $data);
		}
	}


	/**
	 * Создавалка нового пользователя
	 *
	 * @param array $data Данные пользователя
	 * @return array
	 */
	public static function createNewPerson($data) {
		
		/*$data = wdpro_extend([
			'user_login'=>$data['user_email'],
		], $data);
		
		file_put_contents(
			__DIR__.'/../../../app/person/personAddLogWdpro',
			"createNewPerson\n\n".print_r($data, 1)
		);
		
		// Добавляем пользователя
		$data['user_login'] = $data['user_email'];
		$id = wp_insert_user($data);*/

		$person = static::getById($data);

		/*$person = new Entity();
		$person->setData($data);*/
		//$person->save();

		if ($person->save()) {

			// Возвращаем данные пользователя
			return array(
				'user_email'=>$person->data['user_login'],
				'user_email'=>$person->data['user_login'],
				'user_pass'=>$person->getCreatedPass(),
				'id'=>$person->id(),
				'ID'=>$person->id(),
			);
		}
	}


	/**
	 * Установка класса сущности пользователя
	 * 
	 * @param string $class Имя класса
	 */
	public static function setPersonEntityClass($class) {
		
		static::$personClass = $class;
	}
}

return __NAMESPACE__;