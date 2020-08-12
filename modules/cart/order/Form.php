<?php
namespace Wdpro\Cart\Order;

/**
 * Форма оформления заказа
 *
 * Эта форма для примера. Вы можете использовать и ее, если она устраивает.
 *
 * Но когда вы хотите сделать другую форму, просто создайте еще один класс формы. Например, в своем собственном модуле app/order/
 *
 * И затем замените стандартную форму своей следующим образом:
 *
 * <code>
 * \Wdpro\Cart\Order\Controller::setFormClass( Form::class );
 * </code>
 *
 * @package Wdpro\Cart\Order
 */

class Form extends \App\BaseForm {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields()
	{
		$this->add([
			'name'=>'fio',
			'top'=>'ФИО',
			'*'=>true,
		]);

		$this->add([
			'name'=>'phone',
			'top'=>'Телефон',
			'*'=>true,
		]);

		$this->add([
			'name'=>'email',
			'top'=>'E-mail',
			'*'=>true,
		]);

		$this->add([
			'type'=>static::SUBMIT,
			'value'=>'Оформить заказ',
		]);

		$this->addPrivacy();
	}


}