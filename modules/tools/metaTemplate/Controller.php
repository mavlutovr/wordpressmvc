<?php
namespace Wdpro\Tools\MetaTemplate;

class Controller extends \Wdpro\BaseController {

	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole()
	{
		\Wdpro\Console\Menu::addToSettings(ConsoleRoll::class);
	}


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite()
	{
		$sel = SqlTable::select('ORDER BY menu_order');


		/**
		 * Добавляет обработку метатегов
		 * @param string $key title, description, h1
		 * @param Entity $template
		 */
		$add = function ($key, $template) {

			add_filter('wdpro_'.$key, function ($value) use (&$template, $key) {

				if (!$value) {
					$value = $template->getData($key.'[lang]');
					$page = wdpro_current_page();

					$data = [
						'post_title'=>$page->getData('post_title'),
						'h1'=>$page->getH1(false),
						'title'=>$page->getTitle(false),
						'description'=>$page->getDescription(false),
					];
					$value = wdpro_render_text($value, $data);
				}

				return $value;
			});
		};


		// При инициализации хлебных крошек
		breadcrumbsInit(function ($breadcrumbs) use (&$sel, &$add) {
			/** @var \Wdpro\Breadcrumbs\Breadcrumbs $breadcrumbs */

			// Находим шаблон для данной страницы
			$breadcrumbs->eachReverse(function ($element) use (&$sel, &$add) {
				/** @var \Wdpro\Breadcrumbs\Element $element */

				// Перебираем все шаблоны
				foreach ($sel as $row){

					// Если это шаблон для страницы в хлебных крошках
					if ($element->isUri($row['post_name'])) {
						$instance = Entity::instance($row);
						$add('title', $instance);
						$add('description', $instance);
						$add('h1', $instance);
					}
				}
			});

		});
	}


}


return __NAMESPACE__;