<?php
namespace Wdpro\Man;

class ConsoleRoll extends \Wdpro\Console\Roll {


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
	 *  'orderby'=>'menu_order',
	 *  'icon'=>WDPRO_ICONS_PRODUCTS,
	 *      // https://developer.wordpress.org/resource/dashicons/#lock
	 *    // https://fontawesome.com/v4.7.0/icons/
	 *
	 *  'subsections'=>false,
	 *  'where'=>["WHERE ... %d, %d", [1, 2]],
	 *  'pagination'=>10, //  Количество элементов на странице
	 *  'info'=>'<p>Всякая информация над списком элементов</p>'
	 *
	 *  // В страницах
	 *  'order' => 'DESC',
	 *  'orderby' => 'menu_order',
	 * );
	 * </pre>
	 *
	 * @return array
	 */
	public static function params()
	{
		return [
			'labels'=>[
				'name'=>'Справка',
				'label'=>'Справка',
				'add_new'=>'Добавить справку',
			],

			'icon'=>'far fa-question-circle',

			'where'=>'ORDER BY sorting',
		];
	}


	/**
	 * Возвращает колонки таблицы
	 *
	 * @param array $data Данные строки
	 * @param \Wdpro\BaseEntity $entity Объект списка
	 * @return array
	 */
	public function template($data, $entity)
	{
		return [
			'<a href="'
			. $this->getConsoleCardUrl($data)
			.'">'.$data['name'].'</a>',
			//$data['sorting'],
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
			'Страница справки',
			//'Сортировка',
		];
	}


}