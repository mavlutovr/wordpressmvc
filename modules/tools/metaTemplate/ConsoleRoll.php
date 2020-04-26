<?php

namespace Wdpro\Tools\MetaTemplate;

class ConsoleRoll extends \Wdpro\Console\Roll
{

	/**
	 * Возвращает параметры списка (необходимо переопределить в дочернем классе для
	 * установки настроек)
	 *
	 * <pre>
	 * return array(
	 *  'labels'=>array(
	 *   'name'=>'Разделы каталога',
	 *   'label'=>'Каталог',
	 *   'add_new'=>'Добавить раздел...',
	 *  ),
	 *  'order'=>'ASC',
	 *  'orderby'=>'$ORDER_FIELD',
	 *  'icon'=>WDPRO_ICONS_PRODUCTS,
	 *      // https://developer.wordpress.org/resource/dashicons/#lock
	 *
	 *  'subsections'=>false,
	 *  'where'=>["WHERE ... %d, %d", [1, 2]],
	 *  'pagination'=>10, //  Количество элементов на странице
	 *  'info'=>'<p>Всякая информация над списком элементов</p>',
	 *
	 *  'showNew' => true, // Показывать в меню количество новых записей
	 * );
	 * </pre>
	 *
	 * @return array
	 */
	public static function params()
	{
		return [
			'labels' => [
				'label' => 'Мета-шаблоны',
				'add_new' => 'Добавить мета-шаблон',
			],
			// Когда это дочерний элемент
			/*'where'  => [
				'WHERE `post_parent`=%d ORDER BY `menu_order`',
				[
					isset($_GET['sectionId']) ? $_GET['sectionId'] : 0,
				]
			],*/
			'where' => 'ORDER BY menu_order',
			'icon' => 'fas fa-file',

			// 'pagination'=>10, //  Количество элементов на странице
			'info'=>'<p>Здесь у разделов можно задать шаблоны для мета-тегов внутренних страниц.</p>',
		];
	}


	/**
	 * Возвращает заголовки таблицы в виде массива
	 *
	 * @return array
	 */
	public function templateHeaders()
	{
		return [
			'Раздел',
			'Мета-шаблоны для внутренних страниц',
			'№ п.п.',
		];
	}


	/**
	 * Возвращает колонки таблицы
	 *
	 * @param array $data Данные строки
	 * @param \Wdpro\BaseEntity $entity Сущность
	 * @return array
	 */
	public function template($data, $entity)
	{

		$templates = '';

		if ($data['title'])
			$templates .= '<p><strong>Title</strong></p><p>' . $data['title'] . '</p>';

		if ($data['description'])
			$templates .= '<p><strong>Description</strong></p><p>' . $data['description'] . '</p>';

		if ($data['h1'])
			$templates .= '<p><strong>H1</strong></p><p>' . $data['h1'] . '</p>';


		$post = wdpro_get_post_by_name($data['post_name']);
		if ($post) {
			$page = '<a href="'.$post->getUrl().'" target="_blank">'.$post->getButtonText().'</a>';
		}
		else {
			$page = $data['post_name'];
		}

		return [
			$page,

			$templates,

			$this->getSortingField($data)
		];
	}


}