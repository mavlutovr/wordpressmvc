<?php
namespace Wdpro\Blog;


class Controller extends \Wdpro\BaseController {

	protected static $tags = false;


	/**
	 * Инициализация модуля
	 */
	public static function init () {
		// http://localhost/heavy-lift2.ru/blog_tags?tag=Скиддинг
		\Wdpro\Page\Controller::defaultPage(
			'blog_tags',
			function () {
				return require __DIR__.'/default/pages/tags.php';
			}
		);
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite() {

		add_shortcode('blog_posts', function () {
			
			$post = get_post();
			
			return Roll::getHtml(
				['WHERE `post_status`="publish" AND `post_parent`=%d
				AND `in_menu`=1
				ORDER BY `menu_order` DESC', $post->ID]
			);
		});


		// Статьи по тегам
		add_shortcode('blog_tag_list', function () {
			return Roll::getHtml([
				'WHERE post_status="publish" AND in_menu=1 AND tags LIKE %s ORDER BY date_added',
				['%"'.$_GET['tag'].'"%']
			]);
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
							$tags[$tag] = $tag;
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
}

return __NAMESPACE__;