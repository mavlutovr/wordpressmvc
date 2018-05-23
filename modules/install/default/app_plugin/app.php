<?php
/*
 * Plugin Name: Wordpress MVC - Функционал сайта
 * Author: Roman Mavlutov
 */

// При готовности Wdpro
add_action('wdpro-ready', function ()
{
	// Добавляем пространство имен
	Wdpro\Autoload::add('App', __DIR__);

	// Добавляем модули
	// Другие страницы
	Wdpro\Modules::add(__DIR__.'/menu');
	// Меню сверху
	Wdpro\Modules::add(__DIR__.'/menu/top');

	// Стандартные модули
	// Контакты
	Wdpro\Modules::addWdpro('contacts');
	// Модуль разработчика
	Wdpro\Modules::addWdpro('dev');
});


