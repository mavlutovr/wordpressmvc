<?php
/**
 * Общий набор тестов
 */
require_once __DIR__.'/start.php';

class All {
	
	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('CoreSuite');
		$suite->addTestFiles([
			__DIR__.'/modules/form/FormTest.php',
		]);
		
		return $suite;
	}
}