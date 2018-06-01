<?php

namespace Wdpro\Tools\Watermark;

class Controller extends \App\BaseController {
	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки:
	 * https://developer.wordpress.org/resource/dashicons/#forms
	 * https://fontawesome.com/v4.7.0/icons/
	 */
	public static function initConsole () {


		// Перерисовка водяных знаков
		wdpro_ajax('watermark_redraw_step', function () {


			// Создаем массив модулей с водяными знаками
			$modules = [];
			foreach ( \Wdpro\Modules::getControllers() as $controller ) {

				$namespace = $controller::getNamespace();
				/** @var \App\BaseForm $form */
				$formClass = $namespace . '\\ConsoleForm';

				try {
					if ( class_exists($formClass) ) {
						$form = new $formClass;

						/** @var \Wdpro\Console\Roll $roll */
						/*$roll = $namespace.'\\ConsoleRoll';
						$rollParams = $roll::params();*/

						if ( $form && $elements = $form->getWatermarkRedrawingElements() ) {
							$modules[] = [
								'formClass'  => $formClass,
								'formName'   => $namespace,
								'elements'   => $elements,
								'form'       => $form,
								'controller' => $controller,
							];
						}
					}
				} catch ( \Exception $exception ) {
					//print_r($exception);
				}
			}


			$runModule = function ($module) {

				// Получаем из базы следующую запись
				/** @var \App\BaseController $controller */
				$controller = $module['controller'];
				$table      = $controller::sqlTable();

				$sql = '';
				if ($table::isField('post_status')) {
					$sql .= ' AND post_status="publish"';
				}

				if ( $row = $table::getRow([
					'WHERE id>%d'.$sql.' ORDER BY id LIMIT 1',
					[ $_POST['lastId'] ],
				]) ) {

					/** @var \Wdpro\Form\Form $form */
					//$form = $module['form'];

					/** @var \Wdpro\Form\Elements\Image[] $elements */
					$elements = $module['elements'];
					foreach ( $elements as $element ) {
						$element->redrawWatermarks($row);
					}

					return [
						'id'        => $row['id'],
						'formClass' => $module['formClass'],
						'formName'  => $module['formName'],
						'element'   => $row['id'] . ' - ' . (
							$row['post_title'] ? $row['post_title'] :
								( $row['name'] ? $row['name'] : '' )
							),
					];
				}
			};


			$lastFormClass = null;
			if ( $_POST['lastFormClass'] ) {
				$lastFormClass = str_replace('\\\\', '\\', $_POST['lastFormClass']);
			}

			// Получаем запись
			foreach ( $modules as $n => $module ) {

				if ( ! $lastFormClass || $lastFormClass == $module['formClass'] ) {
					$data = $runModule($module);

					if ( ! $data ) {
						if ( isset($modules[ $n + 1 ]) ) {
							$data = $runModule($modules[ $n + 1 ]);
						}
					}

					return $data;
				}
			}
		});


		// Форма настроек
		\Wdpro\Console\Menu::addSettings('Водяной знак', function ($form) {

			/** @var $form \App\BaseForm */

			$form->addHeader('Поменять водяной знак');
			$form->add([
				'name' => 'wdpro_watermark',
				'left' => 'Картинка водяного знака',
				'type' => $form::IMAGE,
			]);

			$form->add($form::SUBMIT_SAVE);

			$form->add([
				'type' => $form::HTML,
				'html' => '<BR><BR><BR><BR><BR><BR><BR><BR><BR><BR>
<div id="js-watermark-redraw">
<h2>Перерисовать водяные знаки</h2>
<p><input type="button" 
 class="wdpro-form-submit button button-primary button-large js-watermark-button-start"
 value="Запустить перерисовку"/>
 <input type="button" 
 style="display: none;"
 class="wdpro-form-submit button button-primary button-large js-watermark-button-stop"
 value="Остановить"/>
</p>
<div class="js-watermark-container"></div>
</div>
',
			]);


			return $form;
		});
	}


}


return __NAMESPACE__;