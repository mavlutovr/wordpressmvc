<?php
namespace Wdpro\Extra\Seo\Scripts;

class Controller extends \Wdpro\BaseController {

	protected static $headScripts = [];
	protected static $cssFileUrl;
	protected static $toNoindex = false;



	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function initSite () {


		static::$toNoindex = wdpro_get_option('wdpro_scripts_to_noindex') == 1;

		add_action( 'wp_enqueue_scripts', function () {
			static::printHead();
		} );

		// Css
		add_filter('w3tc_minify_processed', function ($buffer) {

			if (wdpro_get_option('wdpro_css_to_footer_w3tc') == 1) {

				$buffer = preg_replace_callback(
					'~<head><link rel="stylesheet" type="text/css" href="(.+?)" media="all" />~',
					function ($arr) {
						static::$cssFileUrl = $arr[1];
						return '<head>';
					},
					$buffer
				);

				if (strstr($buffer, '<!-- cssPlace -->')) {
					$buffer = str_replace(
						'<!-- cssPlace -->',
						'<link rel="stylesheet" type="text/css" href="'
						.static::$cssFileUrl
						.'" media="all" />'.PHP_EOL.'</body>',
						$buffer
					);
				}

				else {
					$buffer = str_replace(
						'</body>',
						'<link rel="stylesheet" type="text/css" href="'
						.static::$cssFileUrl
						.'" media="all" />'.PHP_EOL.'</body>',
						$buffer
					);
				}



				return $buffer;

			}


		});


		// Scripts Footer
		if (wdpro_get_option('wdpro_scripts_to_footer') == 1) {

			// Оставить jQuery в head
			if (wdpro_get_option('wdpro_scripts_to_footer_exclude_jquery') == 1) {
				static::$headScripts[] = 'jquery';
			}

			static::scriptsTo('footer');

			add_action( 'wp_footer', 'wdpro_the_footer', 1 );
		}

		// Scripts Header
		else {
			static::$toNoindex && static::scriptsTo('head');
		}
	}


	protected static function printHead() {
		if (!count(static::$headScripts)) return false;

		foreach (static::$headScripts as $script) {
			wp_print_scripts($script);
		}
	}


	/**
	 * Переместить скрипты в...
	 *
	 * @param string $to head или footer
	 */
	protected static function scriptsTo($to) {

		remove_action( 'wp_head', 'wp_enqueue_scripts', 1 );
		remove_action( 'wp_head', 'wp_print_head_scripts', 9 );
		remove_action( 'wp_head', 'wp_print_scripts' );

		add_action('wp_'.$to, function () {
		//	wp_print_styles();
			static::$toNoindex && noindex_start();
			wp_enqueue_scripts();
			static::$toNoindex && noindex_end();
		}, 1 );

//		add_action('wp_'.$to, function ($handles=false) {
//			static::$toNoindex && noindex_start();
//			wp_print_scripts($handles);
//			static::$toNoindex && noindex_end();
//		} );

		add_action('wp_'.$to, function () {
			static::$toNoindex && noindex_start();
			wp_print_head_scripts();
			static::$toNoindex && noindex_end();
		}, 9 );

	}


}

return __NAMESPACE__;