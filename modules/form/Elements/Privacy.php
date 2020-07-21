<?php

namespace Wdpro\Form\Elements;

class Privacy extends Check
{

	public function __construct($params)
	{

		$params = wdpro_extend(array(
			'name' => 'privacy',
			'required' => true,
		), $params);

		parent::__construct($params);
	}


	/**
	 * Возвращает значение поля для отправки
	 *
	 * @return string
	 */
	public function getSendValue()
	{

	}


}
