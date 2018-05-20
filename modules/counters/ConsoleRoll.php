<?php
namespace Wdpro\Counters;

class ConsoleRoll extends \Wdpro\Console\Roll {

	/**
	 * Возвращает параметры списка (необходимо переопределить в дочернем классе для
	 * установки настроек)
	 *
	 * @return array
	 */
	public static function params() {

		return array(
			'labels'=>array(
				'label'=>'Счетчики',
				'add_new'=>'Добавить счетчик',
			),
			'where'=>'ORDER BY sorting',
		);
	}


	/**
	 * Возвращает колонки таблицы
	 *
	 * @param array $data Данные строки
	 * @param \Wdpro\BaseEntity $entity
	 * @return array
	 */
	public function template( $data, $entity ) {

		return array(
			'<div style="max-width: 500px; overflow: hidden;"><pre>'.htmlspecialchars
			($data['code']).'</pre></div>',
			$data['sorting'],
		);
	}


	/**
	 * Возвращает заголовки таблицы в виде массива
	 *
	 * @return array
	 */
	public function templateHeaders() {

		return array(
			'Код счетчика',
			'№ п.п.',
		);
	}


}