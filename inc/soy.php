<?php

// Add Script
wdpro_add_script_to_console(__DIR__.'/../libs/closure-templates/soyutils.js');
wdpro_add_script_to_site(__DIR__.'/../libs/closure-templates/soyutils.js');


/**
 * Compile SOY template to JS file
 *
 * @param string $soyFile name of SOY template file
 * @param string $jsFile name of JS file
 * @throws Exception
 */
function wdpro_closure_compile($soyFile, $jsFile)
{
	$compilerFile = wdpro_separator_normalize(
		__DIR__ . '/../libs/closure-templates/SoyToJsSrcCompiler.jar'
	);

	$timeBefore = is_file($jsFile) ? filemtime($jsFile) : 0;

	$command = 'java -jar '
		. $compilerFile
		. ' --outputPathFormat ' .
		$jsFile
		. ' '
		. $soyFile;

	exec($command);

	clearstatcache(false, $jsFile);

	if ($timeBefore == filemtime($jsFile))
	{
		echo("Command: <BR>\n$command\n\n<BR><BR>\n\n");
		throw new Exception("SOY Error in "
			.$soyFile
			.". Run the below command in terminal to see errors");
	}
}


/**
 * Compile SOY template to JS file just if file was changed
 *
 * @param string $soyFile name of SOY template file
 * @param string $jsFile name of JS file
 */
function wdpro_closure_compile_try($soyFile, $jsFile)
{
	if (defined('WDPRO_SOY_COMPILE') && WDPRO_SOY_COMPILE) {

		$key = 'wdpro-soy-('.wdpro_path_remove_wp_content($soyFile).')';
		$lastEditedTime = filemtime($soyFile);
		$lastCompiledTime = get_option($key);

		if ($lastEditedTime != $lastCompiledTime)
		{
			update_option($key, $lastEditedTime);
			wdpro_closure_compile($soyFile, $jsFile);
		}
	}
}
