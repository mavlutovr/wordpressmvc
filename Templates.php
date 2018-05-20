<?php

namespace Wdpro;

/**
 * Шаблоны
 * 
 * @example http://docs.seobit.ru/dopolniteljnie-shabloni-stranic
 */
class Templates
{
	protected static $templates = array();
	protected static $templatesByUri = array();
	protected static $templatesByFiles = array();
	protected static $forms = array();
	protected static $templateI = 0;




	/**
	 * Инициализация шаблонов
	 */
	public static function init() {
		
		$templatesAddToWordpress = function ($atts)
		{
			//print_r($atts);
			//exit();
			
			// Create the key used for the themes cache
			$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
			// Retrieve the cache list.
			// If it doesn't exist, or it's empty prepare an array
			$templates = wp_get_theme()->get_page_templates();
			if ( empty( $templates ) ) {
				$templates = array();
			}

			// New cache, therefore remove the old one
			wp_cache_delete( $cache_key , 'themes');

			// Now add our template to the list of templates by merging our templates
			// with the existing templates array from the cache.
			$templates = array_merge( $templates, static::getArrayForConsoleMenu());

			// Add the modified cache to allow WordPress to pick it up for listing
			// available templates
			wp_cache_add( $cache_key, $templates, 'themes', 1800 );

			return $atts;
		};


		/**
		 * Добавление формы на страницу
		 * 
		 * @param $post
		 */
		$wdproAddTemplateForm = function ($post) {
			
			$formsHtml = '';
			
			foreach(static::$templates as $n=>$templateParams)
			{
				/** @var WdproForm $form */
				if ($form = $templateParams['getForm']())
				{
					$form->setName('wdproAdditional');
					$form->mergeParams(array(
						'removeFormTag'=>true,
					));
					
					// Загрузка ранее сохраненных данных
					if ($post)
					{
						$data = array();
						$form->eachElementsParams(function ($elementParams) use 
						(&$data, $post)
						{

							$data[$elementParams['name']] = get_post_meta(
								$post->ID,
								$elementParams['name'],
								1
							);
						});
						$form->setData($data);
					}

					$formsHtml .= '<div class="js-wdpro-template-form" data-template-name="' . wdpro_basename($templateParams['file']) . '">'
						//. $templateParams['file']
						. $form->getHtml()
					. '</div>';
				}
			}

			echo('<div id="js-wdpro-templates-forms">' . $formsHtml . '</div>');
		};

		// Сохранение дополнительных форм
		add_action('save_post', function ($postId) {

			// Если форма не прошла проверку
			if (/* !wp_verify_nonce( $_POST['wdpro_nonce'], __FILE__)
				||*/ (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
				|| !current_user_can('edit_post', $postId)
				|| !(isset($_POST['wdproAdditional']) && $_POST['wdproAdditional'])
			) {

				// Завершаем процесс сохранения преждевременно
				return false;
			}

			$post = get_post($postId);
			
			// Получаем данные формы из выбранного шаблона
			if ($templateParams = static::getTemplateParamsByPost($post))
			{
				// Получаем форму
				/** @var WdproForm $form */
				if ($form = $templateParams['getForm']())
				{
					$form->setName('wdproAdditional');

					$form->onSubmit(
						function ($data) use ($postId)
						{
							// Перебираем данные и сохраняем
							foreach($data as $key=>$value)
							{
								empty($value)
									? delete_post_meta($postId, $key)
									: update_post_meta($postId, $key, $value);
							}
						}
					);
				}
			}
			

			return $postId;

		}, 0);

		// Добавление шаблонов в выпадающее меню консоли Атрибуты страницы - Шаблон
		add_filter('page_attributes_dropdown_pages_args', $templatesAddToWordpress);
		
		// Добавление шаблонов в сохранялку страниц
		add_filter('wp_insert_post_data', $templatesAddToWordpress);

		// Определяет файл шаблона по адресу страницы
		add_filter('template_include', function ($template) {

			global $post;
			
			if ($post) {

				// Когда шаблон прикреплен к точному адресу
				if (isset(static::$templatesByUri[$post->post_name]['file']))
				{
					return static::$templatesByUri[$post->post_name]['file'];
				}

				// Когда шаблон указан в параметрах страницы
				if (isset(static::$templatesByFiles[$post->page_template]['file']))
				{
					return static::$templatesByFiles[$post->page_template]['file'];
				}

			}
			
			return $template;
		});
		
		// Дополнительные формы
		add_action('admin_init', function () use (&$wdproAddTemplateForm) {

			
			/*add_meta_box('form_fields',
				'Параметры шаблона',
				$wdproAddTemplateForm,
				'post',
				'normal',
				'high'
			);*/
			add_meta_box('js-wdpro-form-fields',
				'Параметры шаблона',
				$wdproAddTemplateForm,
				'page',
				'normal',
				'high'
			);
		});
	}


	/**
	 * Возвращает параметры выбранного в посте шаблона
	 * 
	 * @param \WP_Post $post Пост/страница
	 * @return null|array
	 */
	public static function getTemplateParamsByPost($post)
	{
		if ($post) {
			// Когда шаблон прикреплен к точному адресу
			if (isset(static::$templatesByUri[$post->post_name]))
			{
				return static::$templatesByUri[$post->post_name];
			}

			// Когда шаблон указан в параметрах страницы
			if (isset(static::$templatesByFiles[$post->page_template]))
			{
				return static::$templatesByFiles[$post->page_template];
			}
		}
	}
	
	
	/**
	 * Добавить шаблон
	 * 
	 * <pre>
	 * \Wdpro\Templates::add(array(
	 *  'file'=>WDPRO_TEMPLATE_PATH.'download.php',
	 *  'uri'=>'download',
	 * ));</pre>
	 * 
	 * @param array $params Параметры
	 */
	public static function add($params) {
		
		$params['_i'] = static::$templateI;
		static::$templateI ++;
		
		$tempForm = null;

		/**
		 * Возвращает формы шаблона
		 * 
		 * @return null|\Wdpro\Form\Form
		 */
		$params['getForm'] = function () use (&$tempForm, &$params)
		{
			if ($tempForm) 
			{
				return $tempForm;
			}
			else
			{
				if ($params['form'])
				{
					$form = $params['form']();
					//$form->saveErrorsToOptions();
					$tempForm = $form;
					return $form;
				}
			}
		};
		
		static::$templates[static::$templateI] = $params;
		
		if (isset($params['uri']) && $params['uri'])
		{
			if (!is_array($params['uri'])) $params['uri'] = [$params['uri']];
			
			foreach($params['uri'] as $uri) {
				static::$templatesByUri[$uri] = $params;
			}
		}
		
		if ($params['file'])
		{
			$fileName = basename($params['file']);
			static::$templatesByFiles[$fileName] = $params;
		}

	}


	/**
	 * Возвращает массив шаблонов для вставки в меню консоли Атрибуты страницы - Шаблон
	 * 
	 * @return array
	 */
	public static function getArrayForConsoleMenu() {
		
		$arr = array();
		
		foreach(static::$templates as $i=>$templateParams)
		{
			if (isset($templateParams['name'])) {
				//$key = $templateParams['uri'] ? $templateParams['uri'] : 'template_'.$i;

				$key = basename($templateParams['file']);

				$arr[$key] = $templateParams['name'];
			}
		}
		
		return $arr;
	}


	/**
	 * Возвращает список шаблонов в теме, которые можно выбрать для страницы
	 * 
	 * @return array
	 */
	public static function getThemeTemplatesList() {

		include_once ABSPATH . 'wp-admin/includes/theme.php';
		if ($templates = get_page_templates()) {
			
			return array_flip($templates);
		}
		
		return [];
	}


	/**
	 * Установка своего шаблона страницы
	 * 
	 * @param string $templateFile Файл шаблона
	 */
	public static function setCurrentTemplate($templateFile) {

		add_filter('template_include', function ($template) use ($templateFile) {

			return $templateFile;
		});
	}
}

add_action('plugins_loaded', function () {
	Templates::init();
});

