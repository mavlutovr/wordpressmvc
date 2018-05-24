<?php
namespace Wdpro\Extra\Seo\Scripts;

class Controller extends \Wdpro\BaseController {

	protected static $headScripts = [];
	protected static $cssFileUrl;


	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function runSite () {
		if (wdpro_get_option('wdpro_scripts_to_footer') == 1) {

			// Оставить jQuery в head
			if (wdpro_get_option('wdpro_scripts_to_footer_exclude_jquery') == 1) {
				static::$headScripts[] = 'jquery';
			}

			add_action( 'wp_enqueue_scripts', function () {
				static::printHead();
			} );

			add_action( 'wp_head', function () {
				static::printHead();
			} );

			static::cleanHead();
		}
	}


	protected static function printHead() {
		if (!count(static::$headScripts)) return false;

		foreach (static::$headScripts as $script) {
			wp_print_scripts($script);
		}
	}


	protected static function cleanHead() {

		if( is_admin() )
			return;

		remove_action( 'wp_head', 'wp_print_scripts' );
		remove_action( 'wp_head', 'wp_print_head_scripts', 9 );
		remove_action( 'wp_head', 'wp_enqueue_scripts', 1 );

		add_action( 'wp_footer', 'wdpro_the_footer', 1 );

		if (wdpro_get_option('wdpro_scripts_to_noindex') == 1) {


			$noindexOb='';
			function noindex_start() {
				global $noindexOb;
				$noindexOb = '';

				ob_start();
			}

			function noindex_end() {
				global $noindexOb;
				$content = ob_get_contents();
				ob_end_clean();

				if ($content) {
					echo '<noindex>'.$content.'</noindex>';
				}
			}

			add_action('wp_footer', function ($handles=false) {
				noindex_start();
				wp_print_scripts($handles);
				noindex_end();
			} );

			add_action('wp_footer', function () {
				noindex_start();
				wp_print_head_scripts();
				noindex_end();
			}, 9 );

			add_action('wp_footer', function () {
				noindex_start();
				wp_enqueue_scripts();
				noindex_end();
			}, 1 );

		}
		else {
			add_action( 'wp_footer', 'wp_print_head_scripts', 9 );
			add_action( 'wp_footer', 'wp_print_scripts' );
			add_action( 'wp_footer', 'wp_enqueue_scripts', 1 );
		}

		if (wdpro_get_option('wdpro_css_to_footer') == 1) {
			add_filter('w3tc_minify_processed', function ($buffer) {

				$buffer = preg_replace_callback(
					'~<head><link rel="stylesheet" type="text/css" href="(.+?)" media="all" />~',
					function ($arr) {
						static::$cssFileUrl = $arr[1];
						return '<head>';
					},
					$buffer
				);

				$buffer = str_replace(
					'</body>',
					'<link rel="stylesheet" type="text/css" href="'
					.static::$cssFileUrl
					.'" media="all" />'.PHP_EOL.'</body>',
					$buffer
				);


				return $buffer;
			});
		}


	}


}

return __NAMESPACE__;