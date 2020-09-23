<?php
namespace Wdpro\Tools\ConvertImgsToLightbox;

class Controller extends \Wdpro\BaseController {

  public static function runSite() {

		\add_filter('wdpro_html', function ($html) {

			$html = preg_replace_callback(
				'~<img[^>]*(data-lightbox="([^"]+)")[^>]*>~',
				function ($arr) {

					// Адрес большой картинки
					preg_match(
						'~src=([\'"])?([^\'"]+)~',
						$arr[0],
						$arrSrc
					);

					// Убираем из img data-lightbox
					$img = str_replace($arr[1], '', $arr[0]);

					// Получаем размеры маленькой картинки
					$width = 0;
					$height = 0;
					if (!\preg_match('~[^\-]width: ([0-9]+)px~', $img, $widthArr)) {
						return $arr[0];
					}
					if (!\preg_match('~[^\-]height: ([0-9]+)px~', $img, $heightArr)) {
						return $arr[0];
					}
					$width = $widthArr[1];
					$height = $heightArr[1];
					// \ob_clean(); print_r($width); exit();

					// Имя файла
					if (!\preg_match('~src=([\'"])?([^\'"]+)~', $img, $fileNameArr)) {
						return $arr[0];
					}

					$originalSrc = $fileNameArr[2];

					// $originalPath = str_replace(\home_url(), '', $originalSrc);
					$originalPath = wdpro_url_to_abs_path($originalSrc);
					if (!$originalPath) return $arr[0];

					$parsedSrc = parse_url($originalSrc);
					$path = $parsedSrc['path'];

					$pathinfo = pathinfo($path);
					$dir = WDPRO_UPLOAD_PATH.'converted-to-lightbox'.$pathinfo['dirname'];
					if (!\is_dir($dir)) {
						mkdir($dir, 0777, true);
					}

					$thumbFileName = $pathinfo['filename'] . '__'
						. $width . 'x' . $height
						. '.' .$pathinfo['extension'];

					$thumbFilePath = $dir.'/'.$thumbFileName;

					// Создание маленькой картинки
					if (!is_file($thumbFilePath)) {
						// \wdpro_image_resize()
						\wdpro_image_resize(
							$originalPath,
							$thumbFilePath,
							[
								'width'=>$width,
								'height'=>$height,
								'type'=>'crop',
							]
						);
					}

					$thumbUrl = wdpro_abs_path_to_url($thumbFilePath);

					$img = preg_replace(
						'~src=([\'"])?([^\'"]+)([\'"])?~',
						'src=$1'.$thumbUrl.'$3',
						$img
					);
					

					// Создаем ссылку
					$a = '<a href="'.$arrSrc[2].'" '.$arr[1].'>'
					.$img
					.'</a>';
					// print_r($a); exit();
					return $a;
				},
				$html
			);

			//\ob_clean();
			//echo 123; exit();

			return $html;
		});
	}
}

return __NAMESPACE__;