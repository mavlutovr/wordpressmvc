<?php
namespace Wdpro\Person;

class ConsoleRoll extends \Wdpro\Console\Roll {

	/**
	 * Возвращает параметры списка (необходимо переопределить в дочернем классе для
	 * установки настроек)
	 *
	 * <pre>
	 * return array(
	 *  'labels'=>array(
	 *   'name'=>'Разделы каталога',
	 *   'label'=>'Каталог',
	 *   'add_new'=>'Добавить раздел...',
	 *  ),
	 *  'order'=>'ASC',
	 *  'orderby'=>'menu_order',
	 *  'icon'=>WDPRO_ICONS_PRODUCTS,
	 *      // https://developer.wordpress.org/resource/dashicons/#lock
	 *
	 *  'subsections'=>false,
	 *  'where'=>["WHERE ... %d, %d", [1, 2]],
	 *  'pagination'=>10, //  Количество элементов на странице
	 *  'info'=>'<p>Всякая информация над списком элементов</p>'
	 * );
	 * </pre>
	 *
	 * @return array
	 */
	public static function params() {

		return [
			'labels'=>[
				'label'=>'Пользователи',
				'add_new'=>'Добавить пользователя',
			],
			//'orderby'=>'user_login',
			'icon'=>'fa-male',
			//'subsections'=>false,
			'where'=>'WHERE 1 ORDER BY `id` DESC',

			'pagination'=>20,
		];
	}


	/**
	 * Возвращает колонки таблицы
	 *
	 * @param array $data Данные строки
	 * @param \Wdpro\BaseEntity $entity
	 * @return array
	 */
	public function template( $data, $entity ) {
		
		// Данные пользователя
		$userData = get_userdata($data['ID']);
		
		// Роль
		$rolesList = $userData->roles;
		$roles = [];
		global $wp_roles;
		foreach($rolesList as $roleKey) {
			$roles[] = translate_user_role($wp_roles->roles[$roleKey]['name']);
		}
		
		return [
			$data['ID'],
			$data['user_email'],
			implode(', ', $roles)
		];
	}


	/**
	 * Возвращает заголовки таблицы в виде массива
	 *
	 * @return array
	 */
	public function templateHeaders() {

		return [
			'ID',
			'E-mail',
			'Роль',
		];
	}


}