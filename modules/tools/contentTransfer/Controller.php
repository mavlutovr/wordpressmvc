<?php
namespace Wdpro\Tools\ContentTransfer;

class Controller extends \Wdpro\BaseController {

	static $jobs;
	static $blockI;


	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole()
	{
		\Wdpro\Console\Menu::add([
			'roll'=>ConsoleRoll::class,
			'n'=>100,
			'icon'=>'fas fa-truck-moving',
		]);
	}


	/**
	 * Установка заданий для сбора контента
	 *
	 * @param array $jobs Задания
	 */
	public static function addJobs($jobs) {

		static::$jobs = $jobs;
		array_unshift(static::$jobs, false);
	}


	/**
	 * Инициализация переменных
	 */
	public static function initVaribles () {
		static::$blockI = wdpro_get_option('wdpro_content_transfer_current_block');
		if (!static::$blockI) static::$blockI = 0;
	}


	/**
	 * Инициализация модуля
	 */
	public static function init()
	{
		// Запуск шага
		wdpro_ajax('contentTransferRun', function () {

			static::initVaribles();

			// Переходим с 0 блока сразу на 1
			if (static::$blockI === 0) return static::nextBlock(static::$blockI);

			// Данные блока
			$block = static::getBlockDataForBrowser(static::$blockI);
			if (!$block) return static::finish();

			// Блок предыдущей задачи (чтобы определять, когда блок меняется)
			$prevBlockI = wdpro_get_option('wdpro_content_transfer_prev_block');

			// Смена блоков
			$blockChanged = $prevBlockI !== static::$blockI;
			update_option('wdpro_content_transfer_prev_block', static::$blockI);


			// Добавляем стартовые адреса, если они еще не добавлены
			/*if ($blockChanged
				&& isset($block['urls'])
				&& is_array($block['urls'])
				&& count($block['urls'])) {

				foreach ($block['urls'] as $url) {
					static::saveUrl($url);
				}
			}*/


			// Отправляем адрес в браузер
			if ($url = static::getNextUrl()) {
				return [
					'block'=>$block,
					'url'=>$url,
				];
			}

			// Переходим на следующий блок
			else {
				return static::nextBlock();
			}


		});


		// Прием результатов шага
		wdpro_ajax('contentTransferResult', function () {

			ini_set('display_errors', 'on');
			error_reporting(7);

			static::initVaribles();

			$url = $_POST['url'];
			$block = $_POST['block'];
			$result = $_POST['result'];


			// Запоминаем, что данный адрес уже проверен
			static::saveUrl($url, [
				'parsed_block'=>$block['i'],
				'parsed_time'=>time(),
			], false);


			// Адреса страниц
			if (!empty($result['urls'])) {
				foreach ($result['urls'] as $newUrl) {

					static::saveUrl($newUrl, [
						'namespace'=>$block['namespace'],
					]);
				}
			}


			// Контент страниц
			if (!empty($result['page'])) {

				// Сохраняем ссылку
				static::saveUrl($url, [
					'parsed_time'=>time(),
					'parsed_block'=>$block['i'],
					'namespace'=>$block['namespace'],
				]);

				// Родительский пост
				$urlParent = static::getUrlParent($url);

				// Родительский пост
				$postParent = null;
				$postParentId = 0;
				if ($urlParent) {
					$postParent = wdpro_get_post_by_id($urlParent['post_id']);

					if ($postParent)
						$postParentId = $postParent->id();
				}

				// Тип поста
				if (!empty($block['post_type']))
					$postType = $block['post_type'];
				else
					return [
						'error'=>'У блока № '.$block['i'].' не указан тип поста. Например, <code>\'post_type\' => \App\Catalog\Sections\Entity::getType(),</code>.',
					];


				// Данные поста
				$postData = wdpro_extend([
					'content_transfer_url_id'=>$url['id'],
					'post_title'=>$url['text'],
					'post_parent'=>$postParentId,
					'post_type'=>$block['post_type'],
					'post_name'=>wdpro_text_to_file_name($url['text']),
					'in_menu'=>1,
				], $result['page']);

				// Данные из адреса
				if (!empty($url['data'])) {
					$postData = wdpro_extend($url['data'], $postData);
				}

				// Свой скрипт
				if (!empty($block['parser']['php'])) {
					$post = require($block['parser']['php']);
					if (!isset($post)) {
						return [
							'error'=>'Файл '.$block['parser']['php'].' не вернул пост или null',
						];
					}
				}

				// Стандартный скрипт
				else {
					$post = static::savePost($postData);
				}

				if ($post)
					static::setPostToUrl($url, $post);

			}
		});


		// Удаление собранных материалов
		wdpro_ajax('contentTransferDrop', function () {
			static::initVaribles();

			ini_set('display_errors', 'on');
			error_reporting(7);

			SqlTableUrls::delete('WHERE id>0');
			update_option('wdpro_content_transfer_prev_block', '');
			update_option('wdpro_content_transfer_current_block', '');

			// Удаление постов
			foreach (static::$jobs as $block) {
				if (isset($block['post_type'])) {
					$entityClass = wdpro_get_entity_class_by_post_type($block['post_type']);
					$table = $entityClass::getSqlTableClass();
					if ($table::isField('content_transfer_url_id')) {

						$row = true;

						while ($row) {

							if (is_array($row)) {
								if ($post = wdpro_get_post_by_id($row['id']))
									$post->remove();
								wp_delete_post($row['id']);
								$table::delete([ 'id'=>$row['id'] ]);
							}

							$row = $table::getRow(
								'WHERE content_transfer_url_id>0 ORDER BY id LIMIT 1',
								'id'
							);
						}
					}
				}
			}
		});
	}


	/**
	 * Возвращает родительский Url
	 *
	 * @param array $url текущий Url
	 * @return array
	 */
	public static function getUrlParent($url) {
		if (!empty($url['parent_id'])) {
			return SqlTableUrls::getRow([
				'WHERE id=%d LIMIT 1',
				[ $url['parent_id'] ]
			]);
		}
	}


	/**
	 * Сохраняет пост
	 *
	 * @param array $data Данные поста
	 * @return \Wdpro\BasePage
	 * @throws \Wdpro\EntityException
	 */
	public static function savePost($data) {

		if (!isset($data['post_status']))
			$data['post_status'] = 'publish';

		$post = wdpro_create_post($data);

		$form = $post->getConsoleForm();
		$post->mergeData(
			$form->getData(
				$post->getData()
			)
		);
		$post->save();


		// Порядковый номер
		$post->nextSortingToData()->save();

		/*$table = $post::getSqlTableClass();
		if ($table::isField('menu_order')) {
			if ($prev = $table::getRow([
				'WHERE post_parent=%d ORDER BY menu_order DESC LIMIT 1',
				[ $post->getData('post_parent') ]
			])) {
				$menu_order = ceil($prev['menu_order'] / 10) * 10 + 10;
			}
			else {
				$menu_order = 10;
			}

			$post->mergeData([ 'menu_order' => $menu_order ])->save();
		}*/

		return $post;
	}


	/**
	 * Запоминает в url ID созданного поста
	 *
	 * @param string $url URL адрес
	 * @param \Wdpro\BasePage $post Пост
	 */
	public static function setPostToUrl($url, $post) {
		SqlTableUrls::update([ 'post_id'=>$post->id() ], [ 'id'=> $url['id'] ]);
	}


	/**
	 * Сохраняет url для парсинга
	 *
	 * @param array|string $data Данные адреса или сам адрес
	 * @param array|null $additionalData Дополнительные данные
	 * @return bool
	 * @throws \Exception
	 */
	public static function saveUrl($data, $additionalData=null, $test=false) {

		if (!is_array($data)) $data = [ 'url' => $data ];

		if ($test) {
			echo PHP_EOL.'$data: ';
			print_r($data);
			echo PHP_EOL.'$additionalData: ';
			print_r($additionalData);
		}

		if ($additionalData)
			$data = wdpro_extend($data, $additionalData);

		if ($test) {
			echo PHP_EOL.'EXTENDED DATA: ';
			print_r($data);
		}

		// Отмена сохранения (например, когда это стартовый адрес блока)
		if (isset($data['save']) && (!$data['save'] || $data['save'] === 'false'))
			return false;

		// Блок
//		if (!isset($data['parsed_block']))
//			$data['parsed_block'] = static::$blockI;


		if ($test) {
			echo PHP_EOL.'Data: ';
			print_r($data);
		}

		// Обновление существующего адреса
		$id = null;
		if (!empty($data['id'])) {
			$id = $data['id'];
		}
		else {

			$block = static::getBlockData();

			if ($test) {
				echo PHP_EOL . 'Block: ';
				print_r($block);
			}

			// Когда указана ссылка
			if ($data['url']) {
				$where = [
					'WHERE namespace=%s AND url=%s',
					[ $block['namespace'], $data['url'] ]
				];
			}

			// Когда указан только текст ссылки (когда выбранная кнопка на сайте без ссылки)
			else {
				unset($data['url']);
				$where = [
					'WHERE namespace=%s AND text=%s',
					[ $block['namespace'], $data['text'] ]
				];
			}

			if ($test) {
				echo PHP_EOL . 'Where: ';
				print_r($where);
			}

			if ($current = SqlTableUrls::getRow($where)) {
				if ($test) {
					echo PHP_EOL . 'Current: ';
					print_r($current);
				}
				$id = $current['id'];
				//$data = wdpro_extend($current, $data);
				$data['data'] = wdpro_extend($current['data'], $data['data']);
			}
		}

		if ($id) {
			if ($test) {
				echo PHP_EOL . 'UpdateData: ';
				print_r($data);
			}
			unset($data['parent_id']); // Чтобы пункт меню не передвигался, в unipack из-за этого была путаница, там почему-то кнопки появлялись снова в подразделах
			SqlTableUrls::update($data, [ 'id' => $id ]);
		}

		else {
			$id = SqlTableUrls::insert($data);
		}

		// Дочерние
		if ($data['children']) {
			foreach ($data['children'] as $child) {
				$child['parent_id'] = $id;

				static::saveUrl($child, $additionalData);
			}
		}
	}


	/**
	 * Возвращает адрес для парсинга
	 *
	 * @return array
	 */
	public static function getNextUrl() {

		$block = static::getBlockData();

		if ($row = SqlTableUrls::getRow([
			'WHERE namespace=%s AND parsed_block<%d ORDER BY parent_id, id LIMIT 1',
			[ $block['namespace'], static::$blockI ]
		])) {

			return $row;
		}
	}


	/**
	 * Переход к следующему блоку
	 * 
	 * @param $currentBlock
	 * @return array
	 */
	public static function nextBlock() {
		static::$blockI ++;

		if ($block = static::getBlockDataForBrowser()) {
			update_option('wdpro_content_transfer_current_block', static::$blockI);

			$block['i'] = static::$blockI;

			$ret = [
				'block'=>$block,
			];

			if ($url = static::getNextUrl()) {
				$ret['url'] = $url;
			}

			return $ret;
		}

		return static::finish();
	}


	/**
	 * Возвращает данные блока
	 *
	 * @param int|null $blockI Номер блока
	 * @return array
	 */
	public static function getBlockData ($blockI=null) {
		if ($blockI === null)
			$blockI = static::$blockI;

		if (isset(static::$jobs[$blockI])) {
			$data = static::$jobs[$blockI];

			$data['i'] = $blockI;

			if (!isset($data['name']))
				$data['name'] = 'block_'.$blockI;

			if (!isset($data['title']))
				$data['title'] = 'Блок обработки № '.$blockI;

			return $data;
		}
	}


	/**
	 * Возвращает данные блока для браузера
	 *
	 * @param int|null $blockI Номер блока
	 * @return array
	 */
	public static function getBlockDataForBrowser($blockI=null) {
		$block = static::getBlockData($blockI);

		if (isset($block['parser']['js'])) {
			$block['script'] = file_get_contents($block['parser']['js']);
		}

		return $block;
	}


	/**
	 * Завершает работу
	 *
	 * @return array
	 */
	public static function finish () {
		update_option('wdpro_content_transfer_prev_block', '');
		update_option('wdpro_content_transfer_current_block', '');

		return [
			'finish'=>true,
		];
	}

}


return __NAMESPACE__;