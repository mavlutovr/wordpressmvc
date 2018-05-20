<?php
namespace Wdpro\Uppod\Js;

class Controller extends \Wdpro\BaseController {

	public static $playerN = 0;
	public static $showN = 0;
	public static $defaultStylesFile;

	/**
	 * Инициализация модуля
	 */
	public static function init()
	{
		add_filter('wdpro_text_help', function ($help) {

			$help .= '<p><b>Вставка видео</b><BR>
			[uppod_js id="ID_ВИДЕО" video="URL_ВИДЕО_ФАЙЛА" width="ШИРИНА"
			height="ВЫСОТА"
			autoplay="1"
			poster="URL_ПОСТЕРА"]
			</p>
			<p><b>Показывание блока, когда видео дошло до времени</b><BR>
			[uppod_js_show id="ID_ВИДЕО" time="МИНУТЫ:СЕКУНДЫ"]<BR>
			ТЕКСТ, КОТОРЫЙ НАДО ПОКАЗАТЬ[/uppos_js_show]</p>';

			return $help;
		});
	}


	/**
	 * Добавление стилей
	 *
	 * Как скачать стили http://uppod.ru/help/q=styles-html5
	 *
	 * @param string $stylesFile Абсолютный путь к файлу стилей
	 */
	public static function addStyles($stylesFile) {

		wdpro_add_script_to_site($stylesFile);
	}


	/**
	 * Дополнительная инициализация для сайта
	 */
	public static function initSite()
	{
		wdpro_add_script_to_site(__DIR__.'/uppod-0.13.04.js');

		// Видео
		add_shortcode('uppod_js', function ($params) {

			static::$playerN ++;

			add_action('wp_footer', function () use (&$params) {

				$attrs = $params['poster'] ? ', poster:"'
					.$params['poster']
					.'"' : '';

				// Стили
				if ($params['st']) {
					$attrs .= ', st: "'
						.$params['st']
						.'"';
				}
				else {
					$attrs .= ', st: "uppodvideo"';
				}

				$readyScript = '';
				if ($params['autoplay']) {
					$readyScript .= 'uppod.Play();';
				}

				$playerVar = 'uppodPlayer'.static::$playerN;
				$playerScript = 'var '.$playerVar .' = new Uppod({m:"video",
				uid:"js-uppod_js-'
					.static::$playerN
					.'", file:"'
					.$params['video']
					.'",
					onReady: function (uppod) {
						'.$readyScript.'
					}'
					.$attrs
					.'});';

				if ($params['id']) {
					$playerScript .= 'window.uppodPlayersByID = window.uppodPlayersByID
					 || {};
					window.uppodPlayersByID["'.$params['id'].'"] = '.$playerVar.';';
				}
				else {
					$playerScript .= 'window.uppodPlayers = window.uppodPlayers || [];
					window.uppodPlayers.push('.$playerVar.');';
				}

				echo '<script type="text/javascript">'
					//.$playerScript
					.'</script>';
			});

			return wdpro_render_php(__DIR__.'/templates/video.php', $params, [
				'playerN' => static::$playerN
			]);
		});

		// Показывающийся текст
		add_shortcode('uppod_js_show', function ($params, $content) {

			static::$showN ++;

			add_action('wp_footer', function () use (&$params) {

				echo '<script type="text/javascript">window.wdpro_uppod_js_show("'
					.static::$showN
					.'");</script>';
			});

			return wdpro_render_php(__DIR__.'/templates/show.php', $params, [
				'content'=>$content,
				'showN'=>static::$showN,
			]);
		});
	}


}

return __NAMESPACE__;
