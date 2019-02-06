<?php
namespace Wdpro\Pay;

use Wdpro\Pay\Methods\MethodInterface;

define('WDPRO_PAY', true);

class Controller extends \Wdpro\BaseController {

	protected static $methods = array();
	protected static $templateListMethods, $templateSingleMethod;


	/**
	 * Инициализация модуля
	 */
	public static function init() {

		wdpro_default_file(
			__DIR__.'/templates/pay_start_list_methods.php',
			WDPRO_TEMPLATE_PATH.'/pay_start_list_methods.php'
		);

		wdpro_default_file(
			__DIR__.'/templates/pay_start_single_method.php',
			WDPRO_TEMPLATE_PATH.'/pay_start_single_method.php'
		);

		static::$templateListMethods = WDPRO_TEMPLATE_PATH.'/pay_start_list_methods.php';
		static::$templateSingleMethod = WDPRO_TEMPLATE_PATH.'/pay_start_single_method.php';
	}


	/**
	 * Возвращает объект транзакции
	 * 
	 * @param int $payId ID транзакции
	 * @return Entity
	 * @throws \Exception
	 */
	public static function getPay($payId) {
		
		/** @var \Wdpro\BaseEntity $obj */
		$obj = wdpro_object(
			Entity::class,
			$payId
		);
		
		if ($obj->loaded()) {
			return $obj;
		}
	}


	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite() {
		
		// Страницы по-умолчанию
		// Страница оплаты
		\Wdpro\Page\Controller::defaultPage('pay', function () {
			return require __DIR__.'/default/pay.page.php';
		});
		// После оплаты
		\Wdpro\Page\Controller::defaultPage('aftersale', function () {
			return require __DIR__.'/default/aftersale.page.php';
		});
		// Ошибка оплаты
		\Wdpro\Page\Controller::defaultPage('pay-error', function () {
			return require __DIR__.'/default/pay-error.page.php';
		});
	}


	/**
	 * Возвращает объект платежки по $_GET данным
	 * 
	 * @return Entity
	 * @throws \Exception
	 */
	public static function getPayByGet() {
		
		if (isset($_GET['in']) && isset($_GET['sw'])) {

			/** @var Entity $pay */
			$pay = wdpro_object( Entity::class, $_GET['in'] );

			if ($pay->secretIsCorrect( $_GET['sw'] )) {

				return $pay;
			}
		}
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole() {

		static::eachMethods(function ($method) {
			
			/** @var MethodInterface $method */
			$method::runConsole();
		});
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {

		// Страница оплаты
		wdpro_on_uri_content('pay', function ($content) {

			$getForm = function () {

				if (isset($_GET['in']) && isset($_GET['sw'])) {

					if ($pay = static::getPayByGet()) {

						return static::getStartBlock(array(
							'pay'=>$pay,
						));
					}

					return 'Ошибка';
				}

				return static::getStartBlock(array(
					'target_key'=>urldecode($_GET['pay']['target']),
					'id'=>$_GET['in'],
				));
			};

			wdpro_replace_or_append(
				$content,
				'[pay_start_form]',
				$getForm()
			);

			return $content;

		});


		static::eachMethods(function ($method) {

			/** @var MethodInterface $method */
			$method::runSite();
		});
	}


	/**
	 * Возвращает блок стартовой страницы
	 *
	 * @param array $params Параметры
	 * @return string
	 */
	public static function getStartBlock($params)
	{
		// Уже есть транзакция
		if (isset($params['pay'])) {

			/** @var Entity $pay */
			$pay = $params['pay'];
			$available = $pay->loaded();
		}
		
		// Загрузка существующей транзакции
		else if (isset($params['id']))
		{
			/** @var Entity $pay */
			$pay = wdpro_object(Entity::class, $params['id']);
			$available = $pay->loaded();
		}

		// Создание новой
		else
		{
			$pay = static::createTransaction($params);
			$available = true;
		}

		// Создаем новую транзакацию
		if ($available)
		{
			// Получаем данные
			$transaction = $pay->getData();

			// Оплачиваемый объект
			/** @var \Wdpro\BaseEntity|\Wdpro\Pay\TargetInterface $target */
			$target = wdpro_object_by_key($transaction['target_key']);

			// Добавляем данные оплачиваемого объекта в шаблон
			$transaction['target'] = $target->getData();

			// Добавляем текст о подтверждении оплаты в шаблон
			$transaction['target_confirm_info'] = $target->payGetConfirmInfoHtml();


			/*if (!is_array($transaction['methods'])) {
				echo '<p>Добавьте хотябы один метод оплаты:</p>
							<code>\Wdpro\Pay\Controller::addMethod(\Wdpro\Pay\Methods\Robokassa::class);</code><BR><BR>';
				throw new \Exception('Нет способа оплаты');
			}*/

			// Перебираем подключенные методы
			static::eachMethods(function ($method) use (&$transaction, &$pay)
			{
				/** @var $method \Wdpro\Pay\Methods\MethodInterface */

				if ($method::enabled()) {
					$transaction['methods'][] = $method::getBlock($pay);
				}
			});
			if(!isset($transaction['methods'])
				|| !is_array($transaction['methods'])
				|| !count($transaction['methods'])) {
				$transaction['methods_error'] = 'Не подключен ни один метод оплаты. Их 
				нужно подключить в скриптах и потом включить в админке.';
				echo '<p>Добавьте хотябы один метод оплаты:</p>
							<code>\Wdpro\Pay\Controller::addMethod(\Wdpro\Pay\Methods\Robokassa::class);</code>
							<p>А затем включите его в админке.</p><BR><BR>';
				throw new \Exception('Нет способа оплаты');
			}

			if (isset($transaction)) {
				// 1 Метод
				if (count($transaction['methods']) == 1) {
					// Биндим шаблон и возвращаем результат
					return wdpro_render_php(
						static::$templateSingleMethod,

						$transaction,
						array(
							'message'=>$pay->getMessage()
						)
					);
				}

				// Много
				else {
					// Биндим шаблон и возвращаем результат
					return wdpro_render_php(
						static::$templateListMethods,

						$transaction,
						array(
							'message'=>$pay->getMessage()
						)
					);
				}
			}
		}
	}


	/**
	 * Создание новой транзакации по параметрам
	 *
	 * @param array $params Параметры
	 * @return \Wdpro\Pay\Entity
	 */
	public static function createTransaction($params)
	{
		// Если задан целевой объект
		if (isset($params['target_key']))
		{
			// Данные новой транзакции
			$transaction = array(
				'target_key'=>$params['target_key'],
				'visitor_id'=>session_id(),
			);

			// Получаем целевой объект
			/** @var $target TargetInterface */
			if ($target = wdpro_object_by_key($transaction['target_key']))
			{

				// Параметры
				$targetPayParams = $target->payGetParams();

				if (!isset($targetPayParams['params']['referer']))
				{
					if (isset($params['referer']))
					{
						$targetPayParams['params']['referer'] = urlencode($params['referer']);
					}
					else
					{
						$targetPayParams['params']['referer'] = urlencode($_SERVER['HTTP_REFERER']);
					}
				}

				// Получаем параметры транзакции
				$transaction['params'] = $targetPayParams['params'];

				// Стоимость
				if (
					isset($targetPayParams['params']['dynamic']) &&
					$targetPayParams['params']['dynamic'] &&
					isset($_GET['pay']['cost']) &&
					$_GET['pay']['cost']
				)
				{
					$transaction['cost'] = $_GET['pay']['cost'];
				}
				else
				{
					$transaction['cost'] = $targetPayParams['cost'];
				}
				unset($transaction['params']['cost']);

				// Текст
				$transaction['text'] = isset($targetPayParams['text']) ? $targetPayParams['text'] : '';
			}

			// Имя метода если есть
			if (isset($params['method_name']))
			{
				$transaction['method_name'] = $params['method_name'];
			}

			// Сохраняем
			$pay = new Entity($transaction);
			$pay->save();
			return $pay;
		}
	}


	/**
	 * Возвращает кнопку для оплаты
	 *
	 * @param TargetInterface $target Целевой объект оплаты
	 * @param array|null $params Параметры
	 * @return string
	 */
	public static function getButton($target, $params=null)
	{
		// Параметры по-умолчанию
		$params = wdpro_extend(array(
			'template'=>__DIR__.'/templates/payButton.php',
			'pay_label'=>'Оплатить',
			'payed_label'=>'Оплачено',
		), $params);

		// Адрес кнопки
		if (!isset($params['http'])) {
			
			// Параметры адреса
			$httpParams = array(
				'target'=>$target->getKey(),
			);
			if (isset($params['referer']))
			{
				$httpParams['referer'] = $params['referer'];
			}

			// Адрес кнопки
			$params['http'] = static::getUrl($httpParams);
		}

		// Статус
		$params['status'] = $target->payGetStatus();
		
		return wdpro_render_php($params['template'], $params);
	}


	/**
	 * Оплата с автоматическим созданием стандартного заказа
	 * 
	 * @param $target Целевой объект оплаты
	 * @param null|array $params Параметры
	 * @return string
	 * @throws \Exception
	 */
	public static function getAutoOrderButton__DELETE($target, $params=null) {

		// Параметры по-умолчанию
		$params = wdpro_extend(array(
			'template'=>__DIR__.'/templates/payButton.php',
			'pay_label'=>'Оплатить',
			'payed_label'=>'Оплачено',
		), $params);

		// Адрес кнопки
		if (!$params['http']) {
			
			$params['http'] = 'pay?simpleOrderTarget='.$target->getKey();
		}

		return wdpro_render_php($params['template'], $params);
	}


	/**
	 * Возвращает адрес стартовой страницы оплаты
	 *
	 * @param array $params Параметры
	 * @return string
	 */
	public static function getUrl($params)
	{
		$params['target'] = urlencode($params['target']);

		return home_url().'/pay?'.wdpro_urlencode_array(array(
			'pay'=>$params
		));
	}


	/**
	 * Перебор всех методов оплаты
	 *
	 * @param callback $callback Каллбэк, который получает имена методов
	 */
	public static function eachMethods($callback)
	{
		foreach(static::$methods as $methodClassName) {
			
			$callback($methodClassName);
		}
	}


	/**
	 * Добавление метода оплаты
	 * 
	 * @param \Wdpro\Pay\Methods\MethodInterface|string $methodClassname Имя класса метода
	 */
	public static function addMethod($methodClassname) {

		static::$methods[$methodClassname::getName()] = $methodClassname;

		$methodClassname::init();
	}


	/**
	 * Возвращает класс метода оплаты
	 * 
	 * @param string $methodName Имя метода
	 * @return Methods\MethodInterface
	 */
	public static function getMethodClass($methodName) {
		return static::$methods[$methodName];
	}


	/**
	 * Установка шаблона стартового блока
	 *
	 * @param string $pathToTemplateFile Путь к файлу шаблона
	 */
	public static function setTemplateListMethods($pathToTemplateFile) {

		static::$templateListMethods = $pathToTemplateFile;
	}


	/**
	 * Возвращает транзакцию по ID
	 * 
	 * @param int|array $idOrData ID или данные транзакции
	 * @return Entity
	 * @throws \Exception
	 */
	public static function getById($idOrData) {
		
		return wdpro_object(Entity::class, $idOrData);
	}


	/**
	 * Возвращает объект транзакции по Hash ID
	 * 
	 * @param string $hashId Hash ID транзакции
	 * @return Entity
	 */
	public static function getByHashId($hashId) {
		
		if ($row = SqlTable::getRow(
			['WHERE `hash_id`=%s ORDER BY id DESC LIMIT 1', [$hashId]]
		)) {
			return static::getById($row);
		}
	}

}

return __NAMESPACE__;