<?php
namespace Wdpro\Form\Elements;

class Html extends Base {

	/**
	 * Возвращает данные для сохранения в базе
	 *
	 * @return mixed
	 */
	public function getSaveValue() {

		
	}


	/**
	 * Дополнительная обработка данных запущенной формы этим полем
	 *
	 * @param array $formData Данные запущенной формы
	 * @returns mixed
	 */
	public function getDataFromSubmit( $formData ) {

		
	}


	/**
	 * Проверка поля на правильное заполнение
	 *
	 * @param $formData
	 * @return bool
	 */
	public function valid( $formData ) {

		return true;
	}


}