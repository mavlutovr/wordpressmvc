<?php
namespace Wdpro\Pay\Methods;

use Wdpro\Exception;

class Base {

	/**
	 * Возвращает имя метода оплаты
	 * 
	 * @return string
	 * @throws Exception
	 */
	public static function getName() {
		
		throw new Exception(
			'В класс '.get_called_class().' надо добавить метод getName()'
		);
	}


	/**
	 * Возвращает форму для начала оплаты
	 *
	 * @param \Wdpro\Pay\Entity $pay Транзакция
	 * @return string
	 * @throws Exception
	 */
	public static function getBlock($pay) {

		throw new Exception(
			'Надо переопределить метод getBlock() в классе '.get_called_class()
		);
	}


	/**
	 * Проверяет. включен ли метод оплаты
	 *
	 * @return bool
	 */
	public static function enabled() {
		
		// Demo только для админов
		if (!is_admin() && static::isDemo()) return false;


		return !!get_option('pay_method_'.static::getName().'_enabled');

	}


	public static function isDemo() {
		return !!get_option('pay_method_' . static::getName() . '_test');
	}


	public static function isTest() {
		return static::isDemo();
	}
}