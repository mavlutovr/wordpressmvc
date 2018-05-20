<?php
namespace Wdpro\Person;

class ConsoleForm extends \Wdpro\Form\Form {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

		$this->add(array(
			'name'=>'user_login',
			'top'=>'Имя пользователя',
			'*'=>true,
		));
		$this->add(array(
			'name'=>'email',
			'top'=>'E-mail',
			'*'=>true,
		));
		$this->add(array(
			'name'=>'first_name',
			'top'=>'Имя',
		));
		$this->add(array(
			'name'=>'last_name',
			'top'=>'Фамилия',
		));

		if (isset($_GET['id'])) {
			$this->add(array(
				'name'=>'new_pass',
				'top'=>'Новый пароль',
			));
		}

		$this->add(array(
			'name'=>'send_register_mail',
			'type'=>'check',
			'checked'=>true,
			'right'=>'Отправить пользователю письмо об учётной записи.',
		));
		
		$this->add(array(
			'name'=>'role',
			'top'=>'Роль',
			'type'=>'select',
			'options'=>static::getRolesOptions(),
			'value'=>get_option('default_role'),
		));
		
		$this->add('submitSave');
	}


	/**
	 * Возвращает роли для встаки в поле формы для выбора роли
	 * 
	 * @return array
	 */
	protected static function getRolesOptions() {

		global $wp_roles;
		$rolesOptions = [
			''=>'',
		];

		foreach($wp_roles->roles as $roleKey=>$role) {
			
			$rolesOptions[$roleKey] = translate_user_role($role['name']);
		}
		
		return $rolesOptions;
	}
}