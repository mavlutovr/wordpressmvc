<?php

namespace Wdpro\Form\Elements;

class Privacy extends Check
{

	public function __construct($params)
	{

		$params = wdpro_extend(array(
			'name' => 'privacy',
			'required' => true
		), $params);

		parent::__construct($params);
	}


	/**
	 * Возвращает данные для сохранения в базе
	 *
	 * @return mixed
	 */
	public function getSaveValue()
	{


	}


	/**
	 * Дополнительная обработка данных запущенной формы этим полем
	 *
	 * @param array $formData Данные запущенной формы
	 * @returns mixed
	 */
	public function getDataFromSubmit($formData)
	{


	}
}