<?php
namespace Wdpro\Blog;


class Controller extends \Wdpro\BaseController {

	protected static $tags = false;
	protected static $postNamePrefix = false;


	/**
	 * Инициализация модуля
	 */
	public static function init () {
		// http://localhost/heavy-lift2.ru/blog_tags?tag=Скиддинг
		\Wdpro\Page\Controller::defaultPage(
			'blog_tag_list',
			function () {
				return require __DIR__.'/default/pages/tags.php';
			}
		);
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {

		// Статьи
		add_shortcode('blog_posts', function () {

			$post = get_post();

			return Roll::getHtml(
				['WHERE `post_status`="publish"
				AND `post_parent`=%d
				AND `in_menu`=1
				AND `post_title[lang]`!=""
				ORDER BY `menu_order` DESC', $post->ID]
			);
		});


		// Статьи по тегам
		wdpro_on_content_uri('blog_tag_list', function ($content, $page) {

			/** @var \Wdpro\Blog\Entity $page */
			wdpro_data('h1', $page->getData('h1') . ' - ' . $_GET['tags']);

			wdpro_replace_or_append(
				$content,
				'[blog_tag_list]',

				Roll::getHtml([
					'WHERE post_status="publish" AND in_menu=1 AND tags[lang] LIKE %s ORDER BY date_added',
					['%"'.urldecode($_GET['tags']).'"%']
				])
			);

			//echo 2; exit();

			return $content;
		});




		wdpro_on_uri('blog_tag_list', function () {

			$page = wdpro_current_page();


			wdpro_data('noindex', true);
			wdpro_data('h1', $page->getH1() . ' - ' . urldecode($_GET['tags']));
			wdpro_data('title', $page->getTitle() . ' - ' . urldecode($_GET['tags']));
		});
	}


	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки: https://developer.wordpress.org/resource/dashicons/#forms
	 */
	public static function initConsole() {

		\Wdpro\Console\Menu::addSettings('Блог', function ($form) {

			/** @var \Wdpro\Form\Form $form */

			// Отправка события на дополнительную инициализацию формы настроек
			do_action('wdpro_blog_init_console', $form);

			$form->add([
				'name'=>'blog_codes',
				'top'=>'Вставить html код под статью',
				'bottom'=>'Сервисы социальных кнопок:
					<a href="https://usocial.pro/" target="_blank">social.pro</a>,
					<a href="https://tech.yandex.ru/share/" target="_blank">tech.yandex.ru/share/</a>
				',
				'type'=>$form::TEXT,
				'width'=>600,
			]);
			$form->add($form::SUBMIT_SAVE);

			return $form;
		});
	}


	/**
	 * Дополнительная обработка формы
	 *
	 * @param callback $callback Каллбэк, принимающий форму
	 */
	public static function initForm($callback) {

		add_action('blog_console_form', $callback);
	}


	/**
	 * Включает теги
	 *
	 * @param bool $on true
	 */
	public static function setTags($on) {
		static::$tags = $on;
	}


	/**
	 * Проверяет, включены ли теги
	 *
	 * @return bool
	 */
	public static function isTags() {
		return static::$tags;
	}


	/**
	 * Check is tags pages module exists
	 *
	 * @return boolean
	 */
	public static function isTagsPagesModule() {
		return \Wdpro\Modules::existsWdpro('blog/tags');
	}


	/**
	 * Возвращает список тегов
	 *
	 * @return array
	 */
	public static function getTagsList() {
		$tags = [];

		$fields = '';
		if ($langs = \Wdpro\Lang\Data::getSuffixes()) {
			foreach ($langs as $lang) {
				if ($fields) $fields .= ', ';
				$fields .= 'tags'.$lang;
			}
		}

		if ($sel = SqlTable::select('', $fields)) {
			foreach ($sel as $value) {
				foreach ($langs as $lang) {
					if (is_array($value['tags'.$lang])) {
						foreach ($value['tags'.$lang] as $tag) {
							$tag = trim($tag);
							if ($tag) {
								$tags[$tag] = $tag;
							}
						}
					}
				}
			}

			sort($tags);
		}

		return array_values($tags);
	}


	/**
	 * Обработка тегов для шаблона
	 *
	 * @param array $tags Теги
	 *
	 * @return array
	 */
	public static function prepareTagsForTemplate($tags) {
		if (is_array($tags)
		    && (!count($tags) || !isset($tags[0]) || !$tags[0])) {
			$tags = null;
		}

		return $tags;
	}


	/**
	 * Set prefix uri mode (blog/article_post_name)
	 * @param bool enabled
	 */
	 public static function usePostNamePrefix($enabled) {
		 static::$postNamePrefix = $enabled;
	 }


	 /**
	  * Return post prefix mode (enabled)
	  */
	 public static function isPostNamePrefix() {
		 return static::$postNamePrefix;
	 }
}

return __NAMESPACE__;
