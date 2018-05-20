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
}
