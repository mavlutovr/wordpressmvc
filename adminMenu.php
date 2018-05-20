<?php

add_action(
	'wdpro-ready',
	function ()
	{
		add_action(
			'admin_menu',

			function () {

				// Options
				add_options_page(
					'Настройки Wordpress MVC',
					'Wordpress MVC',
					'administrator',
					'wdproOptions',
					function () {
						echo('<h2>WDPro</h2>');

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
									'name'=>'wdpro_keep_standart_editor',
									'right'=>'Оставить стандартный редактор',
									'type'=>'checkbox',
								),
								array(
									'name'  => 'wdpro_additional_remove',
									'right' => 'Убрать дополнительные поля для страниц и постов',
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
