<?php
namespace Wdpro\Pay;

/**
 * Интерфейс целевых объектов оплаты
 *
 * @package Modules\pay
 */
interface TargetInterface
{
	/**
	 * Обработка оплаченной транзакации
	 *
	 * @param \Wdpro\Pay\Entity $pay Оплаченная транзакация
	 */
	public function pay(\Wdpro\Pay\Entity $pay);


	/**
	 * Возвращает параметры для транзакации
	 * 
	 * return array(
	 *
	 *      'params'=>array(
	 *          'referer'=>home_url().'/aftersale',
	 *      ),
	 *
	 *      'cost'=>$this->data['cost'],
	 *
	 *      'text'=>get_option('order_text'),
	 * );
	 *
	 * @return array
	 */
	public function payGetParams();


	/**
	 * Возвращает true, если объект уже оплачен
	 *
	 * @return bool
	 */
	public function payGetStatus();


	/**
	 * Возвращает информацию для подтверждения оплаты
	 *
	 * Чтобы человек после того, как заполнил форму, и прямо перед нажатием на кнопку Оплатить, чтобы он увидел всю информацию, которую добавлял. И чтобы еще раз мог проверить, все ли правильно.
	 *
	 * @return string
	 */
	public function payGetConfirmInfoHtml();


	/**
	 * Возвращает номер заказа
	 *
	 * @return string
	 */
	//public function payGetOrderNumber();


	/**
	 * Возвращает E-mail покупателя
	 *
	 * @return string
	 */
	public function getEmail();


	/**
	 * Возвращает Имя покупателя
	 *
	 * @return string
	 */
	public function getName();


	/**
	 * Возвращает фамилию покупателя
	 *
	 * @return string
	 */
	public function getLastName();


	/**
	 * Возвращает ключ объекта, 
	 * 
	 * по которому потом можно получить этот объект с помощью функции 
	 * wdpro_object_by_key() 
	 * 
	 * @return string
	 */
	public function getKey();


	/**
	 * Возвращает ID пользователя/посетителя, который платит
	 * 
	 * Это может быть E-mail или телефон или еще что-нибудь
	 * Это необходимо для Яндекс кассы
	 * 
	 * @return string
	 */
	public function getCustomerNumber();
}
