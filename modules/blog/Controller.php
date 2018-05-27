<?php
namespace Wdpro\Blog;


class Controller extends \Wdpro\BaseController {

	protected static $tags = false;

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
}

return __NAMESPACE__;