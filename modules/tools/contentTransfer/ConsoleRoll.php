<?php
namespace Wdpro\Tools\ContentTransfer;

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
	 *      // https://fontawesome.com/
	 *
	 *  'subsections'=>false,
	 *  'where'  => [
	 *    'WHERE `post_parent`=%d ORDER BY `menu_order`',
	 *    [
	 *      wdpro_current_console_section_id(), // ID текущего раздела
	 *    ]
	 *  ],
	 *
	 *
	 *
	 *  'pagination'=>10, //  Количество элементов на странице
	 *  'info'=>'<p>Всякая информация над списком элементов</p>'
	 *
	 *  // В страницах
	 *  'order' => 'DESC',
	 *  'orderby' => 'menu_order',
	 *
	 *  // Показывать в меню количество новых записей
	 *  'showNew' => true,
	 * );
	 * </pre>
	 *
	 * @return array
	 */
	public static function params()
	{
		return [
			'labels'=>[
				'label'=>'Перенос контента',
				'name'=>'Перенос контента',
				'icon'=>'fas fa-boxes',
			],
		];
	}


	/**
	 * Отображает страницу модуля
	 *
	 * @throws Exception
	 * @throws \Exception
	 */
	public function viewPage()
	{
		Controller::initVaribles();

		if (!empty($_GET['test'])) {

			$form = new \Wdpro\Form\Form();
			$form->add([
				'name'=>'image',
				'type'=>$form::IMAGE,
				'top'=>'Картинка',
			]);
			$form->add([
				'name'=>'test',
				'top'=>'Тест',
				'*'=>true,
			]);

			print_r($form->getData([
				'image'=>'https://unipakspb.ru/int/upload/5d26ff9b5fa08.jpg',
			]));

			print_r($form->getJson());

			exit();

			return false;
		}

		echo wdpro_render_php(
			__DIR__.'/templates/index.php',
			[

			]
		);
	}





}