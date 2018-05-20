<?php
namespace Wdpro\Person;

class Entity extends \Wdpro\BaseEntity {

	/**
	 * Автоматически созданный пароль при создании пользователя
	 * 
	 * @var string
	 */
	protected $createdPass;

	/**
	 * Класс дополнительных параметров
	 * 
	 * @var ExtraData
	 */
	protected static $extraDataClass = ExtraData::class;

	/**
	 * Подготавливает данные для сохранения перед первым сохранением в базе
	 *
	 * @param array $data Исходные данные
	 * @return array|bool
	 */
	protected function prepareDataForCreate( $data ) {

		// Логин
		if (!isset($data['user_login']) || !$data['user_login']) {
			
			$data['user_login'] = $data['user_email'];
		}
		
		// Проверка по логину
		if (static::sqlTable()->count([
			'WHERE `user_login`=%s', [$data['user_login']]
		]) > 0)
			return false;

		// Пароль
		$password = wdpro_generate_password();
		$this->createdPass = $password;
		
		// Создание пользователя
		$id = wp_create_user($data['user_login'], $password, $data['user_email']);
		if (is_wp_error($id)) {
			$text = $id->get_error_message()
				."\n\n<BR><BR>\n\n"
				.print_r($data, 1)
				."\n\n<BR><BR>\n\n"
				.print_r($_GET, 1)
			;
			
			echo($text);

			\Wdpro\AdminNotice\Controller::sendMessageHtml(
				'Ошибка создания пользователя',
				$text
			);
			return false;
		}

		// Удалось добавить
		if (is_numeric($id)) {
			
			// Обновляем другие всякие данные из формы
			$data['ID'] = $id;
			wp_update_user($data);

			// Добавляем пароль, чтобы его можно было использовать дальше, например,
			// для отправки в письме
			$data['user_pass'] = $password;

			// Обработка данных фильтрами
			$data = apply_filters('wdpro_person_create', $data);

			// Отправка письма
			if (isset($data['send_register_mail']) && $data['send_register_mail']) {
				
				\Wdpro\Sender\Templates\Email\Controller::send(
					'person_register',
					$data['user_email'],
					$data,
					[
						'default'=>require(__DIR__.'/default/registerEmail.php'),
					]
				);
				
			}
			
			unset($data['user_pass']);
		}

		return $data;
	}


	/**
	 * Подготавливает данные для сохранения
	 *
	 * @param array $data Исходные данные
	 * @return array
	 */
	protected function prepareDataForSave( $data ) {

		$data = apply_filters('wdpro_person_save', $data);
		
		return parent::prepareDataForSave( $data );
	}


	/**
	 * Срабатывает после сохранения
	 */
	protected function onChange() {

		// Новый пароль
		if (isset($this->data['new_pass']) && $this->data['new_pass']) {
			
			wp_set_password($this->data['new_pass'], $this->id());
		}
	}


	/**
	 * Возвращает данные сущности
	 *
	 * @param null|string $key Ключ данных, которые необходимо получить.
	 * Если ключ не указывать, то будут возвращены все данные
	 * @return array
	 */
	public function getData( $key = null ) {

		$data = parent::getData($key);
		
		$extraData = $this->extraData()->getData($key);
		
		if ($key) {
			
			return $extraData ? $extraData : $data;
		}
		
		else {
			
			return wdpro_extend($data, $extraData);
		}
	}


	/**
	 * Возвращает настройку
	 * 
	 * @param null|string $key Имя настройки
	 * @return array|mixed
	 */
	public function getParam($key=null) {
		
		$params = $this->getData('params');
		
		if ($key) {
			if (isset($params[$key])) {
				return $params[$key];
			}
		}
		else {
			
			return $params;
		}
	}


	/**
	 * Возвращает имя пользователя
	 *
	 * @return string
	 */
	public function getFirstName() {

		return get_user_meta($this->id(), 'first_name');
	}


	/**
	 * Возвращает фамилию пользователя
	 *
	 * @return string
	 */
	public function getLastName() {

		return get_user_meta($this->id(), 'last_name');
	}


	/**
	 * Возвращает E-mail пользователя
	 * 
	 * @return string
	 */
	public function getEmail() {
		
		return $this->data['user_email'];
	}


	/**
	 * Сохранение
	 *
	 * @returns bool|array (false или сохраненные данные)
	 * @throws EntityException
	 */
	public function save() {

		$data = parent::save();

		if ($data) {
			
			/** @var ExtraData $params */
			$extraData = $this->extraData();
			$data['id'] = $this->id();
			//$extraData->mergeData($data);
			foreach($data as $key=>$value) {
				$extraData->data[$key] = $value;
			}
			$extraData->save();
		}
		
		return $data;
	}


	/**
	 * Срабатывает после удаления
	 */
	protected function onRemove() {

		$this->extraData()->remove();
	}


	/**
	 * Возвращает объект параметров, который хранид дополнительные данные
	 * 
	 * @return ExtraData
	 * @throws \Exception
	 */
	public function extraData() {
		
		return wdpro_object(static::$extraDataClass, $this->data[$this->idField()]);
	}


	/**
	 * Обновление параметров
	 *
	 * @param array $params Параметры
	 * @return ExtraData
	 */
	public function mergeParams($params) {

		$extraData = $this->extraData();
		$extraData->mergeData([
			'params'=>$params,
		]);
		return $extraData;
	}

	
	/**
	 * Возвращает автоматически созданный пароль при создании пользователя
	 *
	 * @return string
	 */
	public function getCreatedPass() {

		return $this->createdPass;
	}

}