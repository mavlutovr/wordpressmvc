<?php

/**
 * Заменяет "/" на "\" в адресе для windows при необходимости
 *
 * @param string $path Path
 * @return string
 */
function wdpro_separator_normalize($path)
{
	if (DIRECTORY_SEPARATOR != '/')
	{
		$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
	}

	return $path;
}


/**
 * Возвращает очищенный от ../ путь
 *
 * @param $path absolute path
 * @return string
 */
function wdpro_normalizePath($path)
{
	$parts = array();// Array to build a new path from the good parts
	$path = str_replace('\\', '/', $path);// Replace backslashes with forwardslashes
	$path = preg_replace('/\/+/', '/', $path);// Combine multiple slashes into a single slash
	$segments = explode('/', $path);// Collect path segments
	$test = '';// Initialize testing variable
	foreach($segments as $segment)
	{
		if($segment != '.')
		{
			$test = array_pop($parts);
			if(is_null($test))
				$parts[] = $segment;
			else if($segment == '..')
			{
				if($test == '..')
					$parts[] = $test;

				if($test == '..' || $test == '')
					$parts[] = $segment;
			}
			else
			{
				$parts[] = $test;
				$parts[] = $segment;
			}
		}
	}
	return wdpro_separator_normalize(implode('/', $parts));
}


/**
 * Convert absolute path to relative path from /wp-content/
 *
 * @param string $path absolute path
 * @return string
 */
function wdpro_path_remove_wp_content($path)
{
	return str_replace(realpath(__DIR__.'/../../../'), '', realpath($path));
}


/**
 * Convert absolute path to relative path from /
 *
 * @param string $path absolute path
 * @return string
 */
function wdpro_path_remove_root($path)
{
	$path =  str_replace(wdpro_realpath(__DIR__.'/../../../../'), '', wdpro_realpath($path));

	$path = wdpro_fix_directory_separator($path);

	return $path;
}


/**
 * Исправляет слеши на соответствующие текущей ОС
 *
 * Чтобы в виндовс нормально работали пути
 *
 * @param string $path Путь
 * @return string
 */
function wdpro_fix_directory_separator($path) {
	if (DIRECTORY_SEPARATOR == '\\') {
		$path = str_replace(
			'/',
			DIRECTORY_SEPARATOR,
			$path
		);
	}

	return $path;
}


/**
 * Convert absolute path to relative path from /
 *
 * @param string $path absolute path
 * @return string
 */
function wdpro_path_absolute_to_relative($path) {
	return wdpro_path_remove_root($path);
}


/**
 * Подключение JavaScript файла к консоли
 *
 * @param string      $absolutePath абсолютный путь к файлу
 * @param null|string $handle       Название скрипта в системе. Потом по этому имени
 *                                  можно делать всякие штуки, типа, поставить на
 *                                  добавление скрипта событие и сделать что-то еще
 *                                  после того, как скрипт уже добавлен.
 *                                  Наверное, работает так, я точно не разбирался.
 */
function wdpro_add_script_to_console($absolutePath, $handle=null)
{
	add_action( 'admin_enqueue_scripts', function () use ($absolutePath, $handle)
	{
		$file = wdpro_path_remove_root($absolutePath);
		$file = wdpro_fix_directory_separator_in_url($file);
		if (!$handle) $handle = $file;
		wp_enqueue_script( $handle, $file );
	});
}



/**
 * Исправляет слеши в url адресах
 *
 * @param string $url
 * @return string
 */
function wdpro_fix_directory_separator_in_url($url) {
	return str_replace('\\', '/', $url);
}


/**
 * Подключение JavaScript файла к консоли
 *
 * @param string $absolutePath абсолютный путь к файлу
 */
function wdpro_add_script_to_console_external($absolutePath)
{
	add_action( 'admin_enqueue_scripts', function () use ($absolutePath)
	{
		wp_enqueue_script( $absolutePath, $absolutePath );
	});
}


/**
 * Подключение JavaScript файла к сайту
 *
 * @param string $absolutePath абсолютный путь к файлу
 * @param null|string $handle       Название скрипта в системе. Потом по этому имени
 *                                  можно делать всякие штуки, типа, поставить на
 *                                  добавление скрипта событие и сделать что-то еще
 *                                  после того, как скрипт уже добавлен.
 *                                  Наверное, работает так, я точно не разбирался.
 */
function wdpro_add_script_to_site($absolutePath, $handle=null)
{
	add_action( 'wp_enqueue_scripts', function () use ($absolutePath, $handle)
	{
		if (is_file($absolutePath))
		{
			$file = wdpro_path_remove_root($absolutePath);
			$file = wdpro_fix_directory_separator_in_url($file);
			if (!$handle) $handle = $file;
			wp_enqueue_script( $handle, $file );
		}
	});
}


/**
 * Подключение JavaScript файла к сайту
 *
 * @param string $absolutePath абсолютный путь к файлу
 */
function wdpro_add_script_to_site_external($absolutePath)
{
	add_action( 'wp_enqueue_scripts', function () use ($absolutePath)
	{
		wp_enqueue_script( $absolutePath, $absolutePath );
	}, PHP_INT_MAX);
}


/**
 * Подключение уже зарегистированного скрипта
 *
 * @param string $registeredScriptName Имя зарегистрированного скрипта
 */
function wdpro_add_script_registered($registeredScriptName) {

	add_action( 'wp_enqueue_scripts', function () use ($registeredScriptName)
	{
		wp_enqueue_script( $registeredScriptName );
	});
}


/**
 * Подключение CSS файла к консоли
 *
 * @param string $absolutePath абсолютный путь к файлу
 */
function wdpro_add_css_to_console($absolutePath)
{
	add_action('admin_enqueue_scripts', function () use ($absolutePath)
	{
		$file = wdpro_path_remove_root($absolutePath);
		$file = wdpro_fix_directory_separator_in_url($file);
		wp_enqueue_style( $file, $file );
	});
}


/**
 * Подключение CSS файла к консоли
 *
 * @param string $absolutePath абсолютный путь к файлу
 */
function wdpro_add_css_to_console_external($absolutePath)
{
	add_action('admin_enqueue_scripts', function () use ($absolutePath)
	{
		wp_enqueue_style( $absolutePath, $absolutePath );
	});
}


/**
 * Подключение CSS файла к сайту
 *
 * @param string $absolutePath абсолютный путь к файлу
 */
function wdpro_add_css_to_site($absolutePath)
{
	add_action('wp_enqueue_scripts', function () use ($absolutePath)
	{
		$file = wdpro_path_remove_root($absolutePath);
		$file = wdpro_fix_directory_separator_in_url($file);
		wp_enqueue_style( $file, $file );
	});
}


/**
 * Подключение CSS файла к сайту
 *
 * @param string $absolutePath абсолютный путь к файлу
 */
function wdpro_add_css_to_site_external($absolutePath)
{
	add_action('wp_enqueue_scripts', function () use ($absolutePath)
	{
		wp_enqueue_style( $absolutePath, $absolutePath );
	});
}


/**
 * Добавление значений из массива 1 в массив 2 с заменой переменных
 *
 * @param array|null $arr1 Массив 1 (Параметры по-умолчанию)
 * @param array|null $arr2 Массив 2
 * @return array
 */
function wdpro_extend($arr1, $arr2)
{
	if ($arr1 === null)
	{
		if (!is_array($arr2))
		{
			return array();
		}

		return $arr2;
	}
	if ($arr2 === null || $arr2 === true)
	{
		if (!is_array($arr1))
		{
			return array();
		}

		return $arr1;
	}

	// Проверка на буквенный/числовой массив
	$assoc = false;
	foreach($arr2 as $key=>$value)
	{
		if (!is_numeric($key))
		{
			$assoc = true;
		}
	}
	if (!count($arr2)) {
		$assoc = true;
	}

	// Если это буквенный массив
	if ($assoc)
	{
		// Возвращаем обновленный первый массив
		// Перебираем элементы массива
		foreach($arr2 as $key=>$value)
		{
			// Заменяем значение
			if (is_array($value))
			{
				$arr1[$key] = wdpro_extend(
					isset($arr1[$key]) ? $arr1[$key] : null,
					isset($arr2[$key]) ? $arr2[$key] : null
				);
			}
			else
			{
				$arr1[$key] = $arr2[$key];
			}
		}

		return $arr1;
	}

	// Если это числовой массив
	else
	{
		// Возвращаем новый массив
		// Тут было так, что возвращался не новый массив, а значения нового добавлялись
		// к старому. Я сделал чтобы возвращался новый. Потому что получались лишние
		// данные при сохранении. Это было обнаружено в проекте tridodo.ru при
		// сохранении точек карты.
		return $arr2;
		/*foreach($arr2 as $value) {
			$arr1[] = $value;
		}
		return $arr1;*/
	}
}


/**
 * Удаление из массива элемента по значению
 *
 * @param array $array Массив
 * @param mixed $value Значение
 */
function wdpro_array_remove_by_value(&$array, $value) {

	if(($key = array_search($value,$array)) !== FALSE){
	     unset($array[$key]);
	}
}


/**
 * Заменяет в адресе query данные или добавляет их
 *
 * @param string $url URL
 * @param array $queryParams Параметры
 * @return string
 */
function wdpro_replace_query_params_in_url($url, $queryParams) {
	$arr = parse_url($url);

	$new = array();

	$get = [];
	if (isset($arr['query'])) {
		parse_str($arr['query'], $get);
	}


	if (is_array($get)) {
		foreach($get as $key=>$value)
		{
			if (array_key_exists($key, $queryParams))
			{
				if ($queryParams[$key])
				{
					$new[$key] = $queryParams[$key];
				}
			}

			else
			{
				$new[$key] = $get[$key];
			}

			unset($queryParams[$key]);
		}
	}

	if (is_array($queryParams)) {
		foreach($queryParams as $key=>$value)
		{
			$new[$key] = $value;
		}
	}

	$queryString = http_build_query($new);
	if ($queryString) $queryString = '?'.$queryString;

	return $arr['path'].$queryString;
}


/**
 * Возвращает относительный адрес текущей страницы
 *
 * @param null|array $queryChanges Изменить параметры QUERY_STRING согласно этому массиву
 * @return string
 */
function wdpro_current_uri($queryChanges=null)
{
	$uri = '';
	if (isset($_SERVER['REQUEST_URI_ORIGINAL'])) $uri = $_SERVER['REQUEST_URI_ORIGINAL'];
	if (!$uri) $uri = $_SERVER['REQUEST_URI'];

	if ($queryChanges)
	{
		$uri = wdpro_replace_query_params_in_url($uri, $queryChanges);
	}

	return $uri;
}


/**
 * Возвращает текущий путь страницы от домена до query_string
 *
 * @return string
 */
function wdpro_current_path() {
	$uri = '';
	if (isset($_SERVER['REQUEST_URI_ORIGINAL'])) $uri = $_SERVER['REQUEST_URI_ORIGINAL'];
	if (!$uri) $uri = $_SERVER['REQUEST_URI'];

	$parsedUri = parse_url($uri);
	return $parsedUri['path'];
}


/**
 * Возвращает имя поста (определяет по адресу, а не по get_post())
 *
 * @return string
 */
function wdpro_current_post_name() {
	$path = wdpro_current_path();
	$path = preg_replace('~^/(.*)$~', '$1', $path);

	return $path;
}


/**
 * Возвращает абсолютный адрес текущей страницы
 *
 * @param null|array $queryChanges Изменить параметры QUERY_STRING согласно этому массиву
 * @return string
 */
function wdpro_current_url($queryChanges=null) {
	$uri = wdpro_current_uri($queryChanges);

	return home_url().$uri;
}


/**
 * Проверяет, что это адрес текущей страниц (той, которая открыта)
 *
 * @param string $postName Проверяемый адрес
 * @return bool
 */
function wdpro_is_current_post_name($postName) {

	if ($post = get_post()) {

		return $postName == $post->post_name;
	}

	return false;
}


/**
 * Проверяет на соответствие тип текущей страницы
 *
 * @param string $postType Проверяемый тип
 * @return bool
 */
function wdpro_is_current_post_type($postType) {

	if ($post = get_post()) {
		return $postType == $post->post_type;
	}

	return false;
}


/**
 * True, если это локальная машина
 *
 * Это нужно для тог, чтобы например, для модуля переключателя языков.
 * Этот модуль использует для хранения информации файл. Так надо :)
 * И чтобы при копировании всех файлов на боевой сервер файл на сервере не затирался локальными
 * данными, на боевом сервере используется другой файл. И теперь можно хоть сколько копировать
 * файлы на сервер, данные на сервере не затрутся локальными.
 *
 * @return bool
 */
function wdpro_local() {
	return $_SERVER['HTTP_HOST'] == 'localhost'
			|| (defined('WDPRO_LOCALHOST') && WDPRO_LOCALHOST);
}


/**
 * Редирект с остановкой текущих скриптов
 *
 * @param string $location Адрес куда перейти
 */
function wdpro_location($location)
{
	if (headers_sent())
	{
		echo('<script>window.location = "'.$location.'";</script>');
	}
	else
	{
		header('Location: '.$location);
	}
	exit();
}


/**
 * Возвращает объект поста, при необходимости дождавшись его инициализации
 *
 * @param callback $callback Каллбэк, принимающий обхект поста
 */
function wdpro_get_post($callback) {

	add_action('wp', function () use (&$callback) {

		$callback(get_post());
	});
}


/**
 * Возвращает папку для загрузки файлов плагина wdpro
 *
 * отличается от вордпрессовского upload_dir тем, что возвращает папку именно для
 * данного плагина (файлы wdpro загружает в подпапку upload_dir().'/wdpro
 *
 * Если папки не существует, эта функция создает папку, при этом добавляет в папку файл
 * .htaccess для безопасности
 *
 * @param bool|false|string $subDir Поддиректория внутри основной папки файлов плагина
 * wdpro
 * @return string
 */
function wdpro_upload_dir($subDir=false)
{
	$dirs = wp_upload_dir();

	$dir = $dirs['basedir'] . DIRECTORY_SEPARATOR . 'wdpro';

	if ($subDir)
	{
		$dir .= DIRECTORY_SEPARATOR . $subDir;
	}

	wdpro_upload_dir_create($dir);

	$dir .= DIRECTORY_SEPARATOR;

	return $dir;
}


/**
 * Создает при необходимости папку для загрузки файлов и создает в ней .htaccess для
 * безопасности
 *
 * @param string $dir Папка
 */
function wdpro_upload_dir_create($dir)
{
	if (!is_dir($dir))
	{
		mkdir($dir, 0777, true);
		file_put_contents(
			$dir . DIRECTORY_SEPARATOR . '.htaccess',
			"php_flag engine 0
RemoveHandler .phtml .php .php3 .php4 .php5 .php6 .phps .cgi .exe .pl .asp .aspx .shtml .shtm .fcgi .fpl .jsp .htm .html .wml
AddType application/x-httpd-php-source .phtml .php .php3 .php4 .php5 .php6 .phps .cgi .exe .pl .asp .aspx .shtml .shtm .fcgi .fpl .jsp .htm .html .wml"
		);
	}
}


/**
 * Возвращает папку для загрузки файлов плагина wdpro
 *
 * @param bool|false|string $subDir Поддиректория внутри основной папки файлов плагина
 * wdpro
 * @return string
 */
function wdpro_upload_dir_url($subDir=false)
{
	$dirs = wp_upload_dir();

	$dir = $dirs['baseurl'] . '/' . 'wdpro';

	if ($subDir)
	{
		$dir .= '/' . $subDir;
	}

	if (!preg_match('~/$~', $dir)) {
		$dir .= '/';
	}

	return $dir;
}


/**
 * Возвращает папку для загрузки файлов плагина wdpro
 *
 * @param bool|false|string $subDir Поддиректория внутри основной папки файлов плагина
 * wdpro
 * @return string
 */
function wdpro_upload_dir_path($subDir=false)
{
	$dirs = wp_upload_dir();

	$dir = $dirs['basedir'] . '/' . 'wdpro';

	if ($subDir)
	{
		$dir .= '/' . $subDir;
	}

	if (!preg_match('~/$~', $dir)) {
		$dir .= '/';
	}

	return $dir;
}


/**
 * Архивирует файл в .gz архив
 *
 * @param string $sourceFile Адрес файла, которыя надо заархивировать
 * @param string $zipFile Адрес файла архива, куда заархивировать файл
 * @return bool trueпри удачном архивировании
 */
function wdpro_gz_encode($sourceFile, $zipFile)
{
	$data = file_get_contents($sourceFile);
	if ($gz_data = gzencode($data, 0))
	{
		$fp = fopen($zipFile, 'w');
		fwrite($fp, $gz_data);
		fclose($fp);
		return true;
	}
}


/**
 * Разорхивирует файл из формата .gz
 *
 * @param string $zipFile Адрес архива
 * @param string $targetFile Адрес, куда разархивировать файл
 * @return bool true, если удалось разархивировать
 */
function wdpro_gz_decode($zipFile, $targetFile)
{
	if (!@file_exists ($zipFile) || !@is_readable ($zipFile))
		return false;
	if ((!@file_exists ($targetFile) && !@is_writable (@dirname ($targetFile)) || (@file_exists($targetFile) && !@is_writable($targetFile)) ))
		return false;

	$zipFile_file = @gzopen ($zipFile, "rb");
	$targetFile_file = @fopen ($targetFile, "wb");

	while (!@gzeof ($zipFile_file)) {
		$buffer = @gzread ($zipFile_file, 4096);
		@fwrite ($targetFile_file, $buffer, 4096);
	}

	@gzclose ($zipFile_file);
	@fclose ($targetFile_file);

	return true;
}


/**
 * Преобразует данные в json строку
 *
 * @param array|mixed $data Данные
 * @return mixed|string|void
 */
function wdpro_json_encode($data)
{
	return json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}


/**
 * Преобразует json строку в данные
 *
 * @param string $jsonString json строка
 * @return array|mixed
 */
function wdpro_json_decode($jsonString)
{
	return json_decode($jsonString, true);
}


/**
 * Выводит в браузер данные в виде json строки
 *
 * @param array|mixed $data Данные
 */
function wdpro_json_echo($data)
{
	echo(wdpro_json_encode($data));
}


/**
 * Преобразует текст в имя файла
 *
 * @param string $text Текст
 * @param bool $toLowerCase Уменьшить заглавные буквы
 * @return mixed
 */
function wdpro_text_to_file_name($text, $toLowerCase=false)
{
	//$text = strtolower(ru_en($text));
	$text = (wdpro_ru_en($text));
	//$text = str_replace(' ', '-', $text);
	$text = preg_replace("/[^a-яa-z0-9\/,\. \-_#:]/ui", "", $text);  // Удаляем лишние символы
	$text = preg_replace("/[,-]/ui", " ", $text);                // Заменяем на пробелы
	$text = preg_replace("/[\s]+/ui", "-", $text);                // Заменяем 1 и более

	if ($toLowerCase)
	{
		$text = mb_strtolower($text, 'utf-8');
	}

	return $text;
}


/**
 * Перевод русских букв в аднгийскую транскрипцию
 *
 * @param string $text Строка с русскими буквами
 * @return string Строка с английскими буквами
 */
function wdpro_ru_en($text)
{
	$text=str_replace("а", "a", $text); 	$text=str_replace("А", "A", $text);
	$text=str_replace("б", "b", $text); 	$text=str_replace("Б", "B", $text);
	$text=str_replace("в", "v", $text); 	$text=str_replace("В", "V", $text);
	$text=str_replace("г", "g", $text); 	$text=str_replace("Г", "G", $text);
	$text=str_replace("д", "d", $text); 	$text=str_replace("Д", "D", $text);
	$text=str_replace("е", "e", $text); 	$text=str_replace("Е", "E", $text);
	$text=str_replace("ё", "jo", $text); 	$text=str_replace("Ё", "JO", $text);
	$text=str_replace("ж", "zh", $text); 	$text=str_replace("Ж", "ZH", $text);
	$text=str_replace("з", "z", $text); 	$text=str_replace("З", "Z", $text);
	$text=str_replace("и", "i", $text); 	$text=str_replace("И", "I", $text);
	$text=str_replace("й", "j", $text); 	$text=str_replace("Й", "J", $text);
	$text=str_replace("к", "k", $text); 	$text=str_replace("К", "K", $text);
	$text=str_replace("л", "l", $text); 	$text=str_replace("Л", "L", $text);
	$text=str_replace("м", "m", $text); 	$text=str_replace("М", "M", $text);
	$text=str_replace("н", "n", $text); 	$text=str_replace("Н", "N", $text);
	$text=str_replace("о", "o", $text); 	$text=str_replace("О", "O", $text);
	$text=str_replace("п", "p", $text); 	$text=str_replace("П", "P", $text);
	$text=str_replace("р", "r", $text); 	$text=str_replace("Р", "R", $text);
	$text=str_replace("с", "s", $text); 	$text=str_replace("С", "S", $text);
	$text=str_replace("т", "t", $text); 	$text=str_replace("Т", "T", $text);
	$text=str_replace("у", "u", $text); 	$text=str_replace("У", "U", $text);
	$text=str_replace("ф", "f", $text); 	$text=str_replace("Ф", "F", $text);
	$text=str_replace("х", "h", $text); 	$text=str_replace("Х", "H", $text);
	$text=str_replace("ц", "c", $text); 	$text=str_replace("Ц", "C", $text);
	$text=str_replace("ч", "ch", $text); 	$text=str_replace("Ч", "CH", $text);
	$text=str_replace("ш", "sh", $text); 	$text=str_replace("Ш", "SH", $text);
	$text=str_replace("щ", "shh", $text); 	$text=str_replace("Щ", "SHH", $text);
	$text=str_replace("э", "eh", $text); 	$text=str_replace("Э", "EH", $text);
	$text=str_replace("ю", "yu", $text); 	$text=str_replace("Ю", "YU", $text);
	$text=str_replace("я", "ya", $text); 	$text=str_replace("Я", "YA", $text);
	$text=str_replace("ы", "i", $text); 	$text=str_replace("Ы", "I", $text);
	$text=str_replace("ь", "j", $text); 	$text=str_replace("Ь", "j", $text);
	$text=str_replace("ъ", "j", $text); 	$text=str_replace("Ъ", "j", $text);
	//$text=str_replace(" ", "_", $text);

	// FR
	$text=str_replace('é', 'e', $text);		$text=str_replace('É', 'E', $text);
	$text=str_replace('è', 'e', $text);		$text=str_replace('È', 'E', $text);
	$text=str_replace('à', 'a', $text);		$text=str_replace('À', 'A', $text);
	$text=str_replace('ù', 'u', $text);		$text=str_replace('Ù', 'U', $text);
	$text=str_replace('ç', 'c', $text);		$text=str_replace('Ç', 'C', $text);
	// FR End

	return $text;
}


/**
 * Возвращает свободное имя файла в папке
 *
 * Когда например надо сохранить файл в папке не заменив при этом уже существующий
 * Функция меняет название файла, если такой файл уже есть
 *
 * @param $pathToFile
 * @return string
 */
function wdopro_file_free_name($pathToFile)
{
	$info = pathinfo($pathToFile);
	$fileName = $pathToFile;
	$n = 1;

	while(is_file($fileName))
	{
		$n ++;
		$fileName = $info['dirname'] . DIRECTORY_SEPARATOR
			. $info['filename'] . '_' . $n;
		if($info['extension'])
		{
			$fileName .= '.'.$info['extension'];
		}
	}

	return $fileName;
}


/**
 * Изменение размеров изображения
 *
 * @param string $originalImageFile Путь к файлу оригинального изображения
 * @param string $newImageFile Имя Путь к файлу нового уменьшенного изображения
 * @param array $params Параметры
 * array(
 *  'width'=>number,
 *  'height'=>number,
 *  'type'=>'crop',
 * )
 * @throws Exception
 */
function wdpro_image_resize($originalImageFile, $newImageFile, $params)
{
	$pathInfo = pathinfo($newImageFile);
	wdpro_upload_dir_create($pathInfo['dirname']);

	if ((isset($params['type']) && $params['type'] == 'crop')
		|| (isset($params['mode']) && $params['mode'] == 'crop')
	)
	{
		wdpro_image_resize_crop(
			$originalImageFile,
			$newImageFile,
			isset($params['width']) ? $params['width'] : null,
			isset($params['height']) ? $params['height'] : null
		);
	}
	else
	{
		wdpro_image_resize_rate(
			$originalImageFile,
			$newImageFile,
			isset($params['width']) ? $params['width'] : null,
			isset($params['height']) ? $params['height'] : null
		);
	}
}


/**
 * Наложение водяного знака на изображение
 *
 * @param string $fileFullName Адрес файла изображения
 * @param array $params Параметры
 * array('file'=>WDPRO_UPLOAD_IMAGES_PATH.'watermark.png', 'top|right|bottom|left'=>100)
 */
function wdpro_image_watermark($fileFullName, $params) {

	ini_set('display_errors', 'on');
	error_reporting(7);

	if (!isset($params['opacity'])) $params['opacity'] = 1;

	if (!isset($params['file'])) {
		$params['file']
			= WDPRO_UPLOAD_IMAGES_PATH.wdpro_get_option('wdpro_watermark');
	}

	// Сохранение оригинального рисунка
	if ($params['original']) {
		$originalPath = pathinfo($fileFullName);

		wdpro_copy_file($fileFullName,
			$originalPath['dirname']
			. '/' . $params['original']
			. '/' . $originalPath['basename']);
	}

	if ($fileFullName && is_file($fileFullName) && is_file($params['file'])) {

		// Imagick
		if (class_exists('Imagick')) {
			// Водяной знак
			$watermark = new Imagick($params['file']);
			if ($params['opacity']) {
				$watermark->setImageOpacity($params['opacity']);
			}

			// Оригинальное изображение
			$image = new Imagick($fileFullName);

			// Размеры изображений
			$sizeBig = $image->getImageGeometry();
			$sizeSmall = $watermark->getImageGeometry();

			// Расположение
			// Центр (по-умолчанию)
			$x = round($sizeBig['width'] / 2 - $sizeSmall['width'] / 2);
			$y = round($sizeBig['height'] / 2 - $sizeSmall['height'] / 2);

			// Справа
			if (isset($params['right']) && $params['right']) {
				$x = $sizeBig['width'] - $sizeSmall['width'] - $params['right'];
			}

			// Слева
			if (isset($params['left']) && $params['left']) {
				$x = $params['left'];
			}

			// Сверху
			if (isset($params['top']) && $params['top']) {
				$y = $params['top'];
			}

			// Снизу
			if (isset($params['bottom']) && $params['bottom']) {
				$y = $sizeBig['height'] - $sizeSmall['height'] -
					$params['bottom'];
			}

			// Накладываем изображение
			$image->compositeImage($watermark, Imagick::COMPOSITE_DEFAULT, $x, $y);
			//$image->flattenImages();
			//$image->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
			//$image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
			//$image->setImageCompressionQuality(90);

			@unlink($fileFullName);

			$image->writeImage($fileFullName);
			// $image->writeImage('test.jpg');
		}

		// GD
		else {
			// Картинка
			$img = wdpro_gd_create_image($fileFullName);
			/*imagealphablending($img, false);
			imagesavealpha($img,true);*/

			// Водяной знак
			$watermark = wdpro_gd_create_image($params['file']);
			/*imagealphablending($watermark, false);
			imagesavealpha($watermark,true);*/

			// Размеры
			$imgSize = [
				'width'=>imagesx($img),
				'height'=>imagesy($img),
			];
			$watermarkSize = [
				'width'=>imagesx($watermark),
				'height'=>imagesy($watermark),
			];

			// Отступы
			if (isset($params['right']) && $params['right']) {
				$params['left'] =
					$imgSize['width'] - $watermarkSize['width'] - $params['right'];
			}
			if (isset($params['bottom']) && $params['bottom']) {
				$params['top'] =
					$imgSize['height'] - $watermarkSize['height'] - $params['bottom'];
			}
			if (!isset($params['left'])) $params['left'] = 0;
			if (!isset($params['top'])) $params['top'] = 0;

			// Наложение водяного знака

			// Здесь почему-то появляется черный квадрат у водяного знака
			/*imagecopymerge(
				$img,
				$watermark,
				$params['left'],
				$params['top'],
				0,
				0,
				$watermarkSize['width'],
				$watermarkSize['height'],
				50);*/
			imagecopy(
				$img,
				$watermark,
				$params['left'],
				$params['top'],
				0,
				0,
				$watermarkSize['width'],
				$watermarkSize['height']);

			// Сохранение
			wdpro_gd_save_image($fileFullName, $img);
		}
	}
}


/**
 * Возвращает GD ресурс картинки
 *
 * @param string $imageFile Путь к картинке
 * @return resource an image resource identifier on success, false on errors.
 * @throws Exception
 */
function wdpro_gd_create_image($imageFile) {
	// Размеры изображения
	$size = getimagesize($imageFile);

	// Создаем изображение
	$format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
	//$format = str_replace('jpeg', 'jpg', $format);
	$icfunc = "imagecreatefrom" . $format;
	if (!function_exists($icfunc))
	{
		//throw new Exception('Нет функции '.$icfunc);
	}
	return $icfunc($imageFile);
}


/**
 * Сохранение GD изображения
 *
 * @param string $imgFile Имя файла изображения
 * @param resource $imgResource Ресурс изображения
 */
function wdpro_gd_save_image($imgFile, $imgResource) {

	// Создаем изображение
	$size = getimagesize($imgFile);
	$format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));

	if ($format == 'png') {
		imagepng($imgResource, $imgFile, 9, PNG_ALL_FILTERS);
	}
	else {
		imagejpeg($imgResource, $imgFile, 90);
	}
}


/**
 * Пропорциональное уменьшение размеров изображения
 *
 * @param string $originalImageFile Адрес файла изображения
 * @param string $newImageFile Адрес куда скопировать уменьшенное изображение
 * @param int $width Ширина до которой уменьшить рисунок
 * @param int $height Высота до которой уменьшить рисунок
 * @param int $rgb ?
 * @param int $quality Качество нового рисунка (JPG)
 * @return bool
 * @throws Exception
 */
function wdpro_image_resize_rate($originalImageFile, $newImageFile, $width, $height=null, $rgb=0xFFFFFF, $quality=89)
{
	if ($height==null) { $height=10000; }
	if (!$width) { $width=10000; }

	// Imagick
	if (class_exists('Imagick'))
	{
		$img = new Imagick(realpath($originalImageFile));
		if ($img->getimagewidth() > $width || ($height != null && $img->getimageheight() > $height))
		{
			$img->thumbnailimage(ceil($width), ceil($height), true);
		}
		$img->writeimage(wdpro_realpath($newImageFile));
	}

	// Gd
	else
	{
		if ($height==null) { $height=10000; }
		$size = getimagesize($originalImageFile);
		$format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
		//$format = str_replace('jpeg', 'jpg', $format);
		$icfunc = "imagecreatefrom" . $format;
		if (!function_exists($icfunc))
		{
			//throw new Exception('Нет функции '.$icfunc);
		}
		if (($width<$size[0])||($height<$size[1]))
		{
			///////////////////////////////////////////////// SIZE EDIT
			$x_ratio_edit = $width / $size[0];
			$y_ratio_edit = $height / $size[1];
			$ratio_edit   = min($x_ratio_edit, $y_ratio_edit);
			if ($x_ratio_edit == $ratio_edit)
			{
				$height=$ratio_edit*$size[1];
			}
			else
			{
				$width=$ratio_edit*$size[0];
			}
			$resize=1;
		}
		else
		{
			$width=$size[0];
			$height=$size[1];
		}
		///////////////////////////////////////////////// SIZE EDIT END
		$x_ratio = $width / $size[0];
		$y_ratio = $height / $size[1];

		$ratio       = min($x_ratio, $y_ratio);
		$use_x_ratio = ($x_ratio == $ratio);

		$new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
		$new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
		$new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
		$new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);

		$isrc = $icfunc($originalImageFile);
		$idest = imagecreatetruecolor($width, $height);
		imagealphablending($idest, false);
		imagesavealpha($idest,true);
		$transparent = imagecolorallocatealpha($idest, 255, 255, 255, 127);
		imagefilledrectangle($idest, 0, 0, $width, $height, $transparent);
		//imagefill($idest, 0, 0, $rgb);
		imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
			$new_width, $new_height, $size[0], $size[1]);
		if ($format=='png')
		{
			imagepng($idest, $newImageFile);
		}
		else
		{
			imagejpeg($idest, $newImageFile, $quality);
		}
		imagedestroy($isrc);
		imagedestroy($idest);
	}
}


/**
 * Пропорциональное уменьшение размеров изображения с обрезкой длинной стороны,
 * чтобы оно стало ровно такого размера, как указано в параметрах
 *
 * @param string $originalImageFile Адрес файла изображения
 * @param string $newImageFile Адрес, куда скопировать новое изображение
 * @param int $crop_w Ширина нового изображения
 * @param int $crop_h Высота нового изображения
 * @param int $rgb ?
 * @param int $quality Качество нового рисунка (JPG)
 * @return bool
 * @throws Exception
 */
function wdpro_image_resize_crop($originalImageFile, $newImageFile, $crop_w, $crop_h, $rgb=0xFFFFFF, $quality=89)
{
	// Imagick
	if (class_exists('Imagick'))
	{
		//echo('realpath: '. wdpro_realpath(__DIR__.'/../lit/test.png')); exit();
		$img = new Imagick(realpath($originalImageFile));
		$img->cropthumbnailimage(ceil($crop_w), ceil($crop_h));
		$img->writeimage(wdpro_realpath($newImageFile));
	}


	// GD
	else
	{
		$size = getimagesize($originalImageFile);
		$format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
		//$format = str_replace('jpeg', 'jpg', $format);
		$icfunc = "imagecreatefrom" . $format;
		if (!function_exists($icfunc))
		{
			//throw new Exception('Нет функции '.$icfunc);
		}

		$big_w=$size[0];
		$big_h=$size[1];

		// Уменьшить фото
		if (($crop_w<$big_w)&&($crop_h<$big_h))
		{
			// обрезание высоты
			$n=$crop_w/$big_w;
			$new_h=ceil($big_h*$n);
			if ($new_h>=$crop_h)
			{
				wdpro_image_resize_rate($originalImageFile, $newImageFile, $crop_w);



				$img=$icfunc($newImageFile);
				$crop_left=0;
				$crop_top=ceil(($new_h-$crop_h)/2);
				$crop_size = getimagesize($newImageFile);
			}
			// обрезание высоты End

			// Обрезание ширины
			else
			{
				$n=$crop_h/$big_h;
				$new_w=ceil($big_w*$n);
				if ($new_w>=$crop_w)
				{
					wdpro_image_resize_rate($originalImageFile, $newImageFile, 10000, $crop_h);
					$img=$icfunc($newImageFile);
					$crop_top=0;
					$crop_left=ceil(($new_w-$crop_w)/2);
					$crop_size = getimagesize($newImageFile);
				}
			}
			// Обрезание ширины End

			$white_img=imagecreatetruecolor($crop_w, $crop_h);
			// Белые поля
			$color = imagecolorallocate($white_img,255,255,255);
			// imagecopy( $white_img, $img, 0, 0, -19.5, 40, 200.5, 160 )
			// imagecopy( $white_img, $img, 0, 0, 0, 0, 220, 120 );
			imagefill($white_img,0,0,$color);
			// Белые поля End
			//echo('imagecopy( $white_img, $img, 0, 0, '.$crop_left.', '.$crop_top.', '.($crop_size[0]-$crop_left).', '.($crop_size[1]-$crop_top).' );');
			imagecopy( $white_img, $img, 0, 0, ceil($crop_left), ceil($crop_top), ceil($crop_size[0]-$crop_left), ceil($crop_size[1]-$crop_top) );
			// Белые поля
			$while_left=($crop_w-$crop_size[0])/2-1;
			if ($while_left>0)
			{
				$color2 = imagecolorallocate($white_img,255,255,255);
				imagefilledrectangle($white_img,0,0,$while_left,$crop_h,$color2);
			}
			$white_top=($crop_h-$crop_size[1])/2-1;
			if ($white_top>0)
			{
				$color3 = imagecolorallocate($white_img,255,255,255);
				imagefilledrectangle($white_img,0,0,$crop_w,$white_top,$color3);
			}
			// Белые поля End
			imagejpeg($white_img, $newImageFile, $quality);
			imagedestroy($img);
			imagedestroy($white_img);
		}
		// Уменьшить фото End

		if (!isset($img))
		{
			copy($originalImageFile, $newImageFile);
			/*$img=$icfunc($iz);
			$crop_left=ceil($big_w-$crop_w)/2;
			$crop_top=ceil($big_h-$crop_h)/2;
			$crop_size = $size;*/
		}
	}
}


/**
 * Возвращает полный путь к файлу, удаляя переходы типа ../ (аналог basename)
 *
 * Отличается от basename тем, что возвращает путь к файлу даже тогда, когда файла не
 * существует
 *
 * @param $path
 * @return string
 */
function wdpro_realpath($path) {

	$path = wdpro_fix_directory_separator($path);

	$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
	$parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
	$absolutes = array();
	foreach ($parts as $part) {
		if ('.' == $part) continue;
		if ('..' == $part) {
			array_pop($absolutes);
		} else {
			$absolutes[] = $part;
		}
	}
	$path = implode(DIRECTORY_SEPARATOR, $absolutes);

	if (DIRECTORY_SEPARATOR === '/') {
		$path = '/'.$path;
	}

	return $path;

}


/**
 * Возвращает имя файла из полного адреса
 *
 * Отличается от стандартного basename тем, что возвращает имя файла даже, когда оно с
 * русскими буквами
 *
 * @param string $filePath Полный адрес файла
 * @return string
 */
function wdpro_basename($filePath)
{
	$arr = explode(DIRECTORY_SEPARATOR, $filePath);
	$basename = end($arr);
	return $basename;
}



$_wdproObjects = array();

function wdproObjectsTrace() {
	global $_wdproObjects;
	print_r($_wdproObjects);
}


/**
 * Добавляет объект в список объектов
 *
 * Для быстрого доступа к нему через wdpro_object(...)
 *
 * @param $object \Wdpro\BaseEntity
 * @param int $key
 */
function wdpro_object_add_to_cache($object, $key=-1) {
	global $_wdproObjects;

	$className = '\\' . get_class($object);

	if ($key === -1)
	$key = $object->id();

	if (!$key) {
		$key = 0;
	}


	if (!isset($_wdproObjects[$className]))
	{
		$_wdproObjects[$className] = array();
	}

	if (!isset($_wdproObjects[$className][$key]))
	{
		$_wdproObjects[$className][$key] = $object;
	}
}


/**
 * Удаляет объект из списка объектов для быстрого доступа
 *
 * @param $object \Wdpro\BaseEntity
 */
function wdpro_object_remove_from_cache($object) {
	global $_wdproObjects;
	$className = '\\' . get_class($object);

	$key = $object->id();

	if (!$key) {
		$key = 0;
	}

	unset($_wdproObjects[$className][$key]);

}


/**
 * Возвращает объект, создавая его, если он еще не был создан
 *
 * @param string $className Имя класса
 * @param null|int|string|array $dataOrId ID или данные объекта
 * @return void|object
 */
function wdpro_object($className, $dataOrId=null)
{
	global $_wdproObjects;

	if ($dataOrId === null)
	{
		$dataOrId = 'null';
	}

	if (is_array($dataOrId))
	{
		if (isset($dataOrId['ID'])) $key = $dataOrId['ID'];
		else if (isset($dataOrId['id'])) $key = $dataOrId['id'];
		else $key = null;
	}
	else
	{
		$key = $dataOrId;
	}

	if (!$key) {
		$key = 0;
	}

	if (substr($className, 0, 1) != '\\')
		$className = '\\'.$className;

	if (is_string($className))
	{
		/*if (!isset($_wdproObjects[$className]))
		{
			$_wdproObjects[$className] = array();
		}

		if (!isset($_wdproObjects[$className][$key]))
		{
			$_wdproObjects[$className][$key] = new $className($dataOrId);
		}*/

		wdpro_object_add_to_cache(new $className($dataOrId), $key);

		return $_wdproObjects[$className][$key];
	}
	else
	{
		echo('<pre>Ошибка:<BR>');
		print_r(debug_backtrace());
		echo('</pre>');
		throw new Exception('Здесь нужно имя класса в виде строки, вместо строки получено '.print_r($className, 1));
	}
}


/**
 * Возвращает объект, не создавая его, только загружает из базы
 *
 * @param string $className Имя класса
 * @param null|int|string|array $dataOrId ID или данные объекта
 * @return void|object
 * @throws Exception
 */
function wdpro_load_object($className, $dataOrId=null) {

	$obj = wdpro_object($className, $dataOrId);

	if ($obj->loaded()) {
		return $obj;
	}
}


/**
 * Возвращает объект страницы по ее id
 *
 * @param int $postId ID страницы
 * @return void|\Wdpro\BasePage
 * @throws Exception
 */
function wdpro_object_by_post_id($postId)
{
	$postType = get_post_field('post_type', $postId);

	if ($class = wdpro_get_class_by_post_type($postType))
	{
		return wdpro_object($class, $postId);
	}
}


/**
 * Возвращает объект страницы (аналогично wdpro_object_by_post_id())
 *
 * @param int $postId ID страницы
 * @return void|\Wdpro\BasePage
 */
function wdpro_get_post_by_id($postId) {

	return wdpro_object_by_post_id($postId);
}


/**
 * Возвращает обхект страницы по ее URI
 *
 * @param string $postName URI страницы
 * @return \Wdpro\BasePage
 */
function wdpro_get_post_by_name($postName) {

	return \Wdpro\Page\Controller::getByPostByName($postName);
}


/**
 * Возвращает объект по его ключу, который ранее был получен от объекта с помощью
 * метода getKey()
 *
 * @param string $objectKey Ключ объекта, который ранее был получен из объекта с помощью
 * getKey()
 * @return object|void
 * @throws Exception
 */
function wdpro_object_by_key($objectKey) {

	$key = wdpro_key_parse($objectKey);

	$obj = wdpro_object($key['object']['name'], isset($key['object']['id']) ? $key['object']['id'] : null);

	if (is_object($obj))
	{
		if (method_exists($obj, 'setKey'))
		{
			$obj->setKey($key);
		}

		return $obj;
	}
}


/**
 * Обрабатывает ключ объекта
 *
 * @param null|array $key Ключь
 * @return array
 */
function wdpro_key_parse($key=null)
{

	// Ключь в виде массива
	if (is_array($key))
	{
		// Если ключь не задан, берем его из текущего объекта
		// Ключь уже обработан
		if (isset($key['allReady']) && $key['allReady'])
		{
			return $key;
		}


		// Создаем массив ключа
		$infoArr = $key;

		// Создаем строку ключа
		$infoString = '';

		// Перебираем массив
		foreach ($key as $i => $value)
		{
			if ($infoString != '')
			{
				$infoString .= ',';
			}
			$infoString .= $i . ':' . wdpro_key_escape($value);
		}
	}


	// Ключь в виде строки
	else {
		if (is_string($key))
		{
			$key = str_replace('\\\\', '\\', $key);

		// Создаем строку ключа
			$infoString = $key;

			// Создаем массив ключа
			$infoArr = array();

			// Разбиваем строку
			$elements = explode(',', $infoString);

			// Перебираем элементы строки
			foreach ($elements as $element)
			{
				// Делим элемент на ключь и значение
				$elementParts = explode(':', $element);

				// Добавляем часть в массив
				$infoArr[$elementParts[0]] = wdpro_key_unescape($elementParts[1]);
			}
		}
	}

	//$infoArr['name'] = str_replace('\\\\', '\\', $infoArr['name']);
	//$infoString = str_replace('\\\\', '\\', $infoString);

	return array(
		'key'    => $infoString,
		'object' => $infoArr,
		'allReady'=>true,
	);
}


/**
 * Добавляет значение в ключ
 *
 * @param string|array $key Ключь
 * @param array $values
 * @return array
 */
function wdpro_key_add_values($key, $values) {
	$key = wdpro_key_parse($key);

	if (is_array($values)) {
		foreach ($values as $newKey => $newValue) {
			//$key['key'] .= ','.$newKey.':'.$newValue;
			$key['object'][$newKey] = wdpro_key_escape($newValue);
		}
	}

	$key = wdpro_key_parse($key['object']);

	return $key;
}


/**
 * Экранирование значения ключа
 *
 * Чтобы один ключ можно было вставлять в другой
 *
 * @param string $value Значение ключа в виде строки
 * @return string
 */
function wdpro_key_escape($value) {
	$value = str_replace(',', '&', $value);
	$value = str_replace(':', '=', $value);
	return $value;
}


/**
 * Разэкронирование значение ключа
 *
 * @param string $value Значение ключа в виде строки
 * @return string
 */
function wdpro_key_unescape($value) {
	$value = str_replace('&', ',', $value);
	$value = str_replace('=', ':', $value);
	return $value;
}



$_wdproPostClassesByPostType = array();

/**
 * Возвращает класс страницы по типу поста
 *
 * @param string $postType Тип поста
 * @deprecated
 * @see wdpro_get_entity_by_post_type
 * @return string|void
 */
function wdpro_get_class_by_post_type($postType)
{
	return wdpro_get_entity_class_by_post_type($postType);
}


/**
 * Возвращает класс страницы по типу поста
 *
 * @param string $postType Тип поста
 * @return \App\BasePage
 */
function wdpro_get_entity_class_by_post_type($postType) {
	global $wdpro_register_post_type;

	if (isset($wdpro_register_post_type[$postType])
		&& $wdpro_register_post_type[$postType])
	{
		return $wdpro_register_post_type[$postType];
	}
}


/**
 * Запоминает класс страницы для заданного типа поста
 *
 * @param \Wdpro\BasePage $entityClass Класс сущности
 */
function wdpro_register_post_type($entityClass)
{
	global $wdpro_register_post_type;

	$postType = $entityClass::getType();

	$wdpro_register_post_type[$postType] = $entityClass;
}


function wdpro_get_controller_by_post_type($postType) {
	if ($entityClass = wdpro_get_entity_by_post_type($postType)) {
		$entityClass::getNameSpace();
	}
}

/**
 * Возвращает типы постов
 *
 * @return array
 */
function wdpro_get_post_types()
{
	global $wdpro_register_post_type;

	return array_keys($wdpro_register_post_type);
}


/**
 * Проверяет, есть ли такой тип записи в Wdpro
 *
 * Например, для обычных страниц может не быть типа записей в Wdpro
 *
 * @param string $type Тип
 * @return bool
 */
function wdpro_is_post_type($type)
{
	global $wdpro_register_post_type;
	if (isset($wdpro_register_post_type[$type])) {
		return true;
	}
	return false;
}


/**
 * Инициализация списка
 *
 * @param string $rollClass Класс списка
 * @throws Exception
 */
function wdpro_init_roll($rollClass)
{
	wdpro_object($rollClass)->init();
}


/**
 * Ловит ajax запрос
 *
 * @param string $actionName Событие
 * @param callback $callback Каллбэк, принимающий запрос
 * @return bool
 */
function wdpro_ajax($actionName, $callback)
{
	// Когда событие ajax уже было обработано
	if (defined('DOING_AJAX') && DOING_AJAX) {

		if (isset($_GET['action']) && $_GET['action'] === 'wdpro')
		{

			add_action('init', function () use (&$actionName, &$callback) {

				if (isset($_GET['lang'])) {
					\Wdpro\Lang\Controller::setCurrentLang($_GET['lang']);
				}
				$wdproAction = $_GET['wdproAction'] ? $_GET['wdproAction'] : '';
				if ($wdproAction == $actionName) {
					$result = $callback($_GET);
					echo(wdpro_json_encode($result));
					exit();
				}
			});
		}

		return false;
	}


	// Когда события еще не были
	add_action('wdpro-ajax-'.$actionName, function () use (&$callback) {

		$result = $callback($_GET);

		echo(wdpro_json_encode($result));
		exit();
	});
}


/**
 * Отправляет письма точно так же как и wp_mail, только в формате html
 *
 * @param string|string[] $to E-mail получателя
 * @param string|null $subject Тема
 * @param string|null $messageInHtmlFormat Текст в формате html
 * @param string|string[]|null $headers Заголовки
 * @param string|string[]|null $attachments Прикрепляемые файлы (полные пути до файлов)
 * @return bool
 */
function wdpro_mail( $to,
	$subject = null,
	$messageInHtmlFormat = null,
	$headers = null,
	$attachments = null,
	$from=null
)
{

	if (is_array( $headers )) {
		$headers[] = 'content-type: text/html';
	}
	if ($headers === null)
		$headers = '';
	$headers .= 'content-type: text/html' . "\r\n";

	if  ($from) {
		$subject = '::SMTP_FROM_START::'.$from.'::SMTP_FROM_END::'.$subject;
	}

	return wp_mail( $to, $subject, $messageInHtmlFormat, $headers, $attachments );
}


/**
 * Выводит дату с русскими месяцами
 *
 * http://rche.ru/888_php-date-vyvod-russkogo-mesyaca.html
 *
 * @param string $param Формат
 * @param int $time
 * @return bool|string
 */
function wdpro_rdate($param, $time=0) {
	if(intval($time)==0)$time=time();

	if(strpos($param,'Month')===false) {
		return date($param, $time);
	}
	else {
		$param = str_replace('Month', '{{{---}}}', $param);
		$month = wdpro_get_month($time);
		$date = date($param, $time);

		return str_replace('{{{---}}}', $month, $date);
	}
}


/**
 * Возвращает месяц указанного времени
 *
 * @param int $time Время
 *
 * @return string
 */
function wdpro_get_month($time) {

	return \Wdpro\Lang\Dictionary::get('month_' . date('m',$time));
}


/**
 * Преобразует секунды в отображаемую дату
 *
 * @param int $time Секунды от какого-то там 1970 года
 * @param null|array $params Параметры
 * @return string
 */
function wdpro_date($time, $params=null)
{
	$params = wdpro_extend(array(
		'year'=>false,
		'today'=>true,
		'time'=>false,
		'dateFormat'=>'d Month Y',
		'timeFormat'=>', H:i',
	), $params);

	$date = null;
	if ($params['today'])
	{
		if (date('Y.m.d') == date('Y.m.d', $time))
		{
			$date = 'Сегодня';
		}
		else if (date('Y.m.d') == date('Y.m.d', $time + WDPRO_DAY))
		{
			$date = 'Вчера';
		}
		else if (date('Y.m.d') == date('Y.m.d', $time - WDPRO_DAY))
		{
			$date = 'Завтра';
		}
	}

	if (!$date)
	{
		$date = wdpro_rdate($params['dateFormat'], $time);
	}

	if ($params['time']) {

		$date .= wdpro_rdate($params['timeFormat'], $time);
	}

	return $date;
}


/**
 * Редактирование текста страницы (добавление в текст дополнительного содержания)
 *
 * @param callback $callback Каллбэк, которые получает контент и который должен вернуть
 * измененный контент
 * @param int $priority Приоритет
 */
function wdpro_content($callback, $priority=10) {

	add_filter('the_content', function ($content) use (&$callback) {

		return $callback($content, get_post());
	}, $priority);
}


/**
 * Запускает каллбэк при появлении контента и отправляем в каллбэк обхект страницы
 * \Wdpro\BasePage
 *
 * @param callback $callback Каллбэк function($content, $postWdpro)
 * @param int $priority Приоритет
 */
function wdpro_on_content($callback, $priority=10) {

	add_filter('the_content', function ($content) use (&$callback) {

		if ($return = $callback($content, wdpro_current_page())) {

			return $return;
		}

		return $content;

	}, $priority);
}


/**
 * Запускает каллбэк при появлении контента и отправляет в каллбэк объект страницы
 * \Wdpro\BasePage
 *
 * @param string   $pageType Тип страницы \Wdpro\BasePage::getType()
 * @param callback $callback Каллбэк
 * @param int      $priority Приоритет
 */
function wdpro_on_content_of_page_type($pageType, $callback, $priority=10) {

	add_filter('the_content', function ($content) use (&$callback, &$pageType) {

		$page = wdpro_current_page();
		if ($page::getType() == $pageType) {
			if ($return = $callback($content, $page)) {

				return $return;
			}

		}
		return $content;

	}, $priority);
}


/**
 * Выводит $data в браузер как print_r, только в нормальном виде без простотра
 * исходного кода, сразу на странице
 *
 * @param array|* $data Данные, которые надо отобразить
 * @param bool $exit Завершить сценарий на этом
 * @param int $backtraceLimit Количество точек пути скрипта, отображаемых перед выводом
 * данных
 */
function wdpro_print_r($data, $exit=false, $backtraceLimit=1) {

	echo('<pre style="white-space: pre-wrap;">');
	$backtraces = debug_backtrace(null, $backtraceLimit);
	foreach ($backtraces as $n=>$backtrace) {
		echo '<span style="opacity: 0.5;">'
			.wdpro_path_absolute_to_relative($backtrace['file'])
			. ' - '
			. $backtrace['line'].PHP_EOL
			.'</span>';
	}
	print_r($data);
	echo('</pre>');

	if ($exit) exit();
}


/**
 * Как и wdpro_print_r, но срабатываем только когда в адресе есть ?test=1
 *
 * Выводит $data в браузер как print_r, только в нормальном виде без простотра
 * исходного кода, сразу на странице
 *
 * @param array|* $data Данные, которые надо отобразить
 * @param bool $exit Завершить сценарий на этом
 * @param int $backtraceLimit Количество точек пути скрипта, отображаемых перед выводом
 * данных
 */
function wdpro_TEST($data, $exit=false, $backtraceLimit=1) {
	if (!empty($_GET['test'])) {
		echo('<pre style="white-space: pre-wrap;">');
		$backtraces = debug_backtrace(null, $backtraceLimit);
		foreach ($backtraces as $n=>$backtrace) {
			echo '<span style="opacity: 0.5;">'
				.wdpro_path_absolute_to_relative($backtrace['file'])
				. ' - '
				. $backtrace['line'].PHP_EOL
				.'</span>';
		}
		print_r($data);
		echo('</pre>');

		if ($exit) exit();
	}
}


/**
 * Заменяет в тексте заданную метку на значение, либо если метки нету, добавляет значение в конец текста
 *
 * @param string $text Текст
 * @param string $chunk Метка, например, [form]
 * @param string $add Значение. То что поставить вместо метки
 * @return string
 * @example wdpro_replace_or_append($text, '[form]', $form->getHtml());
 */
function wdpro_replace_or_append(&$text, $chunk, $add)
{
	if (strstr($text, $chunk))
	{
		$text = str_replace($chunk, $add, $text);
	}
	else
	{
		$text = $text.$add;
	}

	return $text;
}


/**
 * Заменяет в тексте заданную метку на значение, либо если метки нету, добавляет значение в начало текста
 *
 * @param string $text Текст
 * @param string $chunk Метка, например, [form]
 * @param string $add Значение. То что поставить вместо метки
 * @return string
 * @example wdpro_replace_or_prepend($text, '[form]', $form->getHtml());
 */
function wdpro_replace_or_prepend(&$text, $chunk, $add)
{
	if (strstr($text, $chunk))
	{
		$text = str_replace($chunk, $add, $text);
	}
	else
	{
		$text = $add.$text;
	}

	return $text;
}


/**
 * Возвращает html код, который
 *
 * @param string $file Файл шаблона
 * @param mixed $data Данные для шаблона. Эта переменная будет доступна в шаблоне
 * @param null|string $defaultFile Запасной файл шаблона. Из него будет сделан
 * основной, если основного не будет
 * @return string
 * @throws Exception
 */
function wdpro_render_php($file, $templateData=null) {

	ob_start();

	$data = array();
	$args = func_get_args();
	array_shift($args);
	foreach($args as $arg) {
		$data = wdpro_extend($data, $arg);
	}

	extract($data, EXTR_SKIP);

	if ($file && is_file($file))
	{
		require($file);
		$html = ob_get_contents();
	}
	else
	{
		throw new Exception('Нет шаблона '.$file);
	}

	ob_clean();
	ob_end_flush();

	return $html;
}


/**
 * Копирует defaultFile в file, если file не существует
 *
 * Например, чтобы модуль wdpro мог создавать в темах шаблоны по-умолчанию
 *
 * @param string $defaultFile Файл по-умолчанию
 * @param string $file Файл, которого может не быть
 */
function wdpro_default_file($defaultFile, $file) {

	if (!is_file($file) && is_dir(dirname($file)) && is_file($defaultFile)) {
		copy($defaultFile, $file);
	}
}


/**
 * Рекурсивное копирование
 *
 * @param string $src Откуда
 * @param string $dst Куда
 * @param null|callback $callbackFilenameMod Каллбэк, с помощью которого можно менять
 *                                           имена файлов
 */
function wdpro_copy($src, $dst, $callbackFilenameMod=null) {
	$dir = opendir($src);
	@mkdir($dst);
	while(false !== ( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				wdpro_copy($src . '/' . $file,$dst . '/' . $file);
			}
			else {
				$target = $file;
				if ($callbackFilenameMod)
				$target = $callbackFilenameMod($file);
				copy($src . '/' . $file,$dst . '/' . $target);
			}
		}
	}
	closedir($dir);
}


/**
 * Копирует файл и при необходимости создает папки
 *
 * @param string $from Откуда (полный путь)
 * @param string $to Куда (полный путь)
 *
 * @return bool
 */
function wdpro_copy_file($from, $to) {
	//$pathFrom = pathinfo($from);

	$pathTo = pathinfo($to);

	if (!is_dir($pathTo['dirname'])) {
		mkdir($pathTo['dirname'], 0777, true);
	}

	return copy($from, $to);
}


/**
 * Создает страницу, если ее нету
 *
 * @param string $uri Адрес страницы
 * @param callback $pageDataCallback Каллбэк, принимающий данные страницы
 * [
 *	 'post_title'=>'Название',
 *	 'post_content'=>'Текст',
 *	 'post_name'=>$uri',
 * ]
 */
function wdpro_default_page($uri, $pageDataCallback) {
	\Wdpro\Page\Controller::defaultPage($uri, $pageDataCallback);
}


/**
 * Заменяет в тексте метки типа {name} на значение
 *
 * @param string $text Текст с метками
 * @param array $data Список значений и меток [['name'=>'Рома'],...]
 * @return string
 */
function wdpro_render_text($text, $data=null) {

	if (is_array($data)) {
		foreach($data as $key=>$value) {

			$text = str_replace('['.$key.']', $value, $text);
		}
	}

	return $text;
}


/**
 * Проверяет, что указанный адрес абсолютный
 *
 * @param string $url Адрес
 * @return bool
 */
function wdpro_is_absolute_url($url) {
	return !!preg_match('~^http(s)?://~', $url);
}


/**
 * Возвращает сайта uri относительно домена
 *
 * @return string
 */
function wdpro_home_uri() {
	$url = home_url();

	$url = str_replace(
		$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'],
		'',
		$url
	);

	return $url;
}


/**
 * Возвращает uri главной страницы текущего языка
 *
 * @return string
 */
function wdpro_home_url_with_lang() {
	return \Wdpro\Lang\Data::currentUrl();
}


/**
 * Запускает каллбэк при открытии страницы по заданному относительному адресу
 *
 * @param string $uri Адрес страницы
 * @param callback $callback Каллбэк, получающий объект поста при открытии страницы
 */
function wdpro_on_uri($uri, $callback) {

	add_action(
		'wp',
		function () use (&$uri, &$callback) {

			$post = get_post();

			if ($uri == $post->post_name) {

				$callback($post);
			}
		}
	);
}


/**
 * Запускает каллбэк при открытии страницы по заданному абсолютному адресу
 *
 * @param string $url Адрес страницы (http://...)
 * @param callback $callback Каллбэк, получающий объект поста при открытии страницы
 */
function wdpro_on_url($url, $callback) {

	add_action(
		'wp',
		function () use (&$url, &$callback) {

			if (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME']) {
				$scheme = $_SERVER['REQUEST_SCHEME'];
			}
			else {
				$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ?
					'https' : 'http';
			}

			$absurl = $scheme.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$absurl = str_replace('?'.$_SERVER['QUERY_STRING'], '', $absurl);

			if ($url == $absurl || $url.'/' == $absurl)
			{
				$callback(get_post());
			}
		}
	);
}


/**
 * Запускает каллбэк при открытии страницы по заданному адресу, и добавляет в текст то,
 * что возвратил каллбэк
 *
 * @param string|array $uri Адрес страницы или массив адресов
 * @param callback $callback Каллбэк, получающий объект поста при открытии страницы
 */
function wdpro_on_uri_content($uri, $callback) {

	add_action(
		'wp',
		function () use (&$uri, &$callback) {

			if (!is_array($uri)) $uri = [$uri];

			// Определяем по посту
			$post = get_post();
			if ($post) {
				foreach($uri as $uriOne) {
					if (($uriOne == $post->post_name)
					|| ($uriOne === '' && $post->ID == get_option('page_on_front'))) {
						wdpro_content($callback);
						break;
					}
				}
			}

			// Определяем по адресу
			else {
				$currentPostName = wdpro_current_post_name();

				return $currentPostName === $uri;
			}
		}
	);
}


/**
 * Запускает каллбэк при открытии страницы по заданному адресу, и добавляет в текст то,
 * что возвратил каллбэк
 *
 * @param string|array $uri Адрес страницы или массив адресов
 * @param callback $callback Каллбэк, получающий объект поста при открытии страницы
 * @param int $priority Приоритет
 */
function wdpro_on_content_uri($uri, $callback, $priority=10) {
	add_action(
		'wp',
		function () use (&$uri, &$callback, &$priority) {

			$post = get_post();

			if (!is_array($uri)) $uri = [$uri];

			if ($post) {
				foreach($uri as $uriOne) {
					if (($uriOne == $post->post_name)
						|| ($uriOne === '' && $post->ID == get_option('page_on_front'))) {
						wdpro_on_content($callback, $priority);
						break;
					}
				}
			}
		}
	);
}


/**
 * Переводит массив в query формат
 *
 * @param array $args Массив
 * @param null|string|array $no ключи, которые не следует добавлять в query из массива
 * @return bool|string
 */
function wdpro_urlencode_array($args, $no=null)
{
	return wdpro_urlencode_array2($args, $no);
	if(!is_array($args)) return false;

	if ((!is_array($no))&&($no!=null))
	{
		$no=array($no);
	}
	if (is_array($no))
	{
		foreach($no as $no_key)
		{
			$no_arr[$no_key]=1;
		}
	}

	$c = 0;
	$out = '';
	foreach($args as $name => $value)
	{
		if (!$no_arr[$name])
		{
			if($c++ != 0) $out .= '&';
			$out .= urlencode("$name").'=';
			if(is_array($value))
			{
				$out .= urlencode(serialize($value));
			}else{
				$out .= urlencode("$value");
			}
		}
	}
	return ($out);
}


/**
 * @param $arr
 * @param null $no
 * @param null $name
 * @return string
 * @ignore
 */
function wdpro_urlencode_array2($arr, $no=null, $name=null)
{
	$return = '';
	$no_arr = array();

	if ((!is_array($no))&&($no!=null))
	{
		$no=array($no);
	}
	if (is_array($no))
	{
		foreach($no as $no_key)
		{
			$no_arr[$no_key]=1;
		}
	}

	if (is_array($arr))
	{
		foreach($arr as $key=>$val)
		{
			if ($name!=null)
			{
				$k=$name.'['.$key.']';
			}
			else
			{
				$k=$key;
			}
			if (!isset($no_arr[$k]) || !$no_arr[$k])
			{
				if ($return!='') { $return.='&'; }
				if (is_array($val))
				{
					$return.=wdpro_urlencode_array2($val, $no, $k);
				}
				else
				{
					$return.=$k."=".urlencode($val);
				}
			}
		}
	}

	return $return;
}


/**
 * Возвращает адрес для ajax запроса
 *
 * @return string|void
 */
function wdpro_ajax_url($params=null) {


	$url =  admin_url('admin-ajax.php');

	if ($params) {

		if (isset($params['action'])) {
			$params['wdproAction'] = $params['action'];
			$params['action'] = 'wdpro';
		}

		$url .= '?'.http_build_query($params);
	}

	return $url;
}


function output_file($file,$name)
{
	//do something on download abort/finish
	//register_shutdown_function( 'function_name'  );
	if(!file_exists($file))
		die('file not exist!');
	$size = filesize($file);
	$name = rawurldecode($name);

	if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']))
		$UserBrowser = "Opera";
	elseif (ereg('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']))
		$UserBrowser = "IE";
	else
		$UserBrowser = '';

	/// important for download im most browser
	$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ?
		'application/octetstream' : 'application/octet-stream';
	@ob_end_clean(); /// decrease cpu usage extreme
	header('Content-Type: ' . $mime_type);
	header('Content-Disposition: attachment; filename="'.$name.'"');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header('Accept-Ranges: bytes');
	header("Cache-control: private");
	header('Pragma: private');

	/////  multipart-download and resume-download
	if(isset($_SERVER['HTTP_RANGE']))
	{
		list($a, $range) = explode("=",$_SERVER['HTTP_RANGE']);
		str_replace($range, "-", $range);
		$size2 = $size-1;
		$new_length = $size-$range;
		header("HTTP/1.1 206 Partial Content");
		header("Content-Length: $new_length");
		header("Content-Range: bytes $range$size2/$size");
	}
	else
	{
		$size2=$size-1;
		header("Content-Length: ".$size);
	}
	$chunksize = 1*(1024*1024);
	$bytes_send = 0;
	if ($file = fopen($file, 'r'))
	{
		if(isset($_SERVER['HTTP_RANGE']))
			fseek($file, $range);
		while(!feof($file) and (connection_status()==0))
		{
			$buffer = fread($file, $chunksize);
			print($buffer);//echo($buffer); // is also possible
			flush();
			$bytes_send += strlen($buffer);
			//sleep(1);//// decrease download speed
		}
		fclose($file);
	}
	else
		die('error can not open file');
	if(isset($new_length))
		$size = $new_length;
	die();
}


/**
 * Возвращает случайный пароль
 *
 * @return string
 */
function wdpro_generate_password() {
	$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
	.'!@#$%^&*()-=_+[]/';
	$pass = array(); //remember to declare $pass as an array
	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	for ($i = 0; $i < 8; $i++) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass); //turn the array into a string
}


/**
 * Возвращает ID сессии (строку)
 *
 * Сделал, потому что забываю функцию session_id() и постоянно ищу что-то вроде
 * wdpro_visitor...
 *
 * @return string
 */
function wdpro_visitor_session_id() {
	return session_id();
}


/**
 * Проверяет что сессия запущена
 *
 * http://php.net/manual/ru/function.session-status.php
 *
 * @return bool
 */
function wdpro_is_session_started()
{
	if ( php_sapi_name() !== 'cli' ) {
		if ( version_compare(phpversion(), '5.4.0', '>=') ) {
			return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
		} else {
			return session_id() === '' ? FALSE : TRUE;
		}
	}
	return FALSE;
}


/**
 * Возвращает значение из $_GET, если оно есть
 *
 * @param $_GET_key
 * @return mixed
 */
function wdpro_get ($_GET_key) {

	if (isset($_GET[$_GET_key]))
		return $_GET[$_GET_key];
}


/**
 * Аналог print_r, только добавляет по краям <pre>
 *
 * @param mixed $data Всякие данные
 * @param bool $return
 * @return string
 */
function print_r_pre($data, $return=false) {

	$text = '<pre>'.print_r($data, 1).'</pre>';

	if ($return) {
		return $text;
	}

	echo($text);
}


/**
 * Открывает <pre>
 */
function wdpro_pre() {
	echo '<pre>';
}


/**
 * Закрывает </pre>
 */
function wdpro_pre_close() {
	echo '</pre>';
}


/**
 * Возвращает настройку, при необходимости сохраняет значение по0умолчанию ,когда
 * настройки нет
 *
 * @param string $optionName Имя настройки
 * @param mixed $defaultValue Значение по-умолчанию
 * @return mixed|null|void
 */
function wdpro_get_option($optionName, $defaultValue=null) {

	if (strstr($optionName, '[lang]')) {
		$optionName = str_replace('[lang]', \Wdpro\Lang\Data::getCurrentSuffix(), $optionName);
	}
	$value = get_option($optionName);

	if (!$value && $defaultValue !== null) {
		update_option($optionName, $defaultValue);
		return $defaultValue;
	}

	return $value;
}


/**
 * Форматирование телефонного номера
 * по шаблону и маске для замены
 *
 * https://dotzero.blog/php-phone-format/
 *
 * @param string $phone
 * @param string|array $format +7 (###) ### ####
 * @param string $mask
 * @return bool|string
 */
function wdpro_phone_format($phone, $format, $mask = '#')
{
    $phone = preg_replace('/[^0-9]/', '', $phone);

    if (is_array($format)) {
        if (array_key_exists(strlen($phone), $format)) {
            $format = $format[strlen($phone)];
        } else {
            return false;
        }
    }

    $pattern = '/' . str_repeat('([0-9])?', substr_count($format, $mask)) . '(.*)/';

    $format = preg_replace_callback(
        str_replace('#', $mask, '/([#])/'),
        function () use (&$counter) {
            return '${' . (++$counter) . '}';
        },
        $format
    );

    return ($phone) ? trim(preg_replace($pattern, $format, $phone, 1)) : false;
}


/**
 * Начало тега <noindex>
 *
 * Функции нужны для того, чтобы не выводить тег <noindex>, внутри которого ничего нету
 */
function noindex_start() {
	global $noindexOb;
	$noindexOb = '';

	ob_start();
}


/**
 * Конец тега </noindex>
 */
function noindex_end() {
	global $noindexOb;
	$content = ob_get_contents();
	ob_end_clean();

	if ($content) {
		echo '<noindex>'.$content.'</noindex>';
		//echo ''.$content.'';
	}
}


/**
 * Добавление шорткода в текст страницы, если шорткода еще нету
 *
 * Это нужно, когда, например, добавляем новость на какую-нибудь страницу.
 * То есть делаем на странице список новостей.
 * А статьи отображаются на странице по шорткоду.
 * Тогда надо делать, чтобы автоматом добавился шорткод на страницу, где отображать
 * списокновостей.
 *
 * @param \Wdpro\BasePage $post Страница, на которую добавлять шорткод
 * @param string $shortcode Сам шорткод
 * @throws \Wdpro\EntityException
 */
function wdpro_add_shortcode_to_post($post, $shortcode) {
	if ($post && $langs = \Wdpro\Lang\Data::getSuffixes()) {
		$needSave = false;

		foreach ($langs as $lang) {
			$text = $post->getData('post_content'.$lang);
			if (!strstr($text, $shortcode)) {
				$needSave = true;
				$text .= '<p>'.$shortcode.'</p>';
				$post->mergeData([
					'post_content'.$lang => $text,
				]);
			}
		}

		if ($needSave) {
			$post->save();
		}
	}
}


/**
 * Возвращает уровень вложенности, на котором находится добавляемый или редактируемый
 * поств админке
 *
 * Это нужно в том числе для того, чтобы для разных уровней делать разные поля в форме
 *
 * @return int
 */
function wdpro_console_post_level() {

	global $wdpro_console_post_level;
	if (isset($wdpro_console_post_level))
		return $wdpro_console_post_level;

	$level = 0;

	if (isset($_GET['sectionId']) && $_GET['sectionId']) {
		$post = wdpro_get_post_by_id($_GET['sectionId']);
		$level = 1;
	}

	else if (isset($_GET['post']) && $_GET['post']) {
		$post = wdpro_get_post_by_id($_GET['post']);
		$level = 0;
	}

	else if (isset($_POST['post_ID']) && $_POST['post_ID']) {
		$post = wdpro_get_post_by_id($_POST['post_ID']);
		$level = 0;
	}

	if (isset($post) && $post) {
		while($post = $post->getParent()) {
			$level ++;
		}
	}

	return $level;
}


/**
 * Возвращает html код options
 *
 * @param  array $options_array Массив options [ [id, text], [id, text], ... ]
 * @param null   $selected_id
 *
 * @return string
 */
function wdpro_options_html ($options_array, $selected_id = null) {
	if ( is_array($options_array) ) {
		$html = '';
		foreach ( $options_array as $option ) {
			$selected = '';
			if ( $selected_id !== null && $option[0] == $selected_id ) {
				$selected = ' selected="selected"';
			}
			$html .= '<option value="' . $option[0] . '"' . $selected . '>' . $option[1] . '</option>';
		}

		return $html;
	}
}


/**
 * Возвращает roll по адресу, который ?page=App.Gallery.ConsoleRoll
 *
 * @param string $page Адрес $_GET['page']
 *
 * @return \Wdpro\Console\Roll
 */
function wdpro_get_roll_by_get_page($page) {
	$page = str_replace('.', '\\', $page);
	$page = '\\'.$page;

	return new $page;
}


/**
 * Создание поста
 *
 * @param array $data Данные поста
 */
function wdpro_create_post($data) {
	// Добавляем страницу
	$data['id'] = wp_insert_post($data);

	$entityClass = wdpro_get_entity_class_by_post_type($data['post_type']);
	$entity = new $entityClass($data);
	$entity->save();
}

/**
 * True, если это даминка
 *
 * @return bool
 */
function wdpro_is_admin() {
	return defined('WP_ADMIN') && WP_ADMIN;
}


/**
 * Добавляет слэш в конец строки, если его нету
 *
 * @param string $str Строка
 */
function wdpro_add_slash_to_end(&$str) {
	$str = rtrim($str, '/') . '/';
}


$wdproData = [];

/**
 * Запоминает или возвращает что-нибудь
 *
 * Например, сначала можно что-то запомнить
 * А потом вывести в шаблоне страницы
 *
 * @param string $name Имя данных
 * @param string|mixed $value Значение
 *
 * @return string|mixed
 */
function wdpro_data($name, $value='WDPRO_NOT_SETTED_SO_RETURN_VALUE') {
	global $wdproData;
	if ($value === 'WDPRO_NOT_SETTED_SO_RETURN_VALUE') {
		if (isset($wdproData[$name])) {
			return $wdproData[$name];
		}
	}

	else {
		$wdproData[$name] = $value;
	}
}


/**
 * Возвращает html код калочки
 *
 * @param string $visible true, 1 - Отображать галочку
 * @param string $title   Текст всплаывающей подсказки
 *
 * @return string
 */
function wdpro_check_html($visible, $title='') {
	if ($visible) {
		return '<i class="fa fa-check" aria-hidden="true" title="'.$title.'"></i>';
	}
}


/**
 * Срабатывает после инициализации страницы
 *
 * Когда уже известно, что за страница открыта, что в хлебных крошках...
 *
 * @param callable $callback Каллбэк, в который отправляется объект страницы
 */
function wdpro_on_page_init($callback) {
	add_action('wdpro_breadcrumbs_init', function ($breadcrumbs) use (&$callback) {
		/** @var $breadcrumbs \Wdpro\Breadcrumbs\Breadcrumbs */

		$callback($breadcrumbs->getFirstEntity());
	});
}


/**
 * Возвращает домен без www из адреса.
 *
 * Или если это просто строка, а не адрес. То возвращает ее без изменений.
 *
 * @param string $url Url
 * @return string
 */
function wdpro_get_domain_from_url($url) {
	$parsed = parse_url($url);
	if (isset($parsed['host'])) {
		return str_replace('www.', '', $parsed['host']);
	}

	return $url;
}


/**
 * Возвращает класс объекта, чтобы в начале всегда был символ \
 *
 * @param object $object Объект
 * @return string
 */
function wdpro_get_class($object) {
	$class = get_class($object);

	$class = wdpro_root_namespace($class);

	return $class;
}


/**
 * Возвращает путь от самого начала (ставит в начале \, если его нету)
 *
 * @param string $path путь до класса
 * @return string
 */
function wdpro_root_namespace($path) {

	if (strpos($path, '\\') !== 0) {
		$path = '\\'.$path;
	}

	return $path;
}


/**
 * Обновление текста из редактора
 *
 * @param string $text Текст
 * @return string
 */
function wdpro_text_from_editor_normalize($text)
{
	// Закомментировал это чтобы текст не ломался, не превращался в полностью
	// htmlspecialchars, когда я вставляю в него html код, отображаемый как html код,
	// например с подсветкой
	/*if (strstr($text, '&lt;p'))
	{
		$text = htmlspecialchars_decode($text);
		$htmlspecialchars = true;
	}*/
	// Неактивные ссылки в активные
	$text = wdpro_link_text($text);

	// Убираем фигню из ссылок
	$text = preg_replace('/<a _src="([^"]+)"/', '<a', $text);

	// Активные ссылки в новом окне
	$text = preg_replace_callback('/<a href="([^"]+)">/', function ($arr)
	{
		$link = $arr[1];
		$parsed = parse_url($link);

		// Если это внешняя ссылка
		if (isset($parsed['host'])) {
			$host = str_replace('www.', '', $parsed['host']);
			if ($host != str_replace('www.', '', $_SERVER['HTTP_HOST'])) {
				$link = wdpro_redirect($link);

				return '<a href="'.$link.'" target="_blank">';
			}
		}

		// Внутренняя ссылка
		return $arr[0];

	}, $text);

	return $text;
}


/**
 * Делает в тексте ссылки активными
 *
 * @param string $text Текст
 * @param bool $blank Открывать в новом окне
 * @return string
 */
function wdpro_link_text($text, $blank=false)
{
	$blankTag = '';
	if ($blank)
	{
		$blankTag = ' target="_blank"';
	}

	$text = str_replace('&nbsp;', ' [&nbsp;] ', $text);

	$replace = function ($arr) use (&$blankTag) {
		$link = $arr[3];
		$text = $link;
		if (strlen($text) > 50) {
			$text = substr($text, 0, 40).'...';
		}

		return $arr[1].$arr[2].'<a href="'
			.$link
			.'"'.$blankTag.'>'.$text.'</a>';
	};

	$text= preg_replace_callback(
		"/(^|[\n ]|<p>)([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is",
		$replace,
		$text);

	$text= preg_replace_callback(
		"/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is",
		$replace,
		$text);

	$text = str_replace(' [&nbsp;] ', '&nbsp;', $text);

	return $text;
}


/**
 * Возвращает ссылку через редирект
 *
 * @param string $link Ссылка
 * @return string
 */
function wdpro_redirect($link) {
	return WDPRO_URL.'redirect.php?http='.urlencode($link);
}


$wdproJsData = [];

/**
 * Добавление данных в объект js: wdpro
 *
 * @param string $key Ключ
 * @param mixed $value Значение
 */
function wdpro_js_data ($key, $value) {
	global $wdproJsData;

	$wdproJsData[$key] = $value;
}


/**
 * Возвращает ID текущего пользователя
 *
 * @return int
 */
function wdpro_person_auth_id() {

	$personId = get_current_user_id();

	// Чтобы можно было заменить ID пользователя на какой-нибудь свой
	$personId = apply_filters(
		'wdpro_person_auth_id',
		$personId
	);

	return $personId;
}


/**
 * Возвращает ip посетителя
 *
 * @return string
 */
function wdpro_get_visitor_ip(){

	if (!empty($_SERVER['HTTP_REMOTE_ADDR'])) {
		$ip = $_SERVER['HTTP_REMOTE_ADDR'];
	}
	else if(!empty($_SERVER['HTTP_CLIENT_IP'])){
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}


// Отключение смайликов
function wdpro_disable_emojis() {
	remove_action( "wp_head", "print_emoji_detection_script", 7 );
	remove_action( "admin_print_scripts", "print_emoji_detection_script" );
	remove_action( "wp_print_styles", "print_emoji_styles" );
	remove_action( "admin_print_styles", "print_emoji_styles" );
	remove_filter( "the_content_feed", "wp_staticize_emoji" );
	remove_filter( "comment_text_rss", "wp_staticize_emoji" );
	remove_filter( "wp_mail", "wp_staticize_emoji_for_email" );
}
