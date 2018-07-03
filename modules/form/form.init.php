<?php

Wdpro\Autoload::add('Wdpro\Form', __DIR__);

/**
 * Вывод формы для сохранения настроек
 *
 * @param $formParams
 */
function wdproOptionsForm($formParams)
{
	// Создание формы
	$form = new Wdpro\Form\Form($formParams);
	$form->setAction(wdpro_current_uri());

	// Сохранение
	$form->onSubmit(function ($data) {

		foreach($data as $key=>$value)
		{
			update_option($key, $value);
		}
	});

	// Загрузка уже ранее сохраненных данных для установки в форму
	$data = array();
	$form->eachElementsParams(function ($elementsParams) use (&$data)
	{
		if (isset($elementsParams['name']) && $elementsParams['name'])
		{
			$data[$elementsParams['name']] = get_option($elementsParams['name']);
		}
	});
	$form->setData($data);

	echo($form->getHtml());
}


// Ajax загрузка файла
add_action('wp_ajax_form_file_upload', function () {
	
	$retFiles = array(
		'files'=>array(),
	);
	
	foreach($_FILES as $fileData)
	{
		/*print_r($_REQUEST);*/
		//print_r($_FILES);
		
		ini_set('display_errors', 'on');
		error_reporting(7);
		ini_set('memory_limit', '512M');
		

		// Копируем файл во временную папку и архивируем его для безопасности
		$tmpDir = wdpro_upload_dir('temp');
		$fileName = wdpro_text_to_file_name(
			wdpro_ru_en(wdpro_basename($fileData['name']))
		);
		$n = 1;
		$zipFileName = $fileName.'_1.gz';
		while(is_file($tmpDir.$zipFileName))
		{
			$n ++;
			$zipFileName = $fileName.'_'.$n.'.gz';
		}

		// Создаем архив с файлом
		wdpro_gz_encode($fileData['tmp_name'], $tmpDir.$zipFileName);

		$retFiles['files'][] = $zipFileName;
	}
	

	// Возвращаем в браузер имя загруженного файла и имя архива
	wdpro_json_echo($retFiles);

	exit();
});


// Сразу подключаем Ckeditor к админке
if (is_admin()) {
	wdpro_default_file(
		__DIR__.'/Elements/Ckeditor/default/app.ckeditor.console.js',
		__DIR__.'/../../../app/ckeditor.console.js'
	);
	wdpro_add_script_to_console( __DIR__.'/../../../app/ckeditor.console.js' );
	wdpro_add_script_to_console( __DIR__.'/Elements/Ckeditor/ckeditor/ckeditor.js' );
	wdpro_add_script_to_console( __DIR__.'/Elements/Ckeditor/console.js' );
	$_SESSION['admin_ok'] = true;
}

