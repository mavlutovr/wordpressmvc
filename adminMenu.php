<?php

add_action(
	'wdpro-ready',
	function ()
	{
		add_action(
			'admin_menu',

			function () {

				add_options_page(
					'Head',
					'Head',
					'administrator',
					'wdproHead',
					function () {
						echo('<h1>Head, seo</h1>');

						wdproOptionsForm(array(
							'title'=>'Настройки',
							'name'=>'options',
							'elements' => array(

								array(
									'type'=>'text',
									'top'=>'Дополнительные мета-теги в head',
									'name'=>'wdpro_head_additional',
									'width'=>600,
								),

								array(
									'type' => 'submit',
									'text' => 'Сохранить',
									'class'=>WDPRO_BUTTON_CSS_CLASS,
								),

								array(
									'type'=>'html',
									'html'=>'<h2>Seo</h2>',
								),

								array(
									'name'=>'wdpro_title_template[lang]',
									'top'=>'Шаблон title',
									'right'=>'[h1]',
								),

								array(
									'name'=>'wdpro_description_template[lang]',
									'top'=>'Шаблон description',
									'right'=>'[h1]',
								),

								array(
									'name'=>'wdpro_keywords_template[lang]',
									'top'=>'Шаблон keywords',
									'right'=>'[h1]',
								),
							),
						));
					}
				);

				// Options
				add_options_page(
					'Настройки Wordpress MVC',
					'Wordpress MVC',
					'administrator',
					'wdproOptions',
					function () {
						echo('<h1>Настройки Wordpress MVC</h1>');

						if (!is_dir(__DIR__.'/../app')) {
							if ($_GET['create_plugin_app']) {
								wdpro_copy(
									__DIR__.'/modules/install/default/app_plugin',
									__DIR__.'/../app',
									function ($fileName) {
										return str_replace('.php.html', '.php', $fileName);
									});


								echo('<p>Загатовка плагина-приложения создана. <a 
class="button-primary"
href="'.WDPRO_CONSOLE_URL.'plugins.php">Активировать плагин</a>
</p>
');
							}
							else {
								echo('<p><a href="'.wdpro_current_uri(['create_theme_app', 'create_plugin_app'])
								     .'&create_plugin_app=1">Создать загатовку плагина-приложения App</a></p>');
							}
						}
						if (!is_dir(__DIR__.'/../../themes/app')) {
							if ($_GET['create_theme_app']) {
								wdpro_copy(__DIR__.'/modules/install/default/app_theme',
									__DIR__.'/../../themes/app');
								echo('<p>Загатовка темы создана. <a 
class="button-primary"
href="'.WDPRO_CONSOLE_URL.'themes.php">Активировать тему</a>
</p>');
							}
							else {
								echo( '<p><a href="' . wdpro_current_uri(['create_theme_app', 'create_plugin_app'])
								      . '&create_theme_app=1">Создать загатовку темы</a></p>' );
							}
						}

						/*$form = new wdproForm();
						$form->add(array(
							'name'=>'test',
							'left'=>'TEST',
						));
						$form->add(array(
							'name'=>'check',
							'type'=>'check',
							'right'=>'Чекбокс',
						));
						$form->add(array(
							'type'=>'submit',
							'text'=>'Сохранить',
						));
						
						echo($form->getHtml());*/

						wdproOptionsForm(array(
							'title'=>'Настройки',
							'name'=>'options',
							'elements' => array(

								array(
									'type'=>'html',
									'htm'=>'<h2>Head</h2>',
								),

								array(
									'type'=>'text',
									'top'=>'Дополнительные мета-теги в head',
									'name'=>'wdpro_head_additional',
								),

								array(
									'type'=>'html',
									'html'=>'<h2>Компиляция (имеет смысл только на локальной машине)</h2>',
								),

								array(
									'type'=>'check',
									'name'=>'wdpro_compile_soy',
									'right'=>'Компилировать Soy шаблоны',
								),

								array(
									'type'=>'check',
									'name'=>'wdpro_compile_less',
									'right'=>'Компилировать Less в Css',
								),

								array(
									'type'=>'html',
									'html'=>'<h2>Убрать лишние кнопки из меню</h2>',
								),

								array(
									'name'=>'remove_edit-comments',
									'right'=>'Комментарии',
									'type'=>'checkbox',
								),

								array(
									'name'=>'remove_upload',
									'right'=>'Медиафайлы',
									'type'=>'checkbox',
								),

								array(
									'name'=>'remove_tools',
									'right'=>'Инструменты',
									'type'=>'checkbox',
								),

								array(
									'name'=>'remove_edit',
									'right'=>'Записи (блог)',
									'type'=>'checkbox',
								),

								array(
									'type'=>'html',
									'html'=>'<h2>СЕО</h2>',
								),

								array(
									'type'=>'html',
									'html'=>'<h3>Скрипты</h3>',
								),

								array(
									'name'=>'wdpro_scripts_to_footer',
									'right'=>'Переместить скрипты в футер',
									'type'=>'checkbox',
								),

								array(
									'name'=>'wdpro_scripts_to_footer_exclude_jquery',
									'right'=>'Оставить jQuery в header',
									'type'=>'checkbox',
								),

								array(
									'name'=>'wdpro_scripts_to_noindex',
									'right'=>'Переместить скрипты в noindex',
									'type'=>'checkbox',
								),

								array(
									'name'=>'wdpro_css_to_footer',
									'right'=>'Переместить Css в футер',
									'type'=>'checkbox',
								),

								/*array(
									'name'=>'wdpro_css_to_footer_w3tc',
									'right'=>'Переместить Css в футер (когда W3 Total Cache)',
									'type'=>'checkbox',
								),*/

								array(
									'type'=>'html',
									'html'=>'<h2>Другие</h2>',
								),

								array(
									'name'=>'wdpro_dev_mode',
									'right'=>'Включить модуль разработчика',
									'type'=>'checkbox',
								),

								array(
									'name'=>'wdpro_mail_antispam',
									'type'=>'checkbox',
									'right'=>'Включить защиту ящиков, размещенных на сайте от спама',
									'bottom'=>'Это имеет смысл, когда на сайте есть ящики, размещенные как ссылки. Например, <a href="mailto:info@'.str_replace('www.', '', $_SERVER['HTTP_HOST']).'">info@'.str_replace('www.', '', $_SERVER['HTTP_HOST']).'</a>. Чтобы потом спам боты не слали на эти ящики спам.',
								),

								array(
									'name'=>'wdpro_keep_standart_editor',
									'right'=>'Оставить стандартный редактор',
									'type'=>'checkbox',
								),

								array(
									'name'=>'wdpro_disable_revisions',
									'right'=>'Отключить ревизии',
									'type'=>'checkbox',
								),

								array(
									'name'  => 'wdpro_additional_remove',
									'right' => 'Убрать дополнительные поля для страниц и постов (метатеги)',
									'type'  => 'checkbox'
								),
								array(
									'name'=>'wdpro_remove_redirect_canonical',
									'right'=>'Убрать ошибку циклической переадресации ERR_TOO_MANY_REDIRECTS',
									'type'=>'checkbox',
								),
								array(
									'name'=>'wdpro_uncatenate_scripts',
									'right'=>'Выключить объединение скриптов',
									'bottom'=>'(Иногда 
									когда они объединяются, то у сервера не хватает 
									ресурсов, чтобы доделать это объединение и админка 
									выглядит не доделанной)',
									'type'=>'checkbox',
								),
								array(
									'name'=>'use_smilies',
									'right'=>'Использовать смайлики',
									'type'=>'check',
								),
								array(
									'name'=>'wdpro_send_errors_to_admins_emails',
									'right'=>'Отправлять сообщения об ошибках в 
									скриптах на почту',
									'type'=>'check',
								),

								array(
									'type' => 'html',
									'html' => '<h2>Mysql</h2>',
								),

								array(
									'type'=>'check',
									'name'=>'wdpro_sql_structure_drop_available',
									'top'=>'Удалять из таблиц поля, которые удалены из структуры таблиц, описанных в php классах таблиц',
									'bottom'=>'Это сделано на всякий случай. Так как структура таблиц меняется в зависимости от того, что описано в классах таблиц, можно там удалить что-то. От чего можно потерять данные. Имеет смысл включать это на локальной машине и отключать в интернете.',
								),

								array(
									'type' => 'submit',
									'text' => 'Сохранить',
									'class'=>WDPRO_BUTTON_CSS_CLASS,
								),
							),
						));
					}
				);
			}
		
		);	
	}
);
