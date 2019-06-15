<?php
namespace Wdpro\Page;



use Wdpro\Exception;
use Wdpro\Templates;

class Controller extends \Wdpro\BaseController {


	/**
	 * Инициализация модуля
	 */
	public static function init() {

		\Wdpro\Autoload::add('Wdpro\Page', __DIR__);
		//Other\SqlTable::init();
		Other\Entity::init();

		// Подготовка таблицы постов
		\Wdpro\Page\SqlTable::init();

		if (is_admin())
		{
			// Редирект страницы после сохранения поста на страницу редактирования поста
			add_filter('redirect_post_location', function ($location)
			{
				$url = parse_url($location);
				$action = null; $post = null; $message = '';
				parse_str($url['query']);

				if ($action == 'edit')
				{
					//$entity = wdpro_object_by_post_id($post);


					return admin_url().'post.php?post='.$post
					.'&action=edit&message='.$message;

					// Сейчас:  post.php?post=252&action=edit&message=6
					// Надо:    post.php?post=42&action=edit
					// post-new.php?post_type=menu_catalog&sectionId=40

					// http://localhost/hozaika-severa.ru_wp/wordpress/wp-admin/
				}

				return $location;
			});


			// Корректировка адресов страниц
			add_filter(
				'post_type_link',
				function ($postLink, $post=0)
				{
					if (!wdpro_is_post_type($post->post_type)
						|| get_option('permalink_structure') != '/%postname%/')
						return $postLink;

					global $wp_rewrite;
					if ( is_wp_error( $post ) )
						return $post;

					$newlink = $wp_rewrite->get_extra_permastruct($post->post_type);
					$newlink = str_replace('/'.$post->post_type.'/', '', $newlink);
					$newlink = user_trailingslashit($newlink);
					$newlink = str_replace('%'.$post->post_type.'%', '%postname%', $newlink);
					$newlink = home_url($newlink);
					return $newlink;
				},
				1, 3
			);
			
			// Удаление лишних кнопок меню
			add_action('admin_menu', function ($a) {
				
				remove_menu_page('edit.php?post_type=page');
			});
		}


		// Загрузка страниц, добавленных через Wdpro плагин
		add_action(
			'pre_get_posts',
			function ($query)
			{
				// Чтобы главная открывалась
				if(
					(!isset($query->query_vars['post_type']) || '' == $query->query_vars['post_type'])
					&& isset($query->query_vars['page_id'])
					&& 0 != $query->query_vars['page_id'])
					$query->query_vars['post_type'] = array( 'page', Other\Entity::getType() );
				
				// Only noop the main query
				if ( ! $query->is_main_query() )
					return;

				// Only noop our very specific rewrite rule match
				if ( 2 != count( $query->query ) || ! isset( $query->query['page'] ) ) {
					return;
				}

				// 'name' will be set if post permalinks are just post_name, otherwise the page rule will match
				if ( ! empty( $query->query['name'] ) ) {

					$query->set(
						'post_type',
						array_merge(array('page', 'post'), wdpro_get_post_types() )
					);
				}
			}
		);
		
		
		// 404 ошибка
		add_action('template_redirect', function () {

			global $wp_query, $post;
			
			if ($wp_query->is_404) {

				if ( isset($wp_query->query['name'])
				     && has_action('wdpro_pages_default:' . $wp_query->query['name']) ) {
					do_action('wdpro_pages_default:' . $wp_query->query['name']);
				}
				
				else {
					do_action('wdpro_pages_default:error404');
					/** @var \WP_Query $wp_query */
					$wp_query->set_404();
					$page = wdpro_get_post_by_name('error404');
					$GLOBALS['post'] = get_post($page->id());
					$wp_query->have_posts();
					global $post;
					$post = $GLOBALS['post'];
					setup_postdata($GLOBALS['post']);
				}
			}

			/*print_r($wp_query);
			print_r($post);
			exit();*/
		});


		// Страницы по-умолчанию
		// 404
		\Wdpro\Page\Controller::defaultPage('error404', function () {
			return require __DIR__.'/default/page_404.php';
		});
		
		
		/*add_filter('pre_post_link', function ($link) {
			
			echo("link: ".$link);
			exit();
		});*/
	}


	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite() {
		
		// Подменю по-умолчанию
		wdpro_default_file(__DIR__.'/../install/default/app_theme/submenu_standart.php',
			WDPRO_TEMPLATE_PATH.'submenu_standart.php');

		// Подменю
		add_shortcode('submenu', function () {
			
			$post = get_post();
			
			return \Wdpro\Site\Menu::getHtml(array(
				'post_parent'=>$post->ID,
				'type'=>$post->post_type,
				'template'=>WDPRO_TEMPLATE_PATH.'submenu_standart.php',
				'entity'=>wdpro_get_entity_class_by_post_type($post->post_type),
			));
		});
		

		// Текст из другой страницы
		add_shortcode('page_text', function ($params) {
			
			if (isset($params['id']) && $params['id']) {
				
				/** @var \WP_Post $post */
				$post = get_post($params['id']);
				
				return do_shortcode($post->post_content);
			}
		});


		// Инициализация страницы до отправки html кода в браузер
		wdpro_on_page_init(function ($page) {
			/** @var $page \App\BasePage */

			if ($page->isHome() && wdpro_current_url() !== wdpro_home_url_with_lang()) {
				wdpro_location(wdpro_home_url_with_lang());
			}

			if (method_exists($page, 'initCard'))
			$page->initCard();
		});
		
		
		// Карточка страницы
		wdpro_on_content(function ($content, $page) {

			// Преобразуем данные так, чтобы в основных данных были данные текущего языка
			// То есть, если сейчас английский, то post_title будет английским, а не русским
			$page->dataToCurrentLang();

			/** @var $page \Wdpro\BasePage */
			if (isset($page->data['post_content']))
			$content = $page->getData('post_content');

			/** @var \Wdpro\BasePage $page */
			if ($page) {

				/** @var \Wdpro\BasePage $page */
				$page->getCard($content);
			}

			return $content;
		});


		// Отключаем форматирование
		if (!get_option('wdpro_keep_standart_editor')) {
			remove_filter( 'the_content', 'wpautop' );
		}

		wdpro_js_data('postId', (int)get_the_ID());

		
		//remove_filter('template_redirect', 'redirect_canonical');
		
		
	}


	/**
	 * Дополниительная инициализация для админки
	 */
	public static function initConsole() {

		\Wdpro\Console\Menu::addSettings('Главная', function ($form) {
			/** @var \Wdpro\Form\Form $form */

			$form->add(array(
				'name'=>'show_on_front',
				'type'=>'check',
				'right'=>'Использовать главную из списка ниже',
				'value'=>'page',
			));
			$form->add(array(
				'name'=>'page_on_front',
				'top'=>'Выберите главную страницу',
				'type'=>'select',
				'options'=>SqlTable::getOptions(array(
					'start'=>array(''=>''),
					'where'=>'WHERE post_parent=0
					AND post_status="publish"
					ORDER BY post_type, menu_order',
					'fields'=>'ID, post_title',
				)),
			));
			$form->add('submitSave');
			
			return $form;
		});

		// ХЗ
		//add_filter ( 'user_can_richedit' , create_function ( '$a' , 'return false;' ) , 50 );
		add_filter ( 'user_can_richedit' , function () {
			return false;
		} , 50 );

		
		// Удаление сущностей при удалении постов
		add_action('admin_init', function () {
			
			// Изменение статуса
			add_action('transition_post_status', function ($newStatus, $oldStatus, $post) {
				
				// Получаем сущность
				if ($page = wdpro_get_post_by_id($post->ID)) {
					
					// Обновляем статус сущности
					$page->mergeData(array(
						'post_status'=>$newStatus,
					))->save();
				}

			}, 10, 3);
			
			// Вообще удаление
			add_action( 'delete_post', function ($postId) {

				// Получаем сущность
				$page = wdpro_get_post_by_id($postId);
				if ($page) {
					$page->remove();
				}
				
			}, 10 );
		});

		/**
		 * Скрипты
		 */
		add_action('admin_footer', function () {
			echo '<script>
			wdpro.WDPRO_TEMPLATE_URL = "'.WDPRO_TEMPLATE_URL.'";
			wdpro.WDPRO_UPLOAD_IMAGES_URL = "'.WDPRO_UPLOAD_IMAGES_URL.'";
			wdpro.WDPRO_HOME_URL = "'.home_url().'/";
			</script>';
		});
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole() {

		add_action(
			'app-ready',
			function ()
			{
				/** @var \Wdpro\BaseEntity $entity */
				$entity = null;
				if (isset($_POST['post_ID'])) $postId = $_POST['post_ID'];
				else if (isset($_GET['post'])) $postId = $_GET['post'];
				global $pagenow;


				// Блок 
				$showStructureMenu = function ($type, $selected=0) use (&$entity)
				{
					add_action(
						'add_meta_boxes',
						function () use (&$entity, &$type, &$selected)
						{
							add_meta_box(
								'postparentdiv',
								__('Attributes'),
								function ( $post ) use (&$entity, &$type, &$selected)
								{
									// Определяем самый корневой раздел этого типа страниц
									// Для этого используем хлебные крошки
									/** @var \Wdpro\Breadcrumbs\ConsoleBreadcrumbs $breadcrumbs */
									$breadcrumbs = null;
									
									do_action('wdpro_console_breadcrumbs',
										function ($br) use (&$breadcrumbs)
										{
											$breadcrumbs = $br;
										}
									);

									$rootSelectId = 0;
									if ($breadcrumbs) {
										$rootSelectId = $breadcrumbs->getParentIdOfPostType
										($type);
									}
									
									//$meta = get_post_meta( $post->ID, '_parent_id', true );
									//$selected = ( isset( $meta ) ) ? $meta : '';

									$dropdown_args = array(
										'post_type'        => $type,
										//'exclude_tree'     => $post->ID,
										'selected'         => $selected,
										'name'             => 'post_parent',
										//'show_option_none' => __( '(no parent)' ),
										'show_option_none' => 'Верхний уровень',
										'sort_column'      => 'post_parent, menu_order, post_title',
										'echo'             => 0,
										'child_of'         => $rootSelectId, // Sales Page
										//'parent'=>'parent',
										'hierarchical'     => 1
									);

									$dropdown_args = apply_filters( 'page_attributes_dropdown_pages_args', $dropdown_args, $post );
									$pages = wp_dropdown_pages( $dropdown_args );

									//print_r($dropdown_args); exit();

									if ( ! empty($pages) )
									{
										wp_nonce_field( plugin_basename( __FILE__ ), 'noncename_wpse_83542' );
										?>
										<p><strong><?php _e('Parent') ?></strong></p>
										<label class="screen-reader-text" for="parent_id"><?php _e('Parent') ?></label>
										<?php
										echo $pages;
									} // end empty pages check

									// Шаблон
									// Сделал, чтобы отображался во всех типах страниц
									if ( /*'page' == $post->post_type && */0 != count( get_page_templates( ) ) && get_option( 'page_for_posts' ) != $post->ID ) {
										$metaPageTemplate = get_post_meta($post->ID, 'page_template');
										if (is_array($metaPageTemplate)) {
											$metaPageTemplate = $metaPageTemplate[0];
										}
										$template = $metaPageTemplate;
										?>
										<p><strong><?php _e('Template') ?></strong></p>
										<label class="screen-reader-text" for="page_template"><?php _e('Page Template') ?></label><select name="page_template" id="page_template">
											<?php
											/**
											 * Filter the title of the default page template displayed in the drop-down.
											 *
											 * @since 4.1.0
											 *
											 * @param string $label   The display value for the default page template title.
											 * @param string $context Where the option label is displayed. Possible values
											 *                        include 'meta-box' or 'quick-edit'.
											 */
											$default_title = apply_filters( 'default_page_template_title',  __( 'Default Template' ), 'meta-box' );
											?>
											<option value="default"><?php echo esc_html( $default_title ); ?></option>
											<?php page_template_dropdown($template); ?>
										</select>
										<?php
									}


									// Порядок
									?>
									<p><strong><?php _e('Order') ?></strong></p>
									<p><label class="screen-reader-text" for="menu_order"><?php _e('Order') ?></label><input name="wdpro_menu_order" type="text" size="4" id="menu_order" value="<?php echo esc_attr($post->menu_order) ?>" /></p>
									<?php
									
									
									// Отображать в меню
									$sqlTable = $entity::sqlTable();
									if ($sqlTable::isField('in_menu')) {
										?>
										<p><label class="screen-reader-text" 
										          for="wdpro_in_menu">Показывать в меню</label>
											<input type="hidden" name="wdpro_in_menu" 
											       value="0" />
											<input name="wdpro_in_menu" type="checkbox" 
											       id="wdpro_in_menu" 
											       <?php if (!isset($entity->data['in_menu'])
											       || $entity->data['in_menu']): 
											       ?>checked="checked" <?php endif; ?>
											       value="1"
											/>
											<label for="wdpro_in_menu">
												<strong>Показывать в меню</strong>
											</label>
										</p>
										<?php
									}
								},
								$entity->getType(),
								'side',
								'core'
							);
						}
					);
				};


				// Редактирование
				if (isset($postId) && $pagenow == 'post.php')
				{
					if ($entity = wdpro_object_by_post_id($postId))
					{
						do_action('wdpro_console_breadcrumbs',
							function ($breadcrumbs)
							{
								/** @var \Wdpro\Breadcrumbs\ConsoleBreadcrumbs $breadcrumbs */
								/*$breadcrumbs->append(
									new \Wdpro\Breadcrumbs\Element('Редактирование')
								);*/
								$breadcrumbs->getRightPrepend()->setComment('Редактирование');
								//$breadcrumbs->removeLink();
							}
						);

						$parentId = get_post_field('post_parent', $postId);

						// Отправляем ID родительской страницы в Javascript, чтобы поменять адрес
						// кнопки "Добавить", чтобы добавление происходило в этот же подраздел, что
						// и эта страница
						add_action('admin_print_scripts', function ($hook) use (&$parentId)
						{
							?>
							<script>
								window.parentPageId = <?php echo((int)$parentId); ?>
							</script>
							<?php
						});

						// Когда данная страница в разделе
						if ($parentId)
						{
							$showStructureMenu(
								get_post_field('post_type', $parentId),
								$parentId
							);
						}

						// Когда данная страница на самом верхнем уровне
						else
						{
							$showStructureMenu(
								get_post_field('post_type', $postId),
								0
							);
						}
					}
				}

				// Новая запись
				if (isset($_GET['post_type']) && $_GET['post_type'])
				{
					if ($entityClass = wdpro_get_class_by_post_type($_GET['post_type']))
					{
						$entity = wdpro_object($entityClass);

						if ($pagenow == 'post-new.php')
						{
							//do_action('wdpro_console_show_breadcrumbs', $entity);

							if ($roll = $entity->getConsoleRoll()) {
								$rollParams = $roll->getParams();

								// Хлебные крошки
								do_action('wdpro_console_breadcrumbs',
									function ($breadcrumbs) use (&$rollParams) {

										/** @var \Wdpro\Breadcrumbs\ConsoleBreadcrumbs $breadcrumbs */
										$breadcrumbs->append(
											new \Wdpro\Breadcrumbs\Element(
												$rollParams['labels']['add_new']
											)
										);
									}
								);

								// Структура
								// Когда данная страница в разделе
								if (isset($_GET['sectionId'])
									&& $_GET['sectionId']
								) {
									$showStructureMenu(
										get_post_field('post_type', $_GET['sectionId']),
										$_GET['sectionId']
									);
								}

								// Когда данная страница на самом верхнем уровне
								else {
									$showStructureMenu(
										$_GET['post_type']
									);
								}
							}
						}
						
						else {
							$showStructureMenu(
								$_GET['post_type']
							);
						}
					}
				}


				if ($entity)
				{
					$form = null;

					// Форма "Параметры"
					if ($form = $entity->getConsoleForm())
					{
						$form->removeFormTag();
						$form->setJsName($entity->getType());

						// Сама форма
						add_action('add_meta_boxes', function () use (&$form, &$entity)
						{
							add_meta_box(
								'additional_form',
								'Данные',

								function () use (&$form, &$entity)
								{
									//$form->setData($entity->getData());
									if (isset($_GET['post']) && $_GET['post']) {
										echo $entity->getEditFormMenu();
									}
									echo $form->getHtml();
								},

								$entity->getType(),
								'normal',
								'high'
							);
						});
					}

					// Сохранялка
					$savePost = function ($postId) use (&$entity, &$form, &$savePost) {

						// Получаем объект поста
						$post = get_post($postId);

						// Сохранение родительского ID
						if (isset($_GET['sectionId'])
							&& $_GET['sectionId'] != $post->post_parent) {

							$post->post_parent = $_GET['sectionId'];
						}


						$updatePost = function () use (&$savePost, &$post) {
							remove_action('save_post', $savePost);

							wp_update_post($post);

							// Обновление Guid
							\Wdpro\Page\SqlTable::update(['guid'=>$post->guid], [
								'ID'=>$post->ID,
							]);

							add_action('save_post', $savePost);
						};


						// Если это не черновик
						if (get_post_field('post_status', $postId) != 'auto-draft'
							&& get_post_type($postId) == $entity->getType()
						)
						{
							//$post = get_post($postId);

							// Данные, которые сохраняются в Entity
							$data = $form ? $form->getData() : array();
							//print_r($data); exit();

							// Преобразование русских букв в английские в адресе страницы
							if (isset($post->post_name)) {

								$rus = null;
								if ($post->post_name) {
									$rus = $post->post_name;
								}
								else {
									$rus = $post->post_title;
								}

								// Нормальный адрес
								$rus = urldecode( $rus );
								$en = wdpro_text_to_file_name( $rus );
								$post->post_name = $en;
								$isSamePostName = function () use (&$post) {
									return !!\Wdpro\Page\SqlTable::count([
										'WHERE `post_name`=%s AND `ID`!=%d',
										[ $post->post_name, $post->ID ]
									]);
								};
								$post->post_name = $en;
								$n = 1;
								while($isSamePostName()) {
									$post->post_name = $en.'-'.$n;
									$n ++;
								}
								$post->guid = home_url($en);
							}

							// Обработка данных из боковой формы
							if ($entity = wdpro_object_by_post_id( $postId )) {

								// № п.п.
								if (isset($_POST['wdpro_menu_order'])) {
									$post->menu_order = $entity->getMenuOrder( $_POST['wdpro_menu_order'] );
									$data['menu_order'] = $post->menu_order;
								}

								if (isset($_POST['page_template'])) {
									$data['page_template'] = $_POST['page_template'];
									$data['_wp_page_template'] = $_POST['page_template'];
									update_post_meta($postId, 'page_template', $data['page_template']);
									//print_r($data);
									//update_post_meta($postId, '_wp_page_template', $data['page_template']);
									$post->page_template = $data['page_template'];
								}

								// Видимость в меню
								if (isset($_POST['wdpro_in_menu'])) {
									$data['in_menu'] = $_POST['wdpro_in_menu'];
								}
							}

							// Это чтобы сохранялись все поля, включая те, которые
							// добавлены через стандартную форму
							foreach($entity->sqlTable()->getFieldsNames() as $fieldName) {
								if (!isset($data[$fieldName])
									&& isset($_POST[$fieldName]))
								{
									//$data[$fieldName] = $_POST[$fieldName];
									$data[$fieldName] = $post->$fieldName;
								}
							}

							// Сортировка страниц
							/*if (isset($_POST['wdpro_menu_order'])) {
								$data['menu_order'] = $_POST['wdpro_menu_order'];
							}*/

							$updatePost();

							if ($data)
							{

								//print_r($data); exit();
								// Дополнительное сохранение в мета данных поста
								foreach($data as $dataName => $dataValue)
								{
									//$post->$dataName = $dataValue;
									update_post_meta($postId, $dataName, $dataValue);
								}

								// Сохранение в основной базе сущности
								$entity->consoleMergeDataFromForm($data)->save();
							}
						}

						// Черновик
						else {
							$updatePost();
						}
					};

					// Сохранение
					add_action('save_post', $savePost);

				}
			}
		);
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {

		// Добавление меток option, которые загружают всякие штуки из настроек
		add_shortcode('option', function ($params) {

			return get_option($params['name']);
		});


		// Шаблон текущей страницы
		wdpro_get_current_page(function ($page) {
			
			/** @var \Wdpro\BasePage $page */
			
			if (isset($page->data['template']) && $page->data['template']) {
				
				\Wdpro\Templates::setCurrentTemplate(
					WDPRO_TEMPLATE_PATH.$page->data['template']
				);
			}
		});


	}


	/**
	 * Установка страницы по-умолчанию
	 * 
	 * @param string $uri Адрес страницы
	 * @param callback $pageDataCallback Каллбэк, который возвращает данные страницы
	 */
	public static function defaultPage($uri, $pageDataCallback) {
		
		add_action('wdpro_pages_default:'.$uri, function () 
		use (&$pageDataCallback, &$uri) {

			if (!isset($_GET['postAdded'])) {
				
				if ($data = $pageDataCallback()) {
					
					$data = wdpro_extend([
						'post_type'=>'page',
						'post_status'=>'publish',
						'post_author'=>1,
						'post_name'=>$uri,
					], $data);
					
					$currentPage = wdpro_get_post_by_name($data['post_name']);
					if (!$currentPage) {
						// Добавляем страницу
						//wp_insert_post($data);

						wdpro_create_post($data);

						// Редирект
						wdpro_location(wdpro_current_uri(['postAdded'=>1]));
					}
				}
				
				else {
					
					throw new Exception("Каллбэк создания страницы ".$uri." не 
					возвратил данных");
				}
			}
		});
	}


	/**
	 * Возвращает обхект страницы по ее URI
	 * 
	 * @param string $postName URI страницы
	 * @return \Wdpro\BasePage
	 */
	public static function getByPostByName($postName) {
		
		if ($pageData = SqlTable::getRow(
			['WHERE `post_name`=%s ', [$postName]],
			'id'
		)) {
			
			return wdpro_get_post_by_id($pageData['id']);
		}

		// Когда страница есть в базе WP, но нету в MVC
		else {

			echo 123;

			if ($postData = \Wdpro\Page\SqlTable::getRow([
					'WHERE post_name = %s',
				[$postName]
			])) {

				print_r($postData);

				// Получаем класс страниц по типу
				if ($class = wdpro_get_entity_class_by_post_type($postData['post_type'])) {
					/** @var \App\BasePage $obj */

					$postData['id'] = $postData['ID'];

					$obj = new $class($postData);

					$obj->save();

					return $obj;
				}
			}
		}
	}

}


return __NAMESPACE__;