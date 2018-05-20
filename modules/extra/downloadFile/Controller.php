<?php
namespace Wdpro\Extra\DownloadFile;

class Controller extends \Wdpro\BaseController {

	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole() {

		// Настройки
		\Wdpro\Console\Menu::addSettings('Закачка файлов', function ($form) {
			
			/** @var \Wdpro\Form\Form $form */
			
			$form->add(array(
				'type'=>'select',
				'top'=>'Способ скачивания',
				'name'=>'wdpro_download_type',
				'options'=>[
					''=>'Обычный',
					'x-accel-redirect-nginx'=>'Через Nginx',
				]
			));
			$form->add('submitSave');
			
			
			return $form;
		});
	}


	/**
	 * Инициализация модуля
	 */
	public static function init() {

		wdpro_ajax('downloadFile', function () {
			
			$filename = WDPRO_UPLOAD_PATH.str_replace('//', '', $_GET['file']);
			$filename = str_replace(':', '', $filename);

			// Этот код взят отсюда
			// http://sitear.ru/material/php_skript_download_file

			// нужен для Internet Explorer, иначе Content-Disposition игнорируется
			if(ini_get('zlib.output_compression'))
				ini_set('zlib.output_compression', 'Off');

			$file_extension = strtolower(substr(strrchr($filename,"."),1));

			if( $filename == "" )
			{
				echo "ОШИБКА: не указано имя файла.";
				exit;
			} elseif ( ! file_exists( $filename ) ) // проверяем существует ли указанный файл
			{
				echo "ОШИБКА: данного файла не существует.";
				exit;
			};
			if (file_exists($filename)) {
				
				$file = $filename;
				// сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
				// если этого не сделать файл будет читаться в память полностью!
				if (ob_get_level()) {
					ob_end_clean();
				}

				// Через Nginx
				if (get_option('wdpro_download_type') == 'x-accel-redirect-nginx') {
					$fileNameNginx = str_replace(dirname(ABSPATH), '', $filename);
					header('X-Accel-Redirect: ' . $fileNameNginx);
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename=' . basename($filename));
					exit;
				}

				
				// заставляем браузер показать окно сохранения файла
				//$finfo = finfo_open(FILEINFO_MIME_TYPE);
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				//header('Content-Type: '.finfo_file($finfo, $filename));
				header('Content-Disposition: attachment; filename=' 
					. basename($filename));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($filename));
				// читаем файл и отправляем его пользователю
				readfile($filename);
				exit;
			}
		});
	}


	/**
	 * Возвращает ссылку для скачивания
	 * 
	 * @param string $pathToFile Путь к файлу, который лежит в WDPRO_UPLOAD_PATH
	 * @return string
	 */
	public static function getDownloadLink($pathToFile) {
		
		$pathToFile = wdpro_realpath($pathToFile);
		$pathToFile = str_replace(WDPRO_UPLOAD_PATH, '', $pathToFile);
		
		return wdpro_ajax_url(array(
			'action'=>'downloadFile',
			'file'=>$pathToFile,
		));
	}
}

return __NAMESPACE__;