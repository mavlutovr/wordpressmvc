<?php
namespace Wdpro\Console;

//use Wdpro\BaseRoll;

/**
 * Список объектов админки
 * 
 * @package Wdpro\Console
 */
class PagesRoll extends Roll
{
	protected static $n = 1;
	
	public function __construct() {
		static:: $n ++;
	}


	/**
	 * Возвращает поддержку редактора, заголовка и других блоков
	 */
	public function getSupports() {
		$supports = [
			'revisions',
			//'excerpt',
			//'page-attributes',
		];

		// Когда надо оставитьстандартный редактор
		if (get_option('wdpro_keep_standart_editor')) {
			$supports[] = 'editor';
		}

		// Когда нету языков, то включаем обычный post_title
//		$controller = static::getController();
//		if (!$controller::isLang()) {
//			$supports[] = 'title';
//		}
		$supports[] = 'title';

		return $supports;
	}

	
	/**
	 * Возвращает uri адрес для кнопки меню в админке
	 *
	 * @param null|array $params Параметры
	 * @return string
	 * @throws RollException
	 */
	public function getRollUri($params=null)
	{
		$this->init();
		
		if ($entityClass = static::getEntityClass())
		{
			/** @var \Wdpro\BaseEntity $entity */
			$postType = $entityClass::getType();
	
			$uri = 'edit.php?post_type='.$postType;
			
			if (isset($params['query']) && $params['query'])
			{
				$uri .= '&'.$params['query'];
			}
			
			return $uri;
		}
		
		else
		{
			throw new RollException('Нет класса сущностей для списка '.get_called_class());
		}
	}


	/**
	 * Инициализация 
	 * 
	 * @return bool
	 * @throws RollException
	 * @throws \Exception
	 */
	public function init()
	{
		$ret = parent::init();

		if ($ret)
		{
			/** @var \Wdpro\BasePage $entityClass */
			if ($entityClass = static::getEntityClass())
			{
				//$postId = isset($_POST['post_ID']) ? $_POST['post_ID'] : $_GET['post'];

				// Запоминаем список для этой сущности
				$entityClass::setConsoleRollClass(static::class);

				// Поддержка всяких штук
				$supports = $this->getSupports();

				// Параметры списка
				$params = $this->_params;
				$params = wdpro_extend(
					array(
						'labels'       => array(
							'name'               => $params['labels']['label'],
							'singular_name'      => $params['labels']['label'],
							'menu_name'          => $params['labels']['label'],
							'parent_item_colon'  => 'Родительский:',
							'all_items'          => 'Все записи',
							'view_item'          => 'Просмотреть',
							'add_new_item'       => 'Добавить новую запись',
							'add_new'            => 'Добавить страницу/раздел',
							'edit_item'          => 'Редактировать запись',
							'update_item'        => 'Обновить запись',
							'search_items'       => 'Найти запись',
							'not_found'          => 'Не найдено',
							'not_found_in_trash' => 'Не найдено в корзине',
							'add_new_subitem'    => 'Добавить подраздел',
						),
						'supports'     => $supports,
						'orderby'      => 'menu_order',
						'order'        => 'ASC',
						//'taxonomies'          => array( 'red_book_tax' ), // категории, которые мы создадим ниже
						'public'       => true,
						'show_in_menu' => false,
						'hierarchical'=>true,
						'subsections'=>true,
						'showposts'=>3,
						'paged'=>true,
						'editor'=>false,
						'columns'=>array(
							'author'=>false,
							'comments'=>false,
						),
						//'show_ui'=>false,
					),
					$params
				);



				/** @var \Wdpro\BasePage $entityClass */
				//$entity = wdpro_object($entityClass, $postId);
				$postType = $entityClass::getType();
				if (strlen($postType) >= 20) {
					echo 'У класса '.$entityClass.' слишком длинное название таблицы, больше 20 символов. Поэтому в него надо добавить: protected static $post_type = \'короткое_имя\';';
				}

				// Регистрируем страницы в Wdpro
				wdpro_register_post_type($entityClass);


				// Отключение стандартного редактора
				if (!get_option('wdpro_keep_standart_editor')) {
					add_action( 'init', function () use (&$postType) {
						remove_post_type_support( $postType, 'editor' );
					} );
				}

				// Языки
				if (defined('WP_ADMIN'))
				add_action('admin_bar_menu', function ($admin_bar) {

					foreach (\Wdpro\Lang\Data::getData() as $lang) {
						$admin_bar->add_menu( array(
							'id'    => 'lang-'.$lang['uri'],
							'title' => '<img src="'
							           . WDPRO_UPLOAD_IMAGES_URL
							           . $lang['flag'].'" /> ',
							'href'  => '#'.$lang['uri'],
							'meta'  => array(
								'class'=>'wdpro-lang-menu js-wdpro-lang-menu',
								'data-lang'=>$lang['uri'],
								//'title' => __('My Item'),
							),
						));
					}


				}, 100);

				// Регистрируем тип страниц
				add_action('init', function () use (&$params, $postType)
				{
					// print_r($params);
					register_post_type( $postType, $params );
					//echo('register_post_type( '.$postType.', <pre>'.print_r($params,1).'</pre>' );
				});
				
				// Убираем блок "Атрибуты страницы"
				add_action('admin_menu', function () {
					remove_meta_box('pageparentdiv', 'page', 'normal');
				});


				// Запрос на получение списков
				add_filter(
					'pre_get_posts',
					function ($wp_query) use ($postType, &$params)
					{
						if (is_admin())
						{
							if ($postType == $wp_query->query['post_type'] 
								&& !(isset($_GET['post_status']) && $_GET['post_status']))
							{

								// Замена параметров из _GET
								if (isset($_GET['orderby']) && $_GET['orderby'])
									$params['orderby'] = $_GET['orderby'];
								if (isset($_GET['order']) && $_GET['order'])
									$params['order'] = $_GET['order'];

								//$wp_query->set('showposts', $params['showposts']);
								$wp_query->set('orderby', $params['orderby']);
								$wp_query->set('order', $params['order']);
								//$wp_query->set('posts_per_page', 3);
								//$wp_query->set('paged', 0);
								
								$postParent = isset($_GET['sectionId']) ? 
									(int)$_GET['sectionId'] : 0;
								$wp_query->set('post_parent', $postParent);

							}
						}
					}
				);


				$standartUrls = get_option('permalink_structure') == '/%postname%/';

				/*if (get_called_class() == 'Wdpro\Blog\ConsoleRoll') {
					
					//print_r(debug_print_backtrace());
					wdproObjectsTrace();
					echo("\n\n------- INIT Wdpro\\Blog\\ConsoleRoll -------\n\n");
				}*/

				// Действия
				add_filter(
					'page_row_actions',
					function ($actions, $post)
					use ($postType, &$params, $entityClass, $standartUrls)
					{
						if ($post->post_status != 'trash' 
							&& !isset($actions['wdpro_inited'])) 
						{

							//$actions['wdpro_inited'] = true;

							/** @var \Wdpro\BasePage $entity */
							$entity = wdpro_object( $entityClass, $post->ID );

							if ($post->post_type == $postType) {

								// Удалить
								$actions['trash'] = preg_replace(
									'~>[^<]*</a>~',
									'></a>',
									$actions['trash'] );
								$actions['trash'] = str_replace(
									"submitdelete",
									"dashicons dashicons-trash",
									$actions['trash']
								);

								// Свойства
								$actions['inline hide-if-no-js'] = preg_replace(
									'~(>[^<]*</a>)~',
									'></a>',
									$actions['inline hide-if-no-js'] );
								$actions['inline hide-if-no-js'] = str_replace(
									'"editinline"',
									"'dashicons editinline dashicons-admin-generic'",
									$actions['inline hide-if-no-js']
								);

								// Перейти
								/*$actions['view'] = preg_replace(
									'~(>[^<]*</a>)~',
									'></a>',
									$actions['view'] );
								$actions['view'] = str_replace(
									'rel="permalink"',
									'rel="permalink" 
									class="dashicons dashicons-external js-post-link"
									data-post-name="'.
									$post->post_name.'"
									 target="_blank"',
									$actions['view']
								);
								if ($standartUrls) {
									$actions['view'] = preg_replace(
										'~(<a href=")([^"]+)(")~',
										'$1../../' . $post->post_name . '$3',
										$actions['view']
									);
								}*/
								$actions['view'] = '<a href="' . home_url($post->post_name) . '"
 class="dashicons dashicons-external js-post-link_"
 target="_blank"></a>';

								// Редактировать
								$actions['edit'] = preg_replace(
									'~(>[^<]*</a>)~',
									'><span class="dashicons dashicons-edit"></span> Изменить</a>',
									$actions['edit'] );
								/*$actions['edit'] = str_replace(
									' title="',
									' class="dashicons dashicons-edit" title="',
									$actions['edit']
								);*/

								$newActions = array();


								// Дочерние элементы
								$newActions = $entity->addChildsToActions( $newActions );

								// Подраздел
								if ($params['subsections']) {
									$newActions = $entity->addSubsectionsToActions($newActions);
								}


								$newActions = array('edit' => $actions['edit']) +
									$newActions;

								return array_merge( $newActions, $actions );
							}
						}

						return $actions;
					},
					10, 2
				);
				
				
				// Редактор
				$params['editor']
				&& !get_option('wdpro_keep_standart_editor')
				&& add_action('admin_init', function ($post) use (&$postType) {


					add_meta_box(
						'post_content_editor',
						'Текст страницы',
						function () use (&$postType) {
							$textHelp = '';

							$textHelp = apply_filters('wdpro_text_help', $textHelp);

							$form = new \Wdpro\Form\Form();

							$editorName = 'post_content';
							$controller = static::getController();
							if ($controller::isLang()) {
								$editorName .= '[lang]';
							}

							$form->add(array(
								'name'=>$editorName,
								'type'=>'ckeditor',
								'bottom'=>$textHelp));

							if (isset($_GET['post'])) {
								$postWordpress = get_post($_GET['post']);

								$formData = [
									'post_content'=>$postWordpress->post_content,
								];

								if ($postWdpro = wdpro_get_post_by_id($_GET['post'])) {
									$formData = array_merge(
										$postWdpro->getData(),
										$formData
									);
								}

								$form->setData($formData);
							}
							$form->removeFormTag();
							echo($form->getHtml());
						},
						$postType,
						'normal'
					);
				});


				// Seo поля
				add_action('admin_init', function () use (&$postType)
				{
					if (get_option('wdpro_additional_remove') != 1) {
						add_meta_box( 'extra_fields',
							'Дополнительно',
							'wdproShowMetaForm',
							$postType,
							'normal');
					}
				});


				// Хлебные крошки, сортировка
				add_action( 'load-edit.php', function() use (&$postType, &$entity) {

					$screen = get_current_screen();

					// Only edit post screen:
					if( 'edit-'.$postType === $screen->id )
					{
						do_action('wdpro_console_breadcrumbs');
					}

					add_filter(
						'manage_'.$screen->id.'_sortable_columns',
						function ($columns)
						{
							$columns['wdpro_menu_order_column'] = 'menu_order';
							return $columns;
						}
					);

				});


				// Колонкии
				if (isset($_GET['post_type']) && $_GET['post_type'] == 
					$postType) {
					
					// Заголовки
					$manage_posts_columns = function ( $columns) use (&$params, &$entityClass)
					{
						if (isset($params['columns'])) {
							foreach($params['columns'] as $columnName=>$column) {
								if ($column['title']) {
									$columns[$columnName] = $column['title'];
								}
								else {
									unset($columns[$columnName]);
								}
							}
						}
						//$columns = array_merge($params['columns'])
						
						$wdproColumns = [];
						
						$table = $entityClass::sqlTable();
						if ($table::isField('in_menu')) {
							$wdproColumns['wdpro_in_menu'] = 'Меню';
						}

						$wdproColumns['wdpro_menu_order_column'] = '№ п.п.';

						return array_merge( $columns,
							$wdproColumns
						);
					};

					// Значения
					$manage_pages_custom_column = function ( $column, $postId ) 
					use ( $postType, &$params ) 
					{

						if (get_post_field( 'post_type', $postId ) == $postType) {

							if (isset($params['columns'][$column])) {
								echo($params['columns'][$column]['callback']($postId));
							}

							// № п.п.
							if ($column == 'wdpro_menu_order_column') {
								echo(get_post_field( 'menu_order', $postId ) . ' ');
							}
							
							// В меню
							if ($column == 'wdpro_in_menu') {
								if (wdpro_get_post_by_id($postId)->getData('in_menu')) {
									echo '<i class="fa fa-check" aria-hidden="true" title="Эта страница отображается в меню"></i>';
								}
								//echo(get_post_meta($postId, 'in_menu', true));
							}
						}
					};

					add_action(
						'manage_posts_columns',
						$manage_posts_columns,
						$postType
					);
					add_action(
						'manage_posts_custom_column',
						$manage_pages_custom_column,
						10,
						2
					);

					add_action(
						'manage_pages_columns',
						$manage_posts_columns,
						$postType
					);
					add_action(
						'manage_pages_custom_column',
						$manage_pages_custom_column,
						10,
						2
					);

				}



				$this->_params = $params;
			}

			else
			{
				throw new RollException('Нет класса сущностей для списка '.get_called_class());
			}
		}
		
		return $ret;
	}


	/**
	 * Возвращает количество дочерних элементов
	 *
	 * @param int $parentid ID родительского объекта
	 * @param string $postType Тип дочерних элементов
	 * @return int
	 */
	public function getChildsCount($parentid, $postType='')
	{
		return static::getEntity($parentid)->getChildsCount($postType);
	}


	/**
	 * Возвращает количество элементов для родительского объекта
	 *
	 * @param \Wdpro\BaseEntity $section Родительский объект
	 * @return null|number
	 */
	public function getCountForSection($section) {

		$typeSql = '';

		$postType = static::getType();
		if ($postType)
		{
			$typeSql = ' AND post_type=%s ';
		}
		
		return \Wdpro\Page\SqlTable::count(array(
			'WHERE post_parent=%d '.$typeSql
			.' AND post_status!="trash" AND post_status!="auto-draft"',
			$section->id(),
			$postType
		));
	}


	/**
	 * Список дочерних подключаемых объектов
	 * 
	 * Например это могут быть товары, которые подключаются к разделу (добавляются в 
	 * раздел)
	 */
	protected static function childs()
	{
		
	}
	
	
	public function getPageCallback()
	{
		
	}


	/**
	 * Является ли этот список подраздельным. То есть, можно ли в нем создавать подразделы.
	 *
	 * Это нужно знать для того, чтобы например, показывать или нет кнопку "Добавить
	 * подраздел"
	 *
	 * @return bool
	 */
	public static function isSubsections() {
		if ($params = static::params()) {
			return !isset($params['subsections']) || $params['subsections'];
		}
	}
}


class RollException extends \Exception
{
	
}