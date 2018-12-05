<?php
namespace Wdpro\Form\Elements;

class Checks extends Select
{


	/**
	 * Дополнительная обработка данных запущенной формы этим полем
	 *
	 * @param array $formData Данные запущенной формы
	 * @return mixed|string
	 */
	public function getDataFromSubmit($formData)
	{
		$return =  parent::getDataFromSubmit($formData);

		if ($return === 'null') {
			$return = null;
		}

		return $return;
	}


}
