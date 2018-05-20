<?php
namespace Wdpro\Person;

class ExtraData extends \Wdpro\BaseEntity {

	/**
	 * Дополнительная таблица
	 *
	 * @return \Wdpro\BaseSqlTable
	 */
	public static function getSqlTableClass() {

		return ExtraDataTable::class;
	}


}