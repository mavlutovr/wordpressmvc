<?php
namespace Wdpro\Form\Elements;

class Recaptcha3 extends Base
{
	protected $error;

	public function __construct($params) {

		$params = wdpro_extend(array(
			'name'=>'recaptcha3',
			'error_text'=>'Ошибка Recaptcha 3',
		), $params);

		parent::__construct($params);
	}


	/**
	 * Проверка поля на правильное заполнение
	 *
	 * @param $formData
	 * @return bool
	 */
	public function valid($formData)
	{
		$keySite = get_option('wdpro_recaptcha3_site');
		$keySecret = get_option('wdpro_recaptcha3_secret');

		if ($keySite && $keySecret) {

			$token = $formData['recaptcha3'];

			$json = file_get_contents(
				'https://www.google.com/recaptcha/api/siteverify?secret='
				.$keySecret
				.'&response='
				.$token);

			$response = json_decode($json, true);

			if ($response['success'] === true && $response['score'] > 0.5) {
				return true;
			}

			$this->error = $this->params['error_text'];
			return false;
		}

		return true;
	}


	public function getError() {
		return $this->error;
	}


	/**
	 * Возвращает данные для сохранения в базе
	 *
	 * @return mixed
	 */
	public function getSaveValue()
	{

	}


}
