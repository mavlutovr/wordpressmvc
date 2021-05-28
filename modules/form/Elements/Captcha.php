<?php
namespace Wdpro\Form\Elements;
require_once __DIR__.'/../captcha/simple-php-captcha.php';


class Captcha extends Base
{
  protected $errorText;

  public function __construct($params) {

    if (!isset($params['name'])) {
      $params['name'] = 'captcha';
    }

    if (!isset($params['error_text'])) {
      $params['error_text'] = 'Вы не верно указали контрольный код.';
    }

    parent::__construct($params);
  }


  public function getParams() {
    $params = parent::getParams();

    $_SESSION['captcha'] = simple_php_captcha([
      'min_length'=>2,
    ]);
    $params['src'] = $_SESSION['captcha']['image_src'];
    $params['required'] = true;
    $params['name'] = 'captcha';

    return $params;
  }


  /**
	 * Проверка поля на правильное заполнение
	 *
	 * @param $formData
	 * @return bool
	 */
	public function valid($formData)
	{
    $name = $this->params['name'];

    if (
      empty($_SESSION[$name]['code'])
      || mb_strtolower($formData[$name]) !== mb_strtolower($_SESSION['captcha']['code'])
    ) {
      // echo(mb_strtolower($formData[$name].PHP_EOL));
      // echo(mb_strtolower($_SESSION['captcha']['code'].PHP_EOL));

      $this->errorText = $this->params['error_text'];
      return false;
    }
    $this->errorText = 'test';

    return true;
	}


  public function getError() {
    return $this->errorText;
  }
}
