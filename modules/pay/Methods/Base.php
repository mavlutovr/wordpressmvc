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
		
		return get_option('pay_method_'.static::getName().'_enabled') == 1;
	}
}