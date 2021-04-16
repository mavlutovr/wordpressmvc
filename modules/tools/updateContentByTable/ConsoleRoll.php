<?php
namespace Wdpro\Tools\UpdateContentByTable;

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
				'label'=>'Обновление контента по таблице',
				'name'=>'Обновление контента по таблице',
				'icon'=>'fas fa-sync',
			],
      'n'=>200,
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
		// Controller::initVaribles();

    $form = new Form();
    $errors = [];

    $form->onSubmit(function ($data) use (&$error) {
      try {
          Controller::updateData($data['table']);
      }
      catch(\Exception $err) {
        $errors = [$err->getMessage()];
      }
    });

    $formHtml = $form->getHtml();


    $colls = [];

    try {
      $colls = Controller::getLastColls('label');
    }
    catch(\Exception $err) {}
    
    $collsText = '';
    if ($colls && count($colls)) {
      $collsText = \implode('</td><td>', $colls);
      $collsText = '
      <div class="update-content-table-colls--block">
        <p>Последние колонки, которые могут быть применены к текущим данным, если не будет новых заголовков:</p>
        <table class="update-content-table-colls"><tr><td>'.$collsText.'</td></tr></table>
      </div>
      ';
    }

		$errors = array_merge(
			$errors,
			Controller::getErrors()
		);

		echo wdpro_render_php(
			__DIR__.'/templates/index.php',
			[
        'form'=>$formHtml,
        'errors'=>$errors,
        'collsText'=>$collsText,
			]
		);
	}
  
}

