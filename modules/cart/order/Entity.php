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
			'color'=>'#CCCCCC',
		],

		'process' => [
			'text'=>'Выполняется',
			'color'=>'#87c1ff',
		],

		'done' => [
			'text'=>'Выполнен',
			'color' =>'#94e65e',
		],

		'canceled' => [
			'text'=>'Отменен',
			'color'=>'#fda193',
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
			.'&se='.$this->getData('secret');
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
	 * Обработка данных для шаблона
	 *
	 * @param array $data Необработанные данные
	 * @return array Обработанные данные
	 */
	public function prepareDataForTemplate($data)
	{
		// Данные покупателя
		$data['formData'] = $this->getCustomerHtml();


		// Статус заказа
		$statusData = $this->getStatusData();
		$data['statusText'] = $statusData['text'];


		// Товары
		$summaryInfo = \Wdpro\Cart\Controller::getSummaryInfo([
			'orderId' => $this->id(),
		]);
		$summaryInfo = apply_filters('wdpro_order_cart_summary_info', $summaryInfo);
		$data['cart'] = wdpro_render_php(
			WDPRO_TEMPLATE_PATH.'order_goods.php',
			$summaryInfo
		);


		// Адрес
		$data['url'] = $this->getUrl();


		// Другие данные
		foreach ($summaryInfo as $key => $value) {

			if (!isset($data[$key]) && (is_numeric($value) || is_string($value))) {
				$data[$key] = $value;
			}
		}

		return $data;
	}


	/**
	 * Заменяет в шаблоне шорткоды на соответствующие элементы
	 *
	 * @param string $template Шаблон
	 * @return string
	 */
	public function getHtmlByTemplate($template) {


		$templateData = $this->getDataForTemplate();


		foreach ($templateData as $key => $value) {

			if (is_numeric($value) || is_string($value)) {
				$template = str_replace(
					'['.$key.']',
					$value,
					$template
				);
			}
		}

		return $template;
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

			if ($label && $name !== 'privacy') {
				$template[$name] = [
					'label'=>$label,
					'value'=>$formData[$name],
				];
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


	public function sendFirstEmail() {

		$template = \Wdpro\Sender\Templates\Email\Controller::getTemplate('order_checkout', [
			'default' => function () {

				return require __DIR__.'/default/emails/order.php';
			},
		]);

		$templateData = $this->getDataForTemplate();


		// Отправка
		// Покупателю
		$template->send(
			$this->getEmail(),
			$templateData);

		// Администратору
		$template->send(
			wdpro_get_option('wdpro_order_admin_email', get_option('admin_email')),
			$templateData
		);

	}


	/**
	 * Возвращает E-mail покупателя
	 *
	 * @return array|mixed
	 */
	public function getEmail() {
		return $this->getData('email');
	}


	/**
	 * Возвращает html код блока статуса для админки
	 *
	 * @return string
	 */
	public function getConsoleStatus() {

		$status = $this->getStatusData();


		// Цвет текущего
		$color = '';
		if ($status['color']) {
			$color = ' background: '.$status['color'].';';
		}


		// Список
		$list = '';
		foreach (static::$statuses as $itemStatusKey => $itemStatus) {

			$itemColor = '';
			if ($itemStatus['color'])
				$itemColor = 'background: '.$itemStatus['color'].';';

			$list .= '<div class="js-order-statuc-item order-status-item" style="'.$itemColor.'" data-status="'.$itemStatusKey.'">'.$itemStatus['text'].'</div>';
		}


		return '<div class="js-order-status order-status">
			<div class="js-order-status-current order-status-current" style="'.$color.'">'.$status['text'].'</div>
			
			<div class="js-order-status-list order-status-list g-hid">'.$list.'</div>
		</div>';
	}


	/**
	 * Устанавливает статус
	 *
	 * @param string $status Новый статус
	 * @return $this
	 */
	public function setStatus($status) {
		$this->mergeData([
			'status'=>$status,
		]);
		return $this;
	}


	/**
	 * Возвращает список товаров
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getConsoleGoods() {
		return \Wdpro\Cart\Controller::getConsoleGoods($this->id());
	}



}