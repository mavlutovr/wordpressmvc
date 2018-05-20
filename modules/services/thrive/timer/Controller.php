<?php
namespace Wdpro\Services\Thrive\Timer;

class Controller extends \Wdpro\BaseController {

	const META_NAME_FOR_STATUS = 'tve_ult_status';
	const CAMPAIGN_STATUS_RUNNING = 'running';
	const META_NAME_FOR_CAMPAIGN_SETTINGS = 'tve_ult_campaign_settings';

	// Список состояний для таймеров на других доменах
	protected static $domainsTimersStatus = [];

	// Таймеры на этой странице
	// Это чтобы знать, какой таймер проверять, напрмер, на странице оплаты
	protected static $timersOnCurrentPage = [];


	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite() {

		// Чтобы отсчет таймера запускался до обработки меток
		// А то когда при обработке меток, то кукисы не устанавливаются,
		// в момент обработки меток часть документа уже отправлена в браузер
		add_action('wp', function () {

			$post = get_post();
			$content = $post->post_content;

			if (strstr($content, '[timer_price')) {
				$arr = [];

				if (preg_match(
					'~\[timer[^\].]*thrive_id=("?)([0-9]+)~',
					$content,
					$arr
				)) {


					$thriveId = $arr[2];

					static::$timersOnCurrentPage[$thriveId] = $thriveId;

					// Запуск начала отсчета
					static::thriveAlive($thriveId);
				}
			}
		});



		// Default
		wdpro_default_page('services-thrive-timer-get_timer', function () {
			return require __DIR__.'/default/services-thrive-timer-get_timer.php';
		});
		wdpro_default_file(
			__DIR__.'/default/services-thrive-timer-get_timer-template.php',
			WDPRO_TEMPLATE_PATH.'services-thrive-timer-get_timer-template.php'
		);


		// Отправляет инфу в другой домен
		add_action('wp', function () {

			$post = get_post();
			if ($post->post_name == 'services-thrive-timer-get_timer' &&
				$_GET['action'] == 'getStatus') {

				$data = $_GET;



				$accessDomains = get_option('services-thrive-timer-access_domains');
				$accessDomainsArr = explode("
", $accessDomains);
				$domains = '';
				foreach($accessDomainsArr as $domain) {
					if (!empty($domain)) {
						$domain = trim($domain);
						if (!strstr($domain, '://')) {
							$domain = 'http://'.$domain;
						}
						if ($domains) $domains .= ' ';
						$domains .= $domain;
					}
				}

				if ($domains) {
					header('Access-Control-Allow-Origin: '.$domains);
				}
				header('Access-Control-Allow-Credentials: true');

				$data = wdpro_extend([
					'design'=>1,
				], $data);

				if (empty($data['thriveId'])) return false;


				// Запуск таймера, если он еще на запущен
				if ($data['start']) {
					static::thriveStart($data['thriveId']);
				}


				// Загрузка Информации о таймере
				if (!empty($data['action']) && $data['action'] == 'getStatus') {

					$code = wdpro_json_encode([
						'alive'=>static::thriveAlive($data['thriveId'])
							? 'alive' : 'finished',
					]);

					echo $data['callback'].'('.$code.')';

					//echo $code;
					exit();
				}
			}
		});


		// Страница отправки таймера в другой домен
		wdpro_on_uri_content('services-thrive-timer-get_timer', function ($content) {

			$data = $_GET;

			$data = wdpro_extend([
				'design'=>1,
			], $data);

			if (empty($data['thriveId'])) return false;

			if (!empty($data['start'])) {
				static::thriveStart($data['thriveId']);
			}

			$html = '<span>[tu_countdown id='
				.$data['thriveId']
				.' 
design='
				.$data['design']
				.'][/tu_countdown]</span>';

			//$html = do_shortcode($html);

			return $html;
		});


		// Добавляет в параметры src для загрузки таймера и его данных
		$getSrc = function (&$params) {
			if (!isset($params['src'])) {

				$params = wdpro_extend([
					'design'=>1,
				], $params);

				$src = $params['domain'];
				if (!strstr($src, '://')) {
					$src = 'https://'.$src;
				}
				if (!preg_match('~/$~', $src)) {
					$src .= '/';
				}

				if (!strstr($src, 'admin-ajax.php')) {
					$src .= 'services-thrive-timer-get_timer';
				}



				$params['src'] = $src;
			}
		};


		// Проверяет не закончился ли еще таймер для данного пользователя
		$getAlive = function ($params) use (&$getSrc) {
			if (!empty($params['domain']))  {
				$getSrc($params);

				$id = $params['thrive_id'];
				$domain = $params['domain'];

				if (!isset(static::$domainsTimersStatus[$domain][$id])) {
					$params['src'] .= '?' . wdpro_urlencode_array([
							'thriveId' => $params['thrive_id'],
							'action'=>'getStatus',
						]);
					$json = file_get_contents($params['src']);
					static::$domainsTimersStatus[$domain][$id] = wdpro_json_decode($json);
				}

				$alive = static::$domainsTimersStatus[$domain][$id]['alive'];
			}

			else {
				$alive = static::thriveAlive($params['thrive_id']);
			}

			if ($alive) {
				return $content;
			}
			else {
				return '';
			}
		};


		// Сам таймер
		add_shortcode('timer', function ($params) use (&$getSrc) {

			if (empty($params['src'])) {
				if (empty($params['domain'])) {
					$params['domain'] = $_SERVER['HTTP_HOST'];
				}
				if (!empty($params['domain'])) {

					$getSrc($params);
					$params['src'] .= '?' . wdpro_urlencode_array([
							'thriveId' => $params['thrive_id'],
							'design' => $params['design'],
						]);
					if ($params['start']) {
						$params['src'] .= '&start=1';
					}
				}
			}

			if (!empty($params['src'])) {
				return wdpro_render_php(
					__DIR__.'/templates/timer_domain.php',
					$params
				);
			}

			return '';
		});


		// Запуск таймера
		add_shortcode('timer_start', function ($params) {

			if (!empty($params['thrive_id'])) {
				static::thriveStart($params['thrive_id']);
			}
			return '';
		});


		// Когда таймер есть
		add_shortcode('timer_yes', function ($params, $content) use (&$getSrc) {

			// Этот домен
			if (empty($params['domain'])) {
				if ($params['start']) {
					static::thriveStart($params['thrive_id']);
				}
				if (static::thriveAlive($params['thrive_id'])) {
					$content = do_shortcode($content);
					return $content;
				}
				else {
					return '';
				}
			}

			// Другой домен
			else {

				$getSrc($params);

				$content = do_shortcode($content);

				return '<span class="js-thrive-timer-visible" 
				data-thrive-id="'.$params['thrive_id'].'"
				data-show="alive" 
				data-start="'.$params['start'].'"
				data-domain="'.$params['domain'].'"
				data-src="'.$params['src'].'"
				style="display: none;">'
				.$content
				.'</span>';
			}

		});


		// Когда таймера нет
		add_shortcode('timer_no', function ($params, $content) use (&$getSrc) {

			// Этот домен
			if (empty($params['domain'])) {
				if ($params['start']) {
					static::thriveStart($params['thrive_id']);
				}
				if (!static::thriveAlive($params['thrive_id'])) {
					$content = do_shortcode($content);
					return $content;
				}
				else {
					return '';
				}
			}

			// Другой домен
			else {

				return '<span class="js-thrive-timer-visible" 
				data-show="finished" 
				data-thrive-id="'.$params['thrive_id'].'"
				data-start="'.$params['start'].'"
				data-domain="'.$params['domain'].'"
				data-src="'.$params['src'].'"
				style="display: none;">'
				.$content
				.'</span>';
			}
		});


		// Создание таймера для пользователя по шорткоду
		add_shortcode('timer_price', function ($params) {

			if ($params['price']) {

				// Таймер Thrive
				if (isset($params['thrive_id'])) {

					// Запуск таймера
					if (isset($params['start'])) {
						static::thriveStart($params['thrive_id']);
					}

					// Если таймер запущен
					if (static::thriveRunning($params['thrive_id'])) {

						// Если нету таймера
						if (!static::getThriveTimerForCurrentVisitor
						($params['thrive_id'])) {

							// Запуск начала отсчета
							//static::thriveAlive($params['thrive_id']);

							// Добавляем информацию о таймере
							SqlTable::insert([
								'visitor_id' => wdpro_visitor_session_id(),
								'price'      => $params['price'],
								'demo'       => 0,
								'thrive_id'=>$params['thrive_id'],
								'started'=>time(),
							]);
						}
					}
				}

				// Обычный таймер
				else {
					// Удаляем таймеры с большей ценой
					$visitorId = wdpro_visitor_session_id();
					SqlTable::delete([
						'WHERE `visitor_id`=%s AND `price`>%d',
						[$visitorId, $params['price']]
					]);

					// Если нету таймера
					if (!static::getTimerForCurrentVisitor()) {

						$currentTimer = time();
						if ($params['demo']) {
							$finish = $currentTimer + 15;
						}
						else {

							$plusTimer = 0;
							if ($params['hours']) {
								$plusTimer += $params['hours'] * 60 * 60;
							}
							if ($params['days']) {
								$plusTimer += $params['days'] * 24 * 60 * 60;
							}
							$finish = $currentTimer + $plusTimer;
						}

						SqlTable::insert([
							'started'    => $currentTimer,
							'finish'     => $finish,
							'visitor_id' => wdpro_visitor_session_id(),
							'price'      => $params['price'],
							'demo'       => $params['demo'] ? 1 : 0,
							'thrive_id'=>0,
						]);
					}
				}
			}
		});
	}


	/**
	 * Принудительный запуск таймера
	 *
	 * @param number $id ID Таймера
	 */
	public static function thriveStart($id) {

		$settings = get_post_meta( $id, static::META_NAME_FOR_CAMPAIGN_SETTINGS, true );

		if (!empty($settings['trigger']['ids'])
			&& isset($settings['trigger']['type'])
			&& $settings['trigger']['type'] == 'url'
		) {

			if (is_numeric($settings['trigger']['ids'])) {
				static::thriveAlive($id, $settings['trigger']['ids']);
			}
		}
	}


	/**
	 * Проверяет, что время таймера еще не закончилось
	 *
	 * @param int $id ID таймера
	 * @param null|int $postId ID Поста (чтобы запускать таймер, когда открывается
	 * страница, которая в настройках таймера указана как стартовая, что при открытии
	 * этой страницы надо запускать таймер, если он еще не запущен)
	 * @return bool
	 */
	public static function thriveAlive($id, $postId=null) {

		global $tve_ult_frontend;

		if (!$postId) {
			$postId = get_the_ID();
			if (!$postId) $postId = null;
		}

		$arr = [
			'action'=>'tve_ult_ajax_load',
			'campaign_ids'=>[$id],
			'post_id'=>$postId,
			'is_sungular'=>true,
			'shortcode_campaign_ids'=>[$id],
		];

		foreach($arr as $key=>$value) {
			$_REQUEST[$key] = $value;
			$_GET[$key] = $value;
			$_POST[$key] = $value;
		}
		$result = $tve_ult_frontend->ajax_load(array(), $arr);

		if (isset($result[$id]['timer_components'])) {
			return true;
		}

		return false;
	}


	/**
	 * Проверяет, запущен ли заданный таймер
	 *
	 * @param int $id ID таймера
	 * @return bool
	 */
	public static function thriveRunning($id) {


		/**
		 * if not post or page do not render shortcode and do not save the campaign globally
		 * cos we don't want to render the shortcode in lists
		 */
		if ( ! is_single() && ! is_page() ) {
			return false;
		}

		/**
		 * check if the campaigns is running
		 */
		$status = get_post_meta( $id, static::META_NAME_FOR_STATUS, true );

		if ( $status !== static::CAMPAIGN_STATUS_RUNNING ) {
			return false;
		}

		return true;
	}


	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки: https://developer.wordpress.org/resource/dashicons/#forms
	 */
	public static function initConsole() {

		\Wdpro\Console\Menu::addSettings('Таймер', function ($form) {

			/** @var \Wdpro\Form\Form $form */
			$form->add([
				'name'=>'services-thrive-timer-access_domains',
				'type'=>$form::TEXT,
				'top'=>'Список доменов, для которых разрешено получать информацию о 
				таймерах с этого сайта',
			]);
			$form->add('submitSave');

			return $form;
		});

		// Подсказки для текста поля текста страницы (про шорткоды)
		add_filter('wdpro_text_help', function ($text) {

			$text .= '<p><b>Снижать цену на указанное время</b><BR>
				[timer price="СНИЖЕННАЯ_СУММА" hours="ЧАСЫ" days="ДНИ" 
				demo="1__СНИЗИТЬ_ТОЛЬКО_НА_15_СЕК"]</p>';

			return $text;
		});
	}


	/**
	 * Возвращает таймер, если он есть
	 *
	 * @return Entity|void
	 * @throws \Exception
	 */
	public static function getTimerForCurrentVisitor() {

		// Если для пользователя еще нету таймера
		$visitorId = wdpro_visitor_session_id();
		if ($data = SqlTable::getRow(['WHERE `visitor_id`=%s', [$visitorId]])) {

			return wdpro_object(Entity::class, $data);
		}
	}


	/**
	 * Возвращает Thrive таймер, если он есть
	 *
	 * @param null|int|array $thriveId ID Thrive таймера или массив айдишников
	 * @return Entity|void
	 * @throws \Exception
	 */
	public static function getThriveTimerForCurrentVisitor($thriveId=null) {

		// Если для пользователя еще нету таймера
		$visitorId = wdpro_visitor_session_id();

		$whereData = [$visitorId];

		$and = ' AND ';

		if ($thriveId == 'currentPage') {
			$thriveId = static::$timersOnCurrentPage;
		}

		if (is_array($thriveId)) {
			$and .= '(0 ';
			foreach($thriveId as $id) {
				$and .= ' OR `thrive_id`=%d';
				$whereData[] = $id;
			}
			$and .= ')';
		}
		else if ($thriveId) {
			$and .= ' `thrive_id`=%d';
			$whereData[] = $thriveId;
		}
		else {
			$and .= ' `thrive_id`>0';
		}

		$where = SqlTable::prepare([
			'WHERE `visitor_id`=%s'.$and.' ORDER BY id DESC',
			$whereData
		]);

		if ($data = SqlTable::getRow($where)) {

			return wdpro_object(Entity::class, $data);
		}
	}


	/**
	 * Меняет цену в зависимости от таймера
	 * 
	 * @param int $price Начальная цена
	 * @return int
	 */
	public static function getPriceForCurrentVisitor($price) {
		
		// Когда всегда использовать цену таймера
		/*if (get_option('app_timer_use_timer_price_always')) {
			return min($price, get_option('app_timer_price'));
		}*/

		if ($timer = static::getThriveTimerForCurrentVisitor('currentPage')) {

			if (static::thriveAlive($timer->data['thrive_id'])) {
				$price = min($price, $timer->data['price']);
			}
		}

		//  Это обычный таймер (не thrive)
		else if ($timer = static::getTimerForCurrentVisitor()) {
			if ($timer->data['finish'] >= time()) {
				$price = min($price, $timer->data['price']);
			}
		}
		
		return $price;
	}


	/**
	 * Возвращает таймеры на текущей странице
	 *
	 * Они могут пригодиться например на странице оплаты, когда надо определить, какой
	 * именно таймер действует на этой странице оплаты и исходя из этого уже показывать
	 * либо сниженную цену, либо большую
	 *
	 * @return array
	 */
	public static function getTimersOnCurrentPage() {
		return static::$timersOnCurrentPage;
	}
}

return __NAMESPACE__;