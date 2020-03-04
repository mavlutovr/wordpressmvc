<?php
namespace Wdpro\Form\Elements;

class Image extends File
{
	public function __construct($params)
	{
		$params = wdpro_extend(
			array(
				'dir' => wdpro_upload_dir( 'images' ),
				'ext'=>'jpg|jpeg|gif|png',
			),
			$params
		);

		parent::__construct($params);
	}


	/**
	 * Возвращает параметры поля
	 *
	 * @return array
	 */
	public function getParams()
	{
		$ret = parent::getParams();

		unset($ret['crop'], $ret['resize']);

		$ret['dirUrl'] = wdpro_upload_dir_url('images');

		return $ret;
	}


	/**
	 * Обработка файла после загрузки
	 *
	 * @param string $fileName Имяфайла
	 */
	protected function afterUpload($fileName)
	{
		$fileName = wdpro_basename($fileName);
		
		if ($fileName)
		{
			// Изменение размеров
			if (isset($this->params['resize']))
			{
				foreach($this->params['resize'] as $resize)
				{
					$targetDir = $this->params['dir'];
					if (isset($resize['dir']) && $resize['dir'])
					{
						$targetDir .= $resize['dir'].'/';
					}

					wdpro_image_resize(
						$this->params['dir'].$fileName,
						$targetDir.$fileName,
						$resize
					);

					// Водяной знак
					if (isset($resize['watermark']) && $resize['watermark']) {

						wdpro_image_watermark(
							$targetDir.$fileName,
							$resize['watermark']
						);
					}
				}
			}
			
			// Водяной знак
			if (isset($this->params['watermark'])) {
				wdpro_image_watermark(
					$this->params['dir'].$fileName,
					$this->params['watermark']
				);
			}
		}
	}


	/**
	 * Проверяет, может ли это поле перерисовывать водяные знаки
	 *
	 * Это нужно, чтобы определить, нужно ли перерисовывать водяные знаки в этом поле при
	 * полной перерисовке водяных знаков.
	 *
	 * @return bool
	 */
	public function canRedrawWatermark() {
		foreach ($this->params['resize'] as $param) {
			if (isset($param['watermark']['original']) && $param['watermark']['original']) {
				return true;
			}
		}
	}


	/**
	 * Перерисовывает водяные знаки
	 */
	public function redrawWatermarks($data) {

		$key = $this->params['name'];

		// Тут надо еще сделать языки


		if (isset($data[$key])) {
			$file = $data[$key];

			if ($file) {

				// Перебираем resize
				foreach ( $this->params['resize'] as $resize ) {

					// Если есть водяной знак
					if (isset($resize['watermark']['original'])) {

						$path = WDPRO_UPLOAD_IMAGES_PATH;
						if (isset($resize['dir'])) $path .= $resize['dir'];

						wdpro_add_slash_to_end($path);
						$originalDir = $path;
						$path .= $file;

						// Возврат к оригинальному файлу
						$originalPath = $originalDir . $resize['watermark']['original'];
						wdpro_add_slash_to_end($originalPath);
						$originalPath .= $file;

						if (is_file($originalPath)) {
							unlink($path);
							copy($originalPath, $path);
						}

						if (is_file($path)) {
							wdpro_image_watermark($path, $resize['watermark']);
						}
					}
				}


			}
		}

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

			if (!empty($this->params['resize']) && is_array($this->params['resize'])) {
				foreach ($this->params['resize'] as $param) {
					if (!empty($param['dir'])) {
						$path = $this->params['dir'].'/'.$param['dir'].'/'.$fileName;
						@unlink($path);
					}
				}
			}
		}
	}

}
