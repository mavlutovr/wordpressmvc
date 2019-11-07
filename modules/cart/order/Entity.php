<?php
namespace Wdpro\Cart\Order;

/**
 * Основная сущность модуля
 */
class Entity extends \Wdpro\BaseEntity {


	/**
	 * Статусы заказа
	 *
	 * @var array
	 */
	protected static $statuses = [

		'' => [
			'text'=>'На обработке',
		],

		'process' => [
			'text'=>'Выполняется',
		],

		'done' => [
			'text'=>'Выполнен',
		],

		'canceled' => [
			'text'=>'Отменен',
		],

	];


	/**
	 * Подготавливает данные для сохранения перед первым сохранением в базе
	 *
	 * В вордпресс страницы сохраняются сразу, как только была открыта форма создания.
	 * То есть еще до того, как заполнили форму создания.
	 *
	 * Этот метод обрабатывает данные как бы перед нормальным созданием. Когда уже
	 * заполнили форму. И это обработка данных первого сохранение формы.
	 *
	 * @param array $data Исходные данные
	 *
	 * @return array
	 */
	protected function prepareDataForCreate($data)
	{
		$data['time'] = time();
		$data['secret'] = md5(json_encode($data, JSON_UNESCAPED_UNICODE));

		return $data;
	}


	/**
	 * Подготавливает данные для сохранения
	 *
	 * @param array $data Исходные данные
	 * @return array
	 */
	protected function prepareDataForSave($data)
	{
		if (isset($data['form']['email'])) {

			$data['email'] = $data['form']['email'];
		}

		return $data;
	}


	/**
	 * Возвращает адрес карточки заказа
	 *
	 * @return string
	 */
	public function getUrl() {
		return home_url().'/order'.wdpro_url_slash_at_end()
			.'?i='.$this->id()
			.'&s='.$this->getData('secret');
	}


	/**
	 * Проверяет на соответствие секретную строку
	 *
	 * @param string $secret Секретная строка
	 * @return bool
	 */
	public function checkSecret($secret) {
		return $this->getData('secret') === $secret;
	}


	/**
	 * Возвращает html код данных, которые указал покупатель
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getCustomerHtml() {

		$template = [];
		$formData = $this->getData('form');


		$form = Controller::getForm();
		$form->eachElements(function ($element) use (&$formData, &$template) {

			/** @var \Wdpro\Form\Elements\Base $element */

			$label = $element->getLabel();
			$name = $element->getName();

			if ($label) {
				$template[$name] = $formData[$name];
			}
		});


		return wdpro_render_php(
			WDPRO_TEMPLATE_PATH.'/order_card_customer.php',
			[
				'form'=>$template
			]
		);
	}


	/**
	 * Возвращает информацию о статусе заказа
	 *
	 * @return array
	 */
	public function getStatusData() {

		$currentStatus = $this->getData('status');

		if (isset(static::$statuses[ $currentStatus ])) {

			return static::$statuses[ $currentStatus ];
		}

		return [ 'text' => 'Неизвестен' ];
	}

}