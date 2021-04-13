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
		$templates = SqlTable::select('ORDER BY menu_order');


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
		breadcrumbsInit(function ($breadcrumbs) use (&$templates, &$add) {
			/** @var \Wdpro\Breadcrumbs\Breadcrumbs $breadcrumbs */

			// Находим шаблон для данной страницы
			$breadcrumbs->eachReverse(function ($element) use (&$templates, &$add) {
				/** @var \Wdpro\Breadcrumbs\Element $element */

				// echo PHP_EOL.'# BREAD: '.PHP_EOL;
				// print_r($element->getData());

				// Перебираем все шаблоны
				foreach ($templates as $templateRow) {

					// echo PHP_EOL.'- Template'.PHP_EOL;

					// print_r($templateRow);

					// Если это шаблон для страницы в хлебных крошках
					if ($element->isUri($templateRow['post_name'])) {
						$template = Entity::instance($templateRow);
						$add('title', $template);
						$add('description', $template);
						$add('h1', $template);
					}
				}
			});

			// Добавляем шаблоны главной /
			foreach ($templates as $templateRow) {

				// echo PHP_EOL.'- Template HOME'.PHP_EOL;

				// Если это шаблон для страницы в хлебных крошках
				if ($templateRow['post_name'] === '/') {
					$template = Entity::instance($templateRow);
					$add('title', $template);
					$add('description', $template);
					$add('h1', $template);
				}
			}

		});
	}


}


return __NAMESPACE__;