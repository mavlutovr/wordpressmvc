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
						echo('<p><img src="https://webdeveloper.pro/wp-content/plugins/wordpressmvc/logo.svg" class="wordpressmvc-logo" alt=""></p><hr><BR>');

						if (!is_dir(__DIR__.'/../app')) {
							if ($_GET['create_plugin_app']) {
								wdpro_copy(
									__DIR__.'/modules/install/default/app_plugin',
									__DIR__.'/../app',
									function ($fileName) {
										return str_replace('.php.html', '.php', $fileName);
									});


								echo('<p>Заготовка плагина-приложения создана. <a
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
								echo('<p>Заготовка темы создана. <a
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
									'html'=>'<h2>Head</h2>',
								),

								array(
									'type'=>'text',
									'top'=>'Дополнительные мета-теги в head',
									'bottom'=>'Например, чтобы подтвержать права на сайт в Вебмастере и других сервисах.',
									'name'=>'wdpro_head_additional',
									'width'=>600,
									'style'=>'max-width: calc(100vw - 100px);',
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
									'type' => 'submit',
									'text' => 'Сохранить',
									'class'=>WDPRO_BUTTON_CSS_CLASS,
								),


								array(
									'type'=>'html',
									'html'=>'<h2>reCaptcha3</h2>
											<p>Чтобы защитить формы от спама, зарегистрируйте сайт в <a href="https://www.google.com/recaptcha/intro/v3.html" target="_blank">Google reCAPTCHA</a></p>
											<p>А затем укажите:</p>',
								),

								array(
									'name'=>'wdpro_recaptcha3_site',
									'top'=>'Ключ сайта',
									'width'=>600,
									'style'=>'max-width: calc(100vw - 100px);',
								),

								array(
									'name'=>'wdpro_recaptcha3_secret',
									'top'=>'Секретный ключ',
									'width'=>600,
									'style'=>'max-width: calc(100vw - 100px);',
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
									'type' => 'submit',
									'text' => 'Сохранить',
									'class'=>WDPRO_BUTTON_CSS_CLASS,
								),


								array(
									'type'=>'html',
									'html'=>'<h2>СЕО</h2>',
								),

								array(
									'type'=>'html',
									'html'=>'<h2>Шаблоны пагинации</h2>',
								),

								array(
									'top'=>'Title',
									'name'=>'pagination_meta_title[lang]',
									'bottom'=>'[title] - page [page]',
								),

								array(
									'right'=>'Игнорировать шаблон, когда Title пустой',
									'name'=>'pagination_meta_title_skip_empty',
									'type'=>'check',
								),

								array(
									'top'=>'Description',
									'name'=>'pagination_meta_description[lang]',
									'bottom'=>'[description] - page [page]',
								),

								array(
									'right'=>'Игнорировать шаблон, когда Description пустой',
									'name'=>'pagination_meta_description_skip_empty',
									'type'=>'check',
								),

								array(
									'top'=>'H1',
									'name'=>'pagination_meta_h1[lang]',
									'bottom'=>'[h1] - page [page]',
								),

								array(
									'right'=>'Игнорировать шаблон, когда H1 пустой',
									'name'=>'pagination_meta_h1_skip_empty',
									'type'=>'check',
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
									'right'=>'Переместить Css в футер (чтобы поставить css в свое место, укажите в коде &lt;!-- cssPlace --&gt;)',
									'type'=>'checkbox',
								),

								array(
									'type' => 'submit',
									'text' => 'Сохранить',
									'class'=>WDPRO_BUTTON_CSS_CLASS,
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
								[
									'name'=>'wdpro_add_query_string_to_canonical',
									'right'=>'Добавить в canonical query string (?page=2)',
									'type'=>'checkbox',
								],
								/*array(
									'name'=>'wdpro_remove_redirect_canonical',
									'right'=>'Убрать ошибку циклической переадресации ERR_TOO_MANY_REDIRECTS',
									'type'=>'checkbox',
								),*/
								/*array(
									'name'=>'wdpro_uncatenate_scripts',
									'right'=>'Выключить объединение скриптов',
									'bottom'=>'(Иногда
									когда они объединяются, то у сервера не хватает
									ресурсов, чтобы доделать это объединение и админка
									выглядит не доделанной)',
									'type'=>'checkbox',
								),*/
								array(
									'name'=>'use_smilies',
									'right'=>'Использовать смайлики',
									'type'=>'check',
								),
								array(
									'name'=>'wdpro_standard_submenu',
									'right'=>'Add submenu to end of text (to add menu without this option use shortcode [submenu])',
									'type'=>'check',
								),
								array(
									'name'=>'wdpro_send_errors_to_admins_emails',
									'right'=>'Отправлять сообщения об ошибках в
									скриптах на почту',
									'type'=>'check',
								),
								array(
									'name'=>'wdpro_remove_link_rel_prev_and_next',
									'right'=>'Remove link rel=’prev’ and link rel=’next’',
									'type'=>'check',
								),

								array(
									'name'=>'wdpro_disable_counters_on_speed_test',
									'right'=>'Disable counters on speed test',
									'type'=>'check',
								),

								array(
									'type' => 'submit',
									'text' => 'Сохранить',
									'class'=>WDPRO_BUTTON_CSS_CLASS,
								),


								/*array(
									'type' => 'html',
									'html' => '<h2>Mysql</h2>',
								),

								array(
									'type'=>'check',
									'name'=>'wdpro_sql_structure_drop_available',
									'top'=>'Удалять из таблиц поля, которые удалены из структуры таблиц, описанных в php классах таблиц',
									'bottom'=>'Это сделано на всякий случай. Так как структура таблиц меняется в зависимости от того, что описано в классах таблиц, можно там удалить что-то. От чего можно потерять данные. Имеет смысл включать это на локальной машине и отключать в интернете.',
								),*/


								array(
									'type'=>'html',
									'html'=>'<h2>Безопасность</h2>',
								),

								array(
									'right'=>'Включить блокировку по IP',
									'type'=>'check',
									'name'=>'wdpro_secure_errors_block',
								),

								array(
									'right'=>'Количество ошибок безоспасности, после которых сайт блокируется для IP. Например, после ввода 3-х неправильных паролей.',
									'name'=>'wdpro_secure_errors_n',
									'center'=>'По-умолчанию: 3',
									'autoWidth'=>false,
									'width'=>150,
								),

								array(
									'right'=>'Время блокировки (сек)',
									'name'=>'wdpro_secure_errors_time',
									'center'=>'По-умолчанию: 60',
									'autoWidth'=>false,
									'width'=>150,
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
