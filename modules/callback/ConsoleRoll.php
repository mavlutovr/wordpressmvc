<?php
namespace Wdpro\Callback;

class ConsoleRoll extends \Wdpro\Console\Roll
{
	public static function params() {

		$count = 10;
		
		return array(
			'labels'=>array(
				'label'=>'Заказ звонков',
			),
			'where'=>'ORDER BY id DESC',
			'pagination'=>10,
		);
	}


	/**
	 * Возвращает колонки таблицы
	 *
	 * @param array $data Данные строки
	 * @param \Wdpro\BaseEntity $entity Объект списка
	 * @return array
	 */
	public function template( $data, $entity ) {

		return array(
			wdpro_date($data['time']),
			$data['data']['name'],
			$data['data']['phone'],
		);
	}


	/**
	 * Возвращает заголовки таблицы в виде массива
	 *
	 * @return array
	 */
	public function templateHeaders() {

		return array(
			'Время',
			'Имя',
			'Телефон',
		);
	}


}