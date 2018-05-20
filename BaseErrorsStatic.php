<?php
namespace Wdpro;

trait BaseErrorsStatic {

	/**
	 * Список ошибок
	 *
	 * @var array
	 */
	protected static $_errors = [];


	/**
	 * Добавить ошибку
	 *
	 * @param string $error Текст ошибки
	 */
	public function addError($error) {

		static::$_errors[] = $error;
	}


	/**
	 * Возвращает ошибки
	 *
	 * @return array
	 */
	public function getErrors() {

		static::$_errors;
	}
}