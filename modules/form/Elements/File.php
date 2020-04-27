<?php
namespace Wdpro\Form\Elements;

class File extends Base
{
	protected $fileCheckResult;


	/**
	 * @param array $params Параметры
	 */
	public function __construct($params)
	{
		$params = wdpro_extend(
			array(
				'dir'=>wdpro_upload_dir('files'),
				'ext'=>'jpg|jpeg|gif|png|doc|docx|name|zip|rar|swf|xls|xlsx|txt|csv|flv|crx|pdf|md',
			),
			$params
		);

		parent::__construct($params);
	}


	/**
	 * Проверка поля на правильное заполнение
	 *
	 * @param $formData
	 * @return bool
	 */
	public function valid($formData)
	{
		// Начало загрузки файла
		$value = $formData[$this->params['name']];

		if (strstr($value, 'ZIP: '))
		{
			$value = str_replace('ZIP: ', '', $value);

			return $this->checkFiles($value);
		}

		return parent::valid($formData);
	}


	/**
	 * Проверка загружаемых файлов
	 * 
	 * @param $value
	 * @return bool
	 */
	public function checkFiles($value)
	{
		$files = array();
		
		if ($this->fileCheckResult)
		{
			return true;
		}


		$values = json_decode(
			urldecode($value), true
		);

		// Это чтобы можно было вручную отправлять в данные формы файлы в формате массива или просто строки с url картинки
		if (!$values)
			$values = $value;
		if ($values && !is_array($values)) $values = [ $values ];

		// Когда в поле указан абсолютный адрес
		// Загружаем картинку из интернета
		if (is_array($values)) {
			foreach ($values as $n=>$file) {
				$values[$n] = $this->tryToLoadFileFromInternet($file);
			}
		}

		//echo PHP_EOL.'$values: '; print_r($values); exit();


		if ($values && count($values))
		{
			foreach($values as $n=>$fileName)
			{
				if (strstr($fileName, 'ZIP: '))
				{
					$fileName = str_replace('ZIP: ', '', $fileName);

					if ($fileName = $this->checkFile($fileName))
					{
						$files[] = $fileName;
					}
				}
				else
				{
					$files[] = $fileName;
				}
			}
			
			// Если есть загруженные файлы
			if (count($files)) {
				
				// Если это не multiple, берем последнюю фотку (которая скорее всего была
				// загружена)
				if (!isset($this->params['multiple']) || !$this->params['multiple'])
				{
					$files = array($files[count($files) - 1]);
				}
			}
			
			// Нет загруженных файлов
			else {
				$files = '';
			}
			
			$this->fileCheckResult = array(
				'files'=>$files,
			);
		}
		
		else {
			$this->fileCheckResult = array(
				'files'=>'',
			);
		}
		
		return true;
	}


	/**
	 * Проверка файла
	 *
	 * @param $fileName Имя загруженного файла
	 * @return bool
	 */
	public function checkFile($fileName)
	{
		/*if ($this->fileCheckResult)
		{
			return $this->fileCheckResult['result'];
		}*/


		$fullGzFileName = wdpro_upload_dir('temp').$fileName;
		$tempFileName = preg_replace('~(_[0-9]+\.gz)$~', '', $fullGzFileName);
		$checkResult = $tempFileName;

		if (is_file($fullGzFileName))
		{
			// Разорхивируем сначала во временную папку
			wdpro_gz_decode($fullGzFileName, $tempFileName);
			unlink($fullGzFileName);
		}
		
		else if (is_file($tempFileName))
		{
			
		}

		else
		{
			$this->addError('Ошибка загрузки файла');
			return false;
		}

		// Проверка файла
		if(!preg_match('!\.('.$this->params['ext'].')$!i', $tempFileName)) {

			$this->addError(
				'Загружаемый файл '.$tempFileName.' не подходит. <BR>Выберите файл одного из следующий форматов: 
<BR>'.implode(', ', explode('|', $this->params['ext']))
			);
			$checkResult = false;

			wdpro_gz_encode($tempFileName, $fullGzFileName);
			unlink($tempFileName);
		}

		/*$this->fileCheckResult = array(
			'result'=>$checkResult,
			'file'=>$tempFileName,
		);*/
		
		if ($checkResult)
		{
			$fileName = wdpro_basename($checkResult);
			$dir = ($this->params['dir']);
			$freeFile = wdopro_file_free_name($dir.$fileName);

			rename($checkResult, $freeFile);
			
			$this->afterUpload($freeFile);

			return wdpro_basename($freeFile);
		}

		return $checkResult;
	}


	/**
	 * Обработка файла после загрузки
	 * 
	 * @param string $fileName Имяфайла
	 */
	protected function afterUpload($fileName)
	{
		
	}


	/**
	 * Возвращает параметры поля
	 *
	 * @return array
	 */
	public function getParams()
	{
		$ret = parent::getParams();

		unset($ret['dir']);

		$ret['dirUrl'] = wdpro_upload_dir_url('files');

		return $ret;
	}


	/**
	 * Загружает файлы из интернета при необходимости
	 *
	 * Когда в данных находится абсолютный адрес
	 *
	 * @param string $file Адрес файла
	 * @return string
	 */
	public function tryToLoadFileFromInternet($file) {
		if (wdpro_is_absolute_url($file)) {

			$fileHeaders = @get_headers($file);
			if (!$fileHeaders || !strpos($fileHeaders[0],"200")) {
				return '';
			}

			$tmpDir = wdpro_upload_dir('temp');
			$fileName = parse_url($file, PHP_URL_PATH);
			$fileName = wdpro_text_to_file_name(
				wdpro_ru_en(wdpro_basename($fileName))
			);
			$n = 1;
			$zipFileName = $fileName.'_1.gz';
			while(is_file($tmpDir.$zipFileName))
			{
				$n ++;
				$zipFileName = $fileName.'_'.$n.'.gz';
			}

			// Создаем архив с файлом
			wdpro_gz_encode($file, $tmpDir.$zipFileName);

			$file = 'ZIP: '.$zipFileName;
		}

		return $file;
	}


	/**
	 * Возвращает обработанные после отправки формы данные (массив имен фалов)
	 *
	 * @param array $formData Данные формы
	 * @return string
	 */
	public function getDataFromSubmit($formData=null)
	{
		if (!$formData)
			$formData = $this->form->getData(null, true);

		// Сдесь файлы в json и urlendode формате
		$files = $formData[$this->params['name']];


		if (!$this->checkFiles($files))
		{
			return null;
		}
		
		/*if (!$this->fileCheckResult['uploaded'])
		{
			$this->fileCheckResult['uploaded'] = true;
			foreach($this->fileCheckResult['files'] as $n=>$file)
			{
				$this->fileCheckResult['files'][$n] = $this->uploadFile($file);
			}
		}*/
		
		if (isset($this->params['multiple']) && $this->params['multiple'])
		{
			return $this->fileCheckResult['files'];
		}

		return $this->fileCheckResult['files'][0];
	}


	/**
	 * Удаляет файлы (этот метод наследуется в Image.php и File.php
	 */
	public function removeFiles()
	{
		$fileName = $this->getDataFromSubmit();

		if ($fileName) {
			$path = $this->params['dir'].'/'.$fileName;
			@unlink($path);
		}
	}





	/**
	 * Завершение загрузки файла (извлечение его из архива в папку)
	 *
	 * @param $tempGzFile
	 * @return string|bool Имя загруженного файла
	 */
	/*protected function uploadFile($tempGzFile)
	{
		// Перемещаем файл в ппку, указанную в параметрах
		$fileName = wdpro_basename($tempGzFile);
		$dir = ($this->params['dir']);
		$freeFile = wdopro_file_free_name($dir.$fileName);

		rename($tempGzFile, $freeFile);

		return wdpro_basename($freeFile);
	}*/
}
