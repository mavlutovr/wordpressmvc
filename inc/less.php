<?php



/**
 * Compile LESS to CSS
 *
 * @param string $lessFile name of LESS file
 * @param string $cssFile name of CSS file
 * @throws Exception
 */
function wdpro_less_compile($lessFile, $cssFile)
{
	require_once(__DIR__.'/../libs/less.php/Less.php');

	$parser = new Less_Parser();
	$parser->parseFile($lessFile);

	$css = $parser->getCss();
	file_put_contents($cssFile, $css);

	/*require_once(__DIR__.'/../libs/lessc.inc.php');
	$less = new lessc;
	$css = $less->compileFile($lessFile);
	file_put_contents($cssFile, $css);*/
}


/**
 * Compile LESS to CSS just if LESS file was changed
 *
 * @param string $lessFile name of LESS file
 * @param string $cssFile name of CSS file
 */
function wdpro_less_compile_try($lessFile, $cssFile)
{
	if (defined('WDPRO_LESS_COMPILE') && WDPRO_LESS_COMPILE) {
		$key = 'wdpro-less-('.wdpro_path_remove_wp_content($lessFile).')';
		$lastEditedTime = filemtime($lessFile);
		$lastCompiledTime = get_option($key);

		if ($lastEditedTime != $lastCompiledTime)
		{
			wdpro_less_compile($lessFile, $cssFile);
			update_option($key, $lastEditedTime);
		}
	}
}

wdpro_less_compile_try(
	__DIR__.'/../css/standart.less', 
	__DIR__.'/../css/standart.less.css'
);
wdpro_add_css_to_console(__DIR__.'/../css/standart.less.css');
wdpro_add_css_to_site(__DIR__.'/../css/standart.less.css');