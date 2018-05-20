<?php
namespace Wdpro\Sender;

class SqlTableCron extends \Wdpro\BaseSqlTable {

	protected static $name = 'wdpro_sender_mail_cron';


	/**
	 * Структура таблицы
	 *
	 * @return array array('sql'=>'CREATE TABLE ...', 'format'=>array('field_name'=>'%d')
	 */
	protected static function structure() {

		return array(

			static::COLLS => array(
				'id',
				'sended'=>'int',
				'to'=>'json',
				'subject',
				'message'=>'text',
				'headers'=>'json',
				'atachments'=>'json',
				'smtp'=>'json',
			),

			static::INDEX => array(
				array('sended'),
			),

			static::ENGINE => static::INNODB,
		);
	}


}