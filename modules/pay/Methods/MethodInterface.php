<?php
namespace Wdpro\Pay\Methods;

interface MethodInterface {

	/**
	 * Инициализация метода
	 */
	public static function init();


	/**
	 * Запускается в админке
	 * 
	 * В этом методе можно добавиьт например какие-нибудь кнопки в меню админки
	 */
	public static function runConsole();


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite();

	
	/**
	 * Возвращает имя метода оплаты
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function getName();


	/**
	 * Возвращает название метода русскими буквами для использования во всяких текстах
	 * 
	 * @return mixed
	 */
	public static function getLabel();


	/**
	 * Возвращает форму для начала оплаты
	 *
	 * @param \Wdpro\Pay\Entity $pay Транзакция
	 * @return string
	 */
	public static function getBlock( $pay );


	/**
	 * Проверяет. включен ли метод оплаты
	 * 
	 * @return bool
	 */
	public static function enabled();

}