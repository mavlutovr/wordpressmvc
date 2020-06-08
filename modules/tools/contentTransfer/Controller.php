<?php
namespace Wdpro\Tools\ContentTransfer;

use Wdpro\Exception;

class Controller extends \Wdpro\BaseController {

	static $jobs;
	static $blockI;
	const CONTENT_IMAGES_DIR_PATH = WDPRO_UPLOAD_IMAGES_PATH.'contentTransfer';
	const CONTENT_IMAGES_DIR_URL = WDPRO_UPLOAD_IMAGES_URL.'contentTransfer';


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

			wdpro_strip_slashes_in_array($url);
			wdpro_strip_slashes_in_array($block);
			wdpro_strip_slashes_in_array($result);


			// Меню
			if (!empty($result['menu'])) {
				foreach ($result['menu'] as $newUrl) {

					static::saveUrl($newUrl, [
						'block'=>$block,
					]);
				}
			}

			// Подменю
			if (!empty($result['submenu']) && is_array($result['submenu'])) {

				$url['children'] = $result['submenu'];
			}


			// Сохранение запрещено
			if (isset($url['save']) && (!$url['save'] || $url['save'] === 'false')) {
				
				// Сохраняем только подменю
				if (isset($url['children']) && is_array($url['children'])) {
					foreach ($url['children'] as $child) {
						static::saveUrl($child, [
							'block'=>$block,
						]);
					}
				}
			}

			// Можно сохранить
			else {

				// Запоминаем, что данный адрес уже проверен
				static::saveUrl($url, [
					'parsed_block'=>$block['i'],
					'parsed_time'=>time(),
					'block'=>$block,
				]);
			}


			// Контент страниц
			if (!empty($result['page'])) {

				// Сохраняем ссылку
				static::saveUrl($url, [
					'parsed_time'=>time(),
					'parsed_block'=>$block['i'],
					'block'=>$block,
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


				// Данные поста
				$postData = wdpro_extend([
					'content_transfer_url_id'=>$url['id'],
					'post_title'=>$url['text'],
					'post_parent'=>$postParentId,
					'post_name'=>wdpro_text_to_file_name($url['text']),
					'in_menu'=>1,
				], $result['page']);


				// Данные из адреса
				if (!empty($url['data'])) {
					$postData = wdpro_extend($url['data'], $postData);
				}

				// Тип поста
				if (empty($postData['post_type']) && !empty($block['post_type'])) {
					$postData['post_type'] = $block['post_type'];
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
					if (empty($postData['post_type'])) {
						return [
							'error'=>'У блока № '.$block['i'].' не указан тип поста. Например, <code>\'post_type\' => \App\Catalog\Sections\Entity::getType(),</code>.',
						];
					}
					else {
						if (is_array($postData['post_type'])) {
							return [
								'error'=>'Когда в post_type указан массив постов, необходимо вручную выбирать тип поста в скрипте, указанном в <code>\'parser\' => [ \'php\' => \'Пть к скрипту\' ]</code>',
							];
						}
					}

					// Загрузка изображений
					$postData['post_content'] = static::loadContentImages(
						$postData['post_content'],
						$url['url']
					);

					$post = static::savePost($postData);
				}

				if ($post)
					static::setPostToUrl($url, $post);

			}


			// Свой скрипт
			else if (!empty($block['parser']['php'])) {
				require($block['parser']['php']);
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

					$postsType = $block['post_type'];
					if (!is_array($postsType)) $postsType = [ $postsType ];

					foreach ($postsType as $postType) {
						$entityClass = wdpro_get_entity_class_by_post_type($postType);
						$table = $entityClass::getSqlTableClass();
						if ($table::isField('content_transfer_url_id')) {

							$row = true;

							while ($row) {

								if (is_array($row)) {
									if ($post = wdpro_get_post_by_id($row['id']))
										$post->remove();
									wp_delete_post($row['id'], true);
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
			}

			// Удаление картинок
			wdpro_rmdir(static::CONTENT_IMAGES_DIR_PATH);
		});


		// Редиректы
		wdpro_ajax('contentTransferRedirects', function () {

			$htaccess = '';

			if ($sel = SqlTableUrls::select(
				'WHERE post_id>0 ORDER BY id',
				'url, post_id'
			)) {
				foreach ($sel as $row) {

					$fromParsed = parse_url($row['url']);
					$from = $fromParsed['path'];
					if ($fromParsed['query'])
						$from .= '?'.$fromParsed['query'];

					$post = wdpro_get_post_by_id($row['post_id']);
					$to = null;
					if ($post->loaded()) {
						$to = $post->getUrl();
					}

					if ($from && $to) {
						$htaccess .= 'Redirect 301 '
							.$from
							.' '
							.$to;
						$htaccess .= PHP_EOL;
					}
				}
			}

			wdpro_add_to_htaccess($htaccess, [
				'name'=>'TransferContent',
				'info'=>'Смена адресов после переноса материалов на новый сайт.',
			]);
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
	 * Загружает картинку, и возвращает ее новый url адрес
	 *
	 * Если картинка с этого же сайта, с которого контент
	 *
	 * @param string $src Адрес картинки
	 * @param string $sourcePageUrl Адрес страницы, на которой расположен тег img картинки
	 * @return string
	 */
	public static function loadImageAndGetNewSrc($src, $sourcePageUrl=null) {

		$srcOb = parse_url($src);
		if ($sourcePageUrl)
			$sourcePageOb = parse_url($sourcePageUrl);

		// Относительный адрес
		if (empty($srcOb['host'])) {

			// От начала
			if (preg_match('~^/~', $src)) {
				$rootDir = $sourcePageOb['scheme'].'://'.$sourcePageOb['host'];
			}

			// От страницы
			else {
				$rootDir = $sourcePageOb['scheme'].'://'.$sourcePageOb['host'].$sourcePageOb['path'];
				$rootDir = preg_replace('~(/[^/]*)$~', '/', $rootDir);
			}

			$src = $rootDir.$src;
			$srcOb = parse_url($src);
		}


		// Если картинка с этого же сайта
		if (!$sourcePageUrl || $srcOb['host'] === $sourcePageOb['host']) {

			//$fileName = basename($src);
			$fileName = pathinfo($src);
			$subdir = preg_replace('~[^/]*$~', '', $srcOb['path']);

			wdpro_upload_dir_create(static::CONTENT_IMAGES_DIR_PATH);
			if (!is_dir(static::CONTENT_IMAGES_DIR_PATH.$subdir))
				mkdir(static::CONTENT_IMAGES_DIR_PATH.$subdir, 0777, true);

			$path = static::CONTENT_IMAGES_DIR_PATH.$subdir
				.$fileName['filename'].'.'.$fileName['extension'];
			$newSrc = static::CONTENT_IMAGES_DIR_URL.$subdir
				.$fileName['filename'].'.'.$fileName['extension'];




			/*$n = 1;
			$path = static::CONTENT_IMAGES_DIR_PATH
				.$fileName['filename'].'.'.$fileName['extension'];
			$newSrc = static::CONTENT_IMAGES_DIR_URL
				.$fileName['filename'].'.'.$fileName['extension'];
			while(is_file($path)) {
				$n ++;
				$path = static::CONTENT_IMAGES_DIR_PATH
					.$fileName['filename'].'_'.$n.'.'.$fileName['extension'];
				$newSrc = static::CONTENT_IMAGES_DIR_URL
					.$fileName['filename'].'_'.$n.'.'.$fileName['extension'];
			}*/

//			echo PHP_EOL.'$sourcePageUrl: '.$sourcePageUrl.PHP_EOL;
//			echo PHP_EOL.'$rootDir: '.$rootDir.PHP_EOL;

//			echo PHP_EOL.'SRC: '.$src.PHP_EOL;
//			echo PHP_EOL.'NEWSRC: '.$newSrc.PHP_EOL;

			if (!is_file($path)) {
				@$imageData = file_get_contents($src);
				@file_put_contents($path, $imageData);

				/*$ch = curl_init($src);
				$fp = fopen($path, 'wb');
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);*/
			}
		}

		return $newSrc;
	}


	/**
	 * Загружает картинки в контенте и меняет их адреса на новые
	 *
	 * @param string $content Контент (html код)
	 * @param string $sourcePageUrl Адерс страницы, с которой взят html код
	 * @return string
	 */
	public static function loadContentImages($content, $sourcePageUrl) {

		// Картинки
		$content = preg_replace_callback(

			'/ src=([^ >]+)/',

			function ($arr) use (&$sourcePageUrl) {

				$src = $arr[1];
				$src = str_replace('"', '', $src);
				$src = str_replace("'", '', $src);
				$src = str_replace("\\", '', $src);

				$src = static::loadImageAndGetNewSrc($src, $sourcePageUrl);

				return ' src="'.$src.'"';
			},
			$content
		);


		// Ссылки
		$content = preg_replace_callback(
			'/ href=([^ >]+)/',

			function ($arr) use (&$sourcePageUrl) {

				$src = $arr[1];
				$src = str_replace('"', '', $src);
				$src = str_replace("'", '', $src);
				$src = str_replace("\\", '', $src);

				// Картинка
				if (preg_match('~\.(png|jpg|jpeg|gif|webp)$~', $src)) {

					$src = static::loadImageAndGetNewSrc($src, $sourcePageUrl);
				}

				return ' href="'.$src.'"';
			},
			$content
		);


		return $content;
	}


	/**
	 * Сохраняет пост
	 *
	 * @param array $data Данные поста
	 * @return \Wdpro\BasePage
	 * @throws \Wdpro\EntityException
	 */
	public static function savePost($data) {

		if (!$data['post_type'])
			throw new \Exception('Для поста '.$data['post_title'].' не указан тип поста');

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

		if ($additionalData){
			$data = wdpro_extend($data, $additionalData);
			if (empty($data['namespace']) && !empty( $additionalData['block'] )) {
				$data['namespace'] = $additionalData['block']['namespace'];
			}
		}

		if ($test) {
			echo PHP_EOL.'EXTENDED DATA: ';
			print_r($data);
		}

		if (isset($data['test']))
			$data['test'] = substr($data['text'], 0, 254);

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
			unset($data['parent_id']); // Чтобы пункт меню не передвигался, в unipack из-за этого была путаница, там почему-то кнопки появлялись снова в подразделах
			SqlTableUrls::update($data, [ 'id' => $id ]);
			if ($test) {
				echo PHP_EOL . 'UpdateData: ';
				print_r($data);
			}
		}

		else {
			$id = SqlTableUrls::insert($data);
		}

		// Дочерние
		if ($data['children']) {
			SqlTableUrls::update([ 'has_children' => 1 ], [ 'id' => $id ]);

			foreach ($data['children'] as $child) {
				$child['parent_id'] = $id;

				unset($additionalData['parsed_block']);
				unset($additionalData['parsed_time']);
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

		if (!empty( $block['source_namespace'] )) {
			$namespace = $block['source_namespace'];
		}
		else if (!empty( $block['namespace'] )) {
			$namespace = $block['namespace'];
		}

		if ($row = SqlTableUrls::getRow([
			'WHERE namespace=%s AND parsed_block<%d ORDER BY parent_id, menu_order, id LIMIT 1',
			[ $namespace, static::$blockI ]
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