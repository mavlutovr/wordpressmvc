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

	// App
	Wdpro\Modules::add(__DIR__.'/menu');
	Wdpro\Modules::add(__DIR__.'/menu/top');
	Wdpro\Modules::add(__DIR__.'/consoleRedirectAfterLogin');

	// Wdpro
	Wdpro\Modules::addWdpro('contacts');
	Wdpro\Modules::addWdpro('dev');
	Wdpro\Modules::addWdpro('extra/fontAwesome5');
	Wdpro\Modules::addWdpro('extra/robots');
	Wdpro\Modules::addWdpro('tools/metrika');
});
