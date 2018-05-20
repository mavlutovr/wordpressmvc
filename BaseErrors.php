<?php
namespace Wdpro;

trait BaseErrors {

	/**
	 * Список ошибок
	 * 
	 * @var array
	 */
	protected $_errors = [];


	/**
	 * Добавить ошибку
	 * 
	 * @param string $error Текст ошибки
	 */
	public function addError($error) {
		
		$this->_errors[] = $error;
	}


	/**
	 * Возвращает ошибки
	 * 
	 * @return array
	 */
	public function getErrors() {
		
		return $this->_errors;
	}
}