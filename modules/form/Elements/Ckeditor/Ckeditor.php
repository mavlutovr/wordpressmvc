<?php
namespace Wdpro\Form\Elements\Ckeditor;

// Подключаем
if (is_admin())
{
	// Файлы подключаются в form.init.php
	/*wdpro_default_file(
		__DIR__.'/default/app.ckeditor.console.js', 
		__DIR__.'/../../../../../app/ckeditor.console.js'
	);
	wdpro_add_script_to_console( __DIR__.'/../../../../../app/ckeditor.console.js' );
	wdpro_add_script_to_console( __DIR__.'/ckeditor/ckeditor.js' );
	wdpro_add_script_to_console( __DIR__.'/console.js' );*/
	$_SESSION['admin_ok'] = true;
	//wdpro_add_css_to_console(__DIR__.'/')
	// <script src="//cdn.ckeditor.com/4.5.3/standard/ckeditor.js"></script>
	//wdpro_add_script_to_console('//cdn.ckeditor.com/4.5.3/standard/ckeditor.js' );
}

class Ckeditor extends \Wdpro\Form\Elements\Text
{
	
}

