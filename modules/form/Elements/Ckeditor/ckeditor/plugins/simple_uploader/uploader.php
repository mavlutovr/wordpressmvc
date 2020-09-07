<?php
session_start();

	error_reporting(0);
	ini_set('display_errors', 'off');

	// Доступные типы файлов
	$allowedFiles = array(
		// Рисунки
		'jpg', 'jpeg', 'png', 'gif', 'bmp',

		// Другие файлы
		'doc', 'docx', 'name', 'zip', 'rar', 'swf', 'xls', 'xlsx', 'txt', 'pdf'
	);

?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
		body
		{
			margin: 0;
			background: white;
		}
	</style>
</head>
<body>
<?php
// Если админ авторизован
if (preg_match('~^localhost(:[0-9]+)?$~', $_SERVER['HTTP_HOST']) || $_SESSION['admin_ok'])
{
	/**
	 * Перевод русских букв в аднгийскую транскрипцию
	 *
	 * @param string $text Строка с русскими буквами
	 * @return string Строка с английскими буквами
	 */
	function ruEn($text)
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
		$text=str_replace(" ", "_", $text);

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
	 * Возвращает расширение файла
	 *
	 * @param string $name Имя файла
	 * @return string
	 */
	function getFileType($name)
	{
		$arr = explode(".", $name);

		if (count($arr) > 1)
		{
			return end($arr);
		}
	}


	/**
	 * Возвращает Имя файла без расширения
	 *
	 * @param string $name Имя файла
	 * @return string
	 */
	function getFileName($name)
	{
		$info = pathinfo($name);

		return $info['filename'];
	}


	//print_r($_FILES);

	// Переходим в public_html
	chdir(__DIR__.'/../../../../../../../../../uploads');

	// Папка, в которую грузить файл
	$dir = 'ckeditor';

	// При необходимости создаем эту папку
	if (!is_dir($dir))
	{
		mkdir($dir, 0777, true);
	}

	// Переходим в эту папку
	chdir($dir);

	// Получаем данные загружаемого файла
	$fileData = $_FILES['uploaderInput'];

	// Обработка ошибок
	if ($fileData['error'])
	{
		$upload_errors = array(
			UPLOAD_ERR_OK        => "No errors.",
			UPLOAD_ERR_INI_SIZE    => "Размер принятого файла превысил максимально допустимый размер, который задан директивой upload_max_filesize конфигурационного файла php.ini.",
			UPLOAD_ERR_FORM_SIZE    => "Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме.",
			UPLOAD_ERR_PARTIAL    => "Загружаемый файл был получен только частично.",
			UPLOAD_ERR_NO_FILE        => "Файл не был загружен.",
			UPLOAD_ERR_NO_TMP_DIR    => "Отсутствует временная папка.",
			UPLOAD_ERR_CANT_WRITE    => "Не удалось записать файл на диск.",
			UPLOAD_ERR_EXTENSION     => "PHP-расширение остановило загрузку файла. PHP не предоставляет способа определить какое расширение остановило загрузку файла; в этом может помочь просмотр списка загруженных расширений из phpinfo().",
			UPLOAD_ERR_EMPTY        => "File is empty." // add this to avoid an offset
		);

		echo('<div style="color: red">Ошибка: '.$upload_errors[$file['error']].'</div>');
		exit();
	}

	// Получаем имя файла
	$fileFullName = $fileData['name'];

	// Преобразуем русские буквы файла в английские
	$fileFullName = ruEn($fileFullName);

	// Получаем расширение файла
	$fileType = strtolower(getFileType($fileFullName));

	// Если такое расширение разрешено
	if (in_array($fileType, $allowedFiles))
	{
		// Имя файла без расширения
		$fileName = getFileName($fileFullName);

		// Номер файла (для переименования при одинаковых именах)
		$fileN = 1;

		// Переименование файла, если такой файл уже есть в папке
		while(is_file($fileFullName))
		{
			$fileN ++;
			$fileFullName = $fileName.'_'.$fileN.'.'.$fileType;
		}

		// Копируем загруженный файл в эту папку
		if (move_uploaded_file($fileData['tmp_name'], $fileFullName))
		{
			// Данные, отправляемые в редактор
			$editorEvent = array(
				'file'=>$fileFullName,
				'type'=>$_GET['type'],
				'editorId'=>$_GET['editorId'],
			);

			// Если это рисунок, добавляем размеры изображения
			if ($_GET['type'] == 'image')
			{
				$size = getimagesize($fileFullName);
				$editorEvent['width'] = $size[0];
				$editorEvent['height'] = $size[1];
			}

			echo('
			<script>
				window.parent.uploaded('.json_encode($editorEvent, JSON_UNESCAPED_UNICODE).');
			</script>
			<div>Файл загружен</div>
			');
		}

		else
		{
			echo('Не удалось заргузить файл');
		}
	}

	else
	{
		echo('Файл не подходит для загрузки');
	}



	// window.parent.CKEDITOR.tools.callFunction(0, '/userfiles/images/Public Folder/i.png', '');

}
?>
</body>
</html>
