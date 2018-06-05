<?php
namespace Wdpro\Extra\Seo\Scripts;

class Controller extends \Wdpro\BaseController {

	protected static $headScripts = [];
	protected static $cssFileUrl;
	protected static $cssFileHtmls='';
	protected static $toNoindex = false;
	protected static $w3css = false;



	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function initSite () {


		static::$toNoindex = wdpro_get_option('wdpro_scripts_to_noindex') == 1;

		add_action( 'wp_enqueue_scripts', function () {
			static::printHead();
		} );

		// W3 Total Cache
		if (class_exists('\W3TC\Dispatcher')) {
			static::$w3css = \W3TC\Dispatcher::config()->get_boolean( 'minify.css.enable' );
		}

		// Css to footer
		$cssToFooter = function ($html) {
			// Css To Footer
			if (wdpro_get_option('wdpro_css_to_footer') == 1) {

				// <link rel="stylesheet" type="text/css" href="..." media="all" />
				// <link rel='stylesheet' id='bfa-font-awesome-css'  href='...' type='text/css'
				// media='all' />

				$html = preg_replace_callback(
					'~(<link.*?rel=["\']stylesheet["\'].*?>)~i',
					function ($arr) {
						static::$cssFileHtmls .= $arr[1];
						return '';
					},
					$html
				);



				if (strstr($html, '<!-- cssPlace -->')) {
					$html = str_replace(
						'<!-- cssPlace -->',
						static::$cssFileHtmls.PHP_EOL,
						$html
					);
				}

				else {
					$html = str_replace(
						'</body>',
						static::$cssFileHtmls.PHP_EOL.'</body>',
						$html
					);
				}



			}

			return $html;
		};

		// Css, Javascript
		add_filter('wdpro_html', function ($html) use (&$cssToFooter) {

			// Скрипты в noindex
			if (wdpro_get_option('wdpro_scripts_to_noindex') == 1) {

				// Убираем пробелы между скриптами
				$html = preg_replace(
					'~(</script>\s*<script)~i',
					'</script><script',
					$html
				);

				// Заключаем скрипты в noindex
				$html = preg_replace(
					'~(<script[\S\s]*?</script>)~i',
					'<noindex>$1</noindex>',
					$html
				);
				// Заключаем nosctipt в noindex
				$html = preg_replace(
					'~(<noscript[\S\s]*?</noscript>)~i',
					'<noindex>$1</noindex>',
					$html
				);

				/*$html = preg_replace(
					'~(</noindex>\s*<noindex>)~',
					'',
					$html
				);*/
				$html = str_replace('</noindex><noindex>', '', $html);


			}


			// Css
			if (!static::$w3css) {
				$html = $cssToFooter($html);
			}


			return $html;
		});

		// Css WC3
		add_filter('w3tc_minify_processed', function ($html) use (&$cssToFooter) {

			// Css
			if (static::$w3css) {
				$html = $cssToFooter($html);
			}

			return $html;
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
			//static::$toNoindex && noindex_start();
			wp_enqueue_scripts();
			//static::$toNoindex && noindex_end();
		}, 1 );

		add_action('wp_'.$to, function () {
			//static::$toNoindex && noindex_start();
			wp_print_head_scripts();
			//static::$toNoindex && noindex_end();
		}, 9 );

	}


}

return __NAMESPACE__;