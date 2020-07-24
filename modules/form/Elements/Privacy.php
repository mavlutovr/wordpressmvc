<?php

namespace Wdpro\Form\Elements;

class Privacy extends Check
{

	public function __construct($params)
	{

		if (\Wdpro\Lang\Data::enabled()) {
			$right = [

				'ru'=>'Я даю свое согласие на обработку персональных данных и соглашаюсь с условиями и <a href="'.wdpro_home_url_with_lang(true).'/privacy-policy/" target="_blank">политикой конфиденциальности</a>.',

				'en'=>'I give my consent to the processing of personal data and agree to the terms and <a href="'.wdpro_home_url_with_lang(true).'/privacy-policy/" target="_blank">privacy policy</a>.',
			];
		}
		else {
			$right = 'Я даю свое согласие на обработку персональных данных и соглашаюсь с условиями и <a href="'.wdpro_home_url_with_lang(true).'/privacy-policy/" target="_blank">политикой конфиденциальности</a>.';
		}

		$params = wdpro_extend(array(
			'name' => 'privacy',
			'right' => $right,
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
