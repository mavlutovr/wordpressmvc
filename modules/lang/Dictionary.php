<?php
namespace Wdpro\Lang;

use Wdpro\Exception;

/**
 * Перевод стандартных текстов на разные языки
 *
 * Например, даты 15 мая, 15 may, ...
 *
 * Class Dictionary
 * @package Wdpro\Lang
 */
class Dictionary {

	protected static $dictionary = [

		// Дата
		// Сегодня
		'date_today'=>[
			'ru'=>'сегодня',
			'en'=>'today',
			'de'=>'heute',
			'fr'=>'hui',
		],

		// Завтра
		'date_tomorrow'=>[
			'ru'=>'завтра',
			'en'=>'tomorrow',
			'de'=>'morgen',
			'fr'=>'demain',
		],

		// Вчера
		'date_yesterday'=>[
			'ru'=>'вчера',
			'en'=>'yesterday',
			'de'=>'gestern',
			'fr'=>'hier',
		],

		// Месяцы
		'month_01'=>[
			'ru'=>'января',
			'en'=>'january',
			'de'=>'januar',
			'fr'=>'janvier',
		],
		'month_02'=>[
			'ru'=>'февраля',
			'en'=>'february',
			'de'=>'februar',
			'fr'=>'février',
		],
		'month_03'=>[
			'ru'=>'марта',
			'en'=>'march',
			'de'=>'märz',
			'fr'=>'mars',
		],
		'month_04'=>[
			'ru'=>'апреля',
			'en'=>'april',
			'de'=>'april',
			'fr'=>'avril',
		],
		'month_05'=>[
			'ru'=>'мая',
			'en'=>'may',
			'de'=>'mai',
			'fr'=>'mai',
		],
		'month_06'=>[
			'ru'=>'июня',
			'en'=>'june',
			'de'=>'juni',
			'fr'=>'juin',
		],
		'month_07'=>[
			'ru'=>'июля',
			'en'=>'july',
			'de'=>'juli',
			'fr'=>'juillet',
		],
		'month_08'=>[
			'ru'=>'августа',
			'en'=>'august',
			'de'=>'august',
			'fr'=>'août',
		],
		'month_09'=>[
			'ru'=>'сентября',
			'en'=>'september',
			'de'=>'september',
			'fr'=>'septembre',
		],
		'month_10'=>[
			'ru'=>'октября',
			'en'=>'october',
			'de'=>'oktober',
			'fr'=>'octobre',
		],
		'month_11'=>[
			'ru'=>'ноября',
			'en'=>'november',
			'de'=>'november',
			'fr'=>'novembre',
		],
		'month_12'=>[
			'ru'=>'декабря',
			'en'=>'december',
			'de'=>'dezember',
			'fr'=>'décembre',
		],

	];


	/**
	 * Возвращает перевод в зависимости от текущего языка
	 *
	 * @param string $name Имя перевода
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function get($name) {

		if (!isset(static::$dictionary[$name])) {
			throw new Exception('В переводах класса \Wdpro\Lang\Dictionary нету значения с именем '.$name);
		}

		$lang = Data::getCurrentLangUriNotEmpty();

		if (!isset(static::$dictionary[$name][$lang])) {
			throw new \Exception('Не задан перевод: '.$name.' для языка "'.$lang.'"');
		}

		return static::$dictionary[$name][$lang];
	}
}