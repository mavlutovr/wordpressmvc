<?php
namespace Wdpro\Lang;

/**
 * Класс данных о языках, которые доступны еще до инициализации sql таблиц
 *
 * Это нужно, чтобы при инициализации таблиц уже были данные о языках и их можно было
 * использовать для обработки таблиц. Чтобы для каждого языка автоматом добавлялись поля.
 *
 * @package Wdpro\Lang
 */
class Data {

	protected static $jsonFileName = 'langs.json';
	protected static $data=[];
	protected static $inited = false;


	/**
	 * Инициализация
	 *
	 * @return bool
	 */
	protected static function init() {
		if (static::$inited) return false;
		static::$inited = true;

		if (is_file(__DIR__.'/'.static::$jsonFileName)) {
			$json = file_get_contents(__DIR__.'/'.static::$jsonFileName);
			static::$data = json_decode($json, 1);
		}
	}


	/**
	 * Сохранение данных о языках
	 *
	 * Здесь специально сделано так, чтобы из uris не удалялись данные. Чтобы при
	 * случайном удалении языка во время обновления таблиц, колонки удаленного языка не
	 * удалились. Чтобы колонки таблицы мог удалить только программист. Удалив язык из
	 * langs.json
	 *
	 * @param array $data Данные
	 */
	public static function setData($data) {
		static ::init();

		$uris = [];
		if (isset(static::$data['uris'])) {
			$uris = static::$data['uris'];
		}

		foreach ($data as $item) {
			$uris[$item['uri']] = $item['uri'];
		}

		static::$data = [
			'langs'=>$data,
			'time'=>time(),
			'uris'=>$uris,
		];

		$json = json_encode(static::$data, JSON_PRETTY_PRINT);
		file_put_contents(__DIR__.'/'.static::$jsonFileName, $json);
	}

	/**
	 * Возвращает массив адресов языков [ '', 'en', 'de' ]
	 */
	public static function getUris() {
		static::init();

		if (isset(static::$data['uris'])) {
			return static::$data['uris'];
		}
	}


	/**
	 * Возвращает данные языков
	 *
	 * @return array
	 */
	public static function getData() {
		static::init();

		if (isset(static::$data['langs'])) {
			return static::$data['langs'];
		}
	}


	/**
	 * Возвращает время последлено обновленя языков
	 *
	 * Это нужно, например, для того, чтобы при инициализации таблиц при добавлении языка
	 * добавить новые поля
	 *
	 * @return int
	 */
	public static function getLastUpdateTime() {
		if (isset(static::$data['time'])) {
			return static::$data['time'];
		}
	}


	/**
	 * Возвращает суффикс с языком
	 *
	 * Это нужно, например, для того,
	 * чтобы делать окончания названий полей в таблице в зависимости от зяыка
	 *
	 * @param $uri
	 *
	 * @return string
	 */
	public static function getPrefix($uri) {
		if (!$uri) return '';

		return '_'.$uri;
	}
}