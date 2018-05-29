<?php
namespace Wdpro\Dev;

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
	 *  'orderby'=>'',
	 *  'icon'=>WDPRO_ICONS_PRODUCTS,
	 *      // https://developer.wordpress.org/resource/dashicons/#lock
	 *
	 *  'subsections'=>false,
	 *  'where'=>["WHERE ... %d, %d", [1, 2]],
	 *  'pagination'=>10, //  Количество элементов на странице
	 *  'info'=>'<p>Всякая информация над списком элементов</p>'
	 * );
	 * </pre>
	 *
	 * @return array
	 */
	public static function params() {

		return [
			'labels' => [
				'label'   => 'Разработка',
				'add_new' => 'Добавить модуль',
			],
			'icon'   => 'dashicons-admin-tools',
		];
	}


	/**
	 * Отображает страницу модуля
	 *
	 * @throws Exception
	 * @throws \Exception
	 */
	public function viewPage() {

		// Форма
		$form = new \Wdpro\Form\Form('devCreateModule');
		$form->add([
			'type'=>$form::HTML,
			'html'=>'<hr>',
		]);
		$form->add([
			'name'    => 'plugin',
			'left'     => 'Плагин, где создать модуль',
			'type'    => $form::SELECT,
			'options' => [
				''      => '',
				'app'   => 'App',
				'wdpro' => 'WdPro',
			],
			'*'=>true,
		]);
		$form->add([
			'name'    => 'type',
			'type'    => $form::SELECT,
			'left'=>'Тип',
			'options' => [
				''       => '',
				'simple' => 'Простые элементы',
				'page'   => 'Страницы',
			],
			'*'       => true,
		]);
		$form->add([
			'left'  => 'Пространство имен',
			'name' => 'namespace',
			'*'    => true,
		]);
		$form->add([
			'name' => 'dir',
			'left'  => 'Папка модуля',
			'*'    => true,
		]);
		$form->add([
			'type' => 'html',
			'html' => '<h2>Создать следующие классы</h2>',
		]);
		$form->add([
			'left'  => 'Entity',
			'right' => 'Сущность, 1 экземаляр того, что будет в mysql таблице',
			'name'  => 'Entity',
			'type'  => $form::CHECK,
			'autoWidth'=>false,
		]);
		$form->add([
			'type'=>$form::HTML,
			'html'=>'<hr>',
		]);
		$form->add([
			'left'  => 'Mysql таблица',
			'name'  => 'SqlTable',
		]);
		$form->add([
			'type'=>$form::HTML,
			'html'=>'<hr>',
		]);
		$form->add([
			'left'  => 'ConsoleForm',
			'right' => 'Форма в админке',
			'name'  => 'ConsoleForm',
			'type'  => $form::CHECK,
			'autoWidth'=>false,
		]);
		$form->add([
			'type'=>$form::HTML,
			'html'=>'<hr>',
		]);
		$form->add([
			'left'  => 'ConsoleRoll',
			'right' => 'Форма список элементов в админке',
			'name'  => 'ConsoleRoll',
			'type'  => $form::CHECK,
			'autoWidth'=>false,
		]);
		$form->add([
			'name'=>'ConsoleRoll_label',
			'left'=>'Текст кнопки',
		]);
		$form->add([
			'name'=>'ConsoleRoll_icon',
			'left'=>'<a href="https://developer.wordpress.org/resource/dashicons/"
			            target="_blank">Иконка wordpress</a>,
			            <a href="http://fontawesome.io/icons/" target="_blank">Awesome</a>',
		]);
		$form->add([
			'name'=>'ConsoleRoll_add_new',
			'left'=>'Добавить ...',
		]);
		$form->add([
			'name'=>'ConsoleRoll_sorting_field',
			'left'=>'Поле сортировки',
			'type'=>$form::SELECT,
			'options'=>[
				''=>'',
				'sorting'=>'sorting',
				'menu_order'=>'menu_order',
			],
		]);
		$form->add([
			'type'=>$form::HTML,
			'html'=>'<hr>',
		]);
		$form->add([
			'left'=>'Readme Название',
			'name'=>'Readme',
		]);
		$form->add([
			'type'  => $form::SUBMIT,
			'value' => 'Создать модуль',
			'class' => 'button button-primary button-large',
		]);


		$formHtml = '';

		// Submit
		$form->onSubmit(function ($data) use (&$form, &$formHtml) {

			if (!$data['plugin']) { $form->showErrorMessage('Ошибка'); return false; }


			// Создаем папку модуля
			$pluginDir='';
			if ($data['plugin'] == 'app') {
				$pluginDir = WDPRO_PATH . '../app/';
			}
			else if ($data['plugin'] == 'wdpro') {
				$pluginDir = WDPRO_PATH.'modules/';
			}
			$moduleDir = $pluginDir . $data['dir'];
			$moduleDir = wdpro_realpath($moduleDir).'/';
			//echo($moduleDir); exit();
			mkdir($moduleDir, 0777, true);


			// Родительское пространство имен
			if ($data['plugin'] == 'app') {
				$data['parent_namespace'] = 'App';
			}
			else {
				$data['parent_namespace'] = 'Wdpro';
			}


			// Создавалка файлов
			$create = function ($className, $type=null) use (&$moduleDir, &$data) {

				if ($type) {
					$sourceFile = __DIR__.'/templates/'.$className.'.'.$type.'.template.php';
				}
				else {
					$sourceFile = __DIR__.'/templates/'.$className.'.template.php';
				}
				$targetFile = $moduleDir.$className.'.php';
				$code = wdpro_render_php($sourceFile, $data);
				file_put_contents($targetFile, $code);
			};


			// Создание файлов
			$create('Controller');
			if ($data['ConsoleForm']) { $create('ConsoleForm'); }
			if ($data['ConsoleRoll']) { $create('ConsoleRoll', $data['type']); }
			if ($data['SqlTable']) { $create('SqlTable'); }
			if ($data['Entity']) { $create('Entity', $data['type']); }
			if ($data['Readme']) {
				$Readme = wdpro_render_php(
					__DIR__.'/templates/Readme.template.php',
					$data
				);
				file_put_contents(
					$moduleDir.'/Readme.md',
					$Readme
				);
			}

			$formHtml .= '<p>Модуль создан в папке '.$moduleDir.'</p>';
		});

		if (!$form->sended()) {
			$formHtml = $form->getHtml();
		}

		echo wdpro_render_php(
			__DIR__ . '/templates/page.php',
			[
				'form' => $formHtml,
			]
		);
	}


}
