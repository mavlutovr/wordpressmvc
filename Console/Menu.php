<?php
namespace Wdpro\Console;

use Wdpro\Exception;

/**
 * Меню админки Wordpress
 * 
 * @package Wdpro\Console
 */
class Menu
{
	protected static $buttonN = -100;
	protected static $buttonCount = 0;
	

	/**
	 * Добавление кнопки в админку
	 * 
	 * @param array|string $buttonParams Параметры кнопки или класс списка
	 */
	public static function add($buttonParams)
	{
		static::$buttonN += 1;
		static::$buttonCount ++;
		
		// Когда указан только класс списка
		if (is_string($buttonParams))
			$buttonParams = [ 'roll'=>$buttonParams ];
		
		// По-умолчанию
		$buttonParams = wdpro_extend(array(
			'capability' => WDPRO_ADMINISTRATOR,
			'menu_slug'  => 'menu-' . static::$buttonN,
			'page_title' => isset($buttonParams['label']) ? $buttonParams['label'] : '',
			'menu_title' => isset($buttonParams['label']) ? $buttonParams['label'] : '',
			'icon'       => WDPRO_ICONS_PAGES,
			'open'       => '',
			//'n'=>static::$buttonCount,
			'count'=>0,
		), $buttonParams);
		
		// Обработка списка, если укаазан класс списка
		if (isset($buttonParams['roll']) && $buttonParams['roll'])
		{
			$rollClass = $buttonParams['roll'];
			/** @var \Wdpro\Console\PagesRoll $roll */
			$roll = wdpro_object($rollClass);
			
			if (!$buttonParams['page_title'] || !$buttonParams['menu_title'])
			{
				$rollParams = $roll->getParams();

				if (!isset($buttonParams['showNew'])) {
					if (isset($rollParams['showNew'])) {
						$buttonParams['showNew'] = $rollParams['showNew'];
					}
					else {
						$buttonParams['showNew'] = false;
					}
				}

				if (isset($rollParams['icon']) && $rollParams['icon'])
					$buttonParams['icon'] = $rollParams['icon'];

				if (!$buttonParams['page_title'])
					$buttonParams['page_title'] = $rollParams['labels']['menu_name'];
				
				if (!$buttonParams['menu_title'])
					$buttonParams['menu_title'] = $rollParams['labels']['menu_name'];

				if (!isset($buttonParams['n']) || $buttonParams['n'] === null) {
					if (isset($rollParams['n'])) {
						$buttonParams['n'] = $rollParams['n'];
					}
					else {
						$buttonParams['n'] = static::$buttonCount;
					}
				}
			}
			
			
			$buttonParams['menu_slug'] = $roll->getRollUri($buttonParams);
			$buttonParams['open'] = $roll->getPageCallback();
		}

		// Создание типа страниц
		add_action('init', function () use (&$buttonParams, &$roll) {

			// Новые записи
			if ($buttonParams['showNew']) {
				if ($newCount = $roll->getNewCount())
				{
					$buttonParams['count'] = $newCount;
				}
			}


			// Отображаемое количество
			if ($buttonParams['count']) {

				$buttonParams['menu_title'] .= ' <span class="awaiting-mod count-' . $buttonParams['count'] . '"><span class="pending-count">' . $buttonParams['count'] . '</span></span>';
			}
			
			
			add_action(
				'admin_menu',

				function () use (&$buttonParams)
				{
					// Текст кнопки
					$buttonTitle = $buttonParams['menu_title'];

					// Иконки
					$icon = '';
					if (isset($buttonParams['icon']) && $buttonParams['icon']) {

						// Awesome 5
						if (strstr($buttonParams['icon'], 'fas')
						|| strstr($buttonParams['icon'], 'far')
						|| strstr($buttonParams['icon'], 'fal')
						|| strstr($buttonParams['icon'], 'fab')
						) {
							$buttonTitle = '<i class="fa5 '.$buttonParams['icon']
								.'" aria-hidden="true"></i>'.$buttonTitle;
							$icon = 'none';
						}

						// Awedome 4
						else if (strstr($buttonParams['icon'], 'fa-')) {
							$buttonTitle = '<i class="fa '.$buttonParams['icon']
								.'" aria-hidden="true"></i>'.$buttonTitle;
							$icon = 'none';
						}

						// Standard
						else {
							$icon = $buttonParams['icon'];
						}
					}


					if (isset($buttonParams['position']) 
						&& $buttonParams['position'] == 'settings')
					{
						add_options_page(
							$buttonParams['page_title'],
							$buttonTitle,
							$buttonParams['capability'],
							$buttonParams['menu_slug'],
							$buttonParams['open']
						);
					}
					else
					{
						add_menu_page(
							$buttonParams['page_title'],
							$buttonTitle,
							$buttonParams['capability'],
							$buttonParams['menu_slug'],
							$buttonParams['open'],
							$icon,
							-100 - (isset($buttonParams['n']) ?
								$buttonParams['n'] : 0)
						);
					}
				}
			);
		});
	}


	/**
	 * Добавляет кнопку в блок настроек
	 *
	 * @param array|string $buttonParams
	 */
	public static function addToSettings($buttonParams) {
		if (is_string($buttonParams))
			$buttonParams = [ 'roll'=>$buttonParams ];

		$buttonParams['position'] = 'settings';
		static::add($buttonParams);
	}


	/**
	 * Добавление настроек
	 *
	 * @param array|string $buttonParams Параметры кнопки
	 * @param null|\callback $formCallback Каллбэк, который принимает объект формы и в 
	 * котором следует добавить в форму поля и вернуть объект формы
	 */
	public static function addSettings($buttonParams, $formCallback=null)
	{
		static::$buttonN += 1;

		if (is_string($buttonParams))
		{
			$buttonParams = array(
				'label'=>$buttonParams,
				'form'=>$formCallback,
			);
		}

		// По-умолчанию
		$buttonParams = wdpro_extend(array(
			'capability' => WDPRO_ADMINISTRATOR,
			'menu_slug'  => 'menu-' . static::$buttonN,
			'page_title' => isset($buttonParams['label']) ? $buttonParams['label'] : '',
			'menu_title' => isset($buttonParams['label']) ? $buttonParams['label'] : '',
		), $buttonParams);

		if (!isset($buttonParams['menu_slug']) || !$buttonParams['menu_slug'])
		{
			$buttonParams['menu_slug'] = wdpro_text_to_file_name(
				$buttonParams['menu_title']
			);
		}

		add_action(
			'admin_menu',

			function () use (&$buttonParams)
			{
				add_options_page(
					$buttonParams['page_title'],
					$buttonParams['menu_title'],
					$buttonParams['capability'],
					$buttonParams['menu_slug'],

					function () use (&$buttonParams) {

						if ($buttonParams['menu_slug'] == $_GET['page']) {

							// Форма настроек
							if (isset($buttonParams['form'])) {
								$form = new \Wdpro\Form\Form('wdpro');
								/** @var \Wdpro\Form\Form $form */
								$form = $buttonParams['form']($form);

								if (!$form) {
									throw new Exception('Каллбэк, в котором добавляются 
								поля в форму, не возвращает саму форму. Добавьте в 
								конце каллбэка return $form;');
								}

								$text = '<h2>'.$buttonParams['page_title'].'</h2>';

								$form->onSubmit(function ($data) {

									foreach($data as $key=>$value)
									{
										update_option($key, $value, true);
									}
								});

								$data = array();
								$form->eachElementsParams(
									function ($elementParams) use (&$data) {
										if (isset($elementParams['name']))
										{
											$data[$elementParams['name']]
												= get_option($elementParams['name']);
										}
									}
								);
								$form->setData($data);

								$text .= '<div class="wdpro-settings-form">'
								         .$form->getHtml()
								         .'</div>';

								echo($text);
							}


							// Обычная страница
							else if (isset($buttonParams['open'])) {

								echo $buttonParams['open']();
							}
						}

					}
				);
			}
		);
	}
}