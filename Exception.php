<?php
namespace Wdpro;

/**
 * Исключение Wdpro
 * 
 * @package Wdpro
 */
class Exception extends \Exception
{
	/**
	 * @param string $message Сообщение ошибки
	 * @param int $deep Номер места от этой назад по ходу движения скрипта, 
	 * о котором показать ошибку (лучше просто потестить 1, 2, 3...)
	 * @param int $code Код ошиюки
	 * @param \Exception|null $previous Предыдущее исключение
	 */
	public function __construct($message, $deep=0, $code = 0, \Exception $previous = null)
	{
		if ($deep)
		{
			$backtraces = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 20+$deep);
			// print_r($backtraces);
			$info = $backtraces[$deep];

			$this->file = $info['file'];
			$this->line = $info['line'];
		}


		parent::__construct($message, $code, $previous);
	}


	/**
	 * Вернет аргумент, который был получен функцией
	 *
	 * @param int $deep Номер места от этой назад по ходу движения скрипта,
	 * о котором показать ошибку (лучше просто потестить 1, 2, 3...)
	 * @param int $argNumber Номер аргумента
	 * @return mixed
	 */
	public static function arg($deep=0, $argNumber=0)
	{
		$backtraces = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2 + $deep);
		$info = $backtraces[1];
		
		return $info['args'][$argNumber];
	}
	
	
	
}