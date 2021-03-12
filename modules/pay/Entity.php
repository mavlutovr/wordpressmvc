<?php
namespace Wdpro\Pay;

class Entity extends \Wdpro\BaseEntity {

	/**
	 * Подготавливает данные для сохранения перед первым сохранением в базе
	 *
	 * @param array $data Исходные данные
	 * @return array
	 */
	protected function prepareDataForCreate( $data ) {

		$data['secret'] = md5(rand(1000000, 10000000));
		$data['created'] = time();

		if (!isset($data['visitor_id']))
			$data['visitor_id'] = wdpro_visitor_session_id();

		if (!isset($data['person_id']))
		$data['person_id'] = wdpro_person_auth_id();
		
		return $data;
	}


	/**
	 * Возвращает ID пользователя, который платит
	 * 
	 * @return string|number
	 */
	public function getCustomerNumber() {
		
		$target = $this->target();
		
		return $target->getCustomerNumber();
	}


	/**
	 * Возвращает адрес страницы выбора способа оплаты
	 * 
	 * @return string
	 */
	public function getUrl() {
		
		return home_url().'/pay?in='.$this->id().'&sw='.$this->data['secret'];
	}


	/**
	 * Сохранение данных Result
	 *
	 * @param $post
	 * @return $this
	 */
	public function setResultPost($post)
	{
		$this->mergeData(array(
			'result_post'=>$post,
		));
		
		return $this;
	}


	/**
	 * Подтверждение транзакции
	 *
	 * @param string $methodName Имя способа оплаты
	 * @param int $confirm (1 - подтверждена, -1 - отклонена)
	 */
	public function confirm($methodName, $confirm=1)
	{
		// Запоминаем, что транзакция подтверждена
		$this->data['confirm'] = $confirm;

		// Имя способа оплаты
		$this->data['method_name'] = $methodName;

		// Сохраняем транзакцию
		$this->save();

		// Если транзакция подтверждена
		if ($confirm)
		{
			// Получаем целевой объект
			$target = wdpro_object_by_key($this->data['target_key']);
			/** @var $target \Wdpro\Pay\TargetInterface */

			// Отправляем транзакцию в целевой объект для зачисления средств
			return $this->target()->pay($this);
		}
	}


	/**
	 * Update pay (renewal)
	 *
	 * @param array $data
	 * @return void
	 */
	public function update($data) {
		$this->target()->payUpdate($this, $data);
	}


	/**
	 * Установка подписи
	 *
	 * @param $signature
	 */
	public function setSignature($signature)
	{
		$this->data['signature'] = $signature;
	}

	
	/**
	 * Отмена транзакции
	 *
	 * @param string $methodName Имя способа оплаты
	 */
	public function cancel($methodName)
	{
		// Запоминаем что транзакция отменена
		$this->data['confirm'] = 0;

		// Имя способа оплаты
		$this->data['method_name'] = $methodName;

		// Сохраняем транзакцию
		$this->save();
	}


	/**
	 * Возвращает номер заказа оплаты (id транзакции)
	 *
	 * @return int
	 */
	public function getOrderNumber()
	{
		return $this->id();
		//return $this->target()->payGetOrderNumber();
	}


	/**
	 * Возвращает скрытую форму оплаты
	 *
	 * @return string
	 */
	public function getHiddenForm()
	{
		return $this->method()->getHiddenForm($this->arr);
	}


	/**
	 * Возвращает URL страницы после успешной оплаты
	 *
	 * @return string
	 */
	public function getAftersaleUrl() {
		if (isset($this->data['params']['aftersale']))
		{
			return $this->data['params']['aftersale'];
		}
	}


	/**
	 * Осуществляет редирект на страницу после успешной оплаты
	 */
	public function gotoAftersaleUrl() {
		if ($url = $this->getAftersaleUrl()) {
			wdpro_location(urldecode($url));
			exit();
		}
	}


	/**
	 * Возвращает Url Страницы ошибки
	 *
	 * @return string
	 */
	public function getErrorUrl()
	{
		if (isset($this->data['params']['pay-error']))
		{
			return $this->data['params']['pay-error'];
		}
	}


	/**
	 * Переход на странице ошибки
	 */
	public function goToErrorUrl() {
		if ($url = $this->getErrorUrl()) {
			wdpro_location(urldecode($url));
			exit();
		}
	}


	/**
	 * Возвращает реферал
	 *
	 * @return string
	 */
	public function getReferer()
	{
		if (isset($this->data['params']['referer']))
		{
			return $this->data['params']['referer'];
		}
	}


	/**
	 * Переход к странице, где была нажата кнопка "Оплатить"
	 */
	public function goToReferer()
	{
		if ($referer = $this->getReferer())
		{
			wdpro_location(urldecode($referer));
			exit();
		}
	}


	/**
	 * Возвращает сумму транзакации
	 *
	 * @return int
	 */
	public function getCost()
	{
		return $this->data['cost'] * 1;
	}


	/**
	 * Возвращает описание покупки
	 * 
	 * @return string
	 */
	public function getText() {
		return $this->data['text'];
	}


	/**
	 * Возвращает сообщение об оплате, что оплачивается
	 *
	 * @return string
	 */
	public function getMessage()
	{
		if (isset($this->data['params']['message']))
		{
			return $this->data['params']['message'];
		}
	}


	/**
	 * Возвращает имя метода оплаты, с помощью которого была оплачена транзакция
	 *
	 * @return string
	 */
	public function getMethodName()
	{
		return $this->method()->getName();
	}


	/**
	 * Возвращает объект использованного для оплаты метода
	 *
	 * @return \Wdpro\Pay\Methods\MethodInterface
	 */
	public function method()
	{
		return Controller::getMethodClass($this->data['method_name']);
	}


	/**
	 * Возвращает название метода оплаты русскими буквами
	 * 
	 * @return string
	 */
	public function getMethodLabel() {
		$method = $this->method();
		
		return $method::getLabel();
	}


	/**
	 * Возвращает целевой объект
	 *
	 * @return TargetInterface|\Wdpro\BaseEntity
	 */
	public function target()
	{
		return wdpro_object_by_key($this->data['target_key']);
	}


	/**
	 * Возвращает комментарий к заказу
	 *
	 * @return string
	 */
	public function getComment()
	{
		if (!empty($this->data['params']['message'])) {
			return $this->data['params']['message'];
		}
		
		return $this->target()->payGetItemName();
	}


	/**
	 * Возвращает E-mail полкупателя
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->target()->getEmail();
	}


	/**
	 * Возвращает имя покупателя
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->target()->getName();
	}


	/**
	 * Возвращает фамилию покупателя
	 *
	 * @return string
	 */
	public function getLastName()
	{
		return $this->target()->getLastName();
	}


	/**
	 * Возвращает адрес, куда отправить пользователя после завершения платежа
	 *
	 * @return string
	 */
	public function getReturnUrl()
	{
		return $this->data['params']['referer'];
	}


	/**
	 * Транзакция в процессе выполнения
	 *
	 * @return bool
	 */
	public function process()
	{
		return $this->data['confirm'] == 0;
	}


	/**
	 * Проверка секретного кода
	 * 
	 * @param string $secret Секретный код
	 * @return bool
	 */
	public function secretIsCorrect($secret) {
		
		return $this->data['secret'] == $secret;
	}


	/**
	 * запоминание вссякой информации
	 *
	 * @param array $data Данные
	 * @return $this
	 */
	public function mergeInfo($data)
	{
		if (get_option('pay_methods_not_exists_message') == 1) {
			$this->data['info'] = wdpro_extend($this->data['info'], $data);
		}

		return $this;
	}


	/**
	 * Установка ID в виде hash строки
	 * 
	 * @param string $hashId ID в виде hash строки
	 * @return $this
	 */
	public function setHashId($hashId) {
		
		$this->data['hash_id'] = $hashId;
		
		return $this;
	}


	/**
	 * Return additional data for pay transaction (user_id, order_id, etc.)
	 *
	 * @return void
	 */
	public function getCustomData() {
		return [
			'pai'=>$this->id(),
			'pas'=>$this->data['secret'],
		];
	}
}