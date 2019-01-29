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

				// <link rel="stylesheet" type="text/css" href="..." media="all" />
				// <link rel='stylesheet' id='bfa-font-awesome-css'  href='...' type='text/css'
				// media='all' />

				$html = preg_replace_callback(
					'~(<link.*?rel=["\']stylesheet["\'].*?>)~i',
					function ($arr) {
						static::$cssFileHtmls .= $arr[1].PHP_EOL;
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
					if (strstr($html, '</body>')) {
						$html = str_replace(
							'</body>',
							static::$cssFileHtmls.PHP_EOL.'</body>',
							$html
						);
					}

					else {
						$html .= static::$cssFileHtmls;
					}
				}

			return $html;
		};

		// Css to header
		$cssToHeader = function ($html) {

				// <link rel="stylesheet" type="text/css" href="..." media="all" />
				// <link rel='stylesheet' id='bfa-font-awesome-css'  href='...' type='text/css'
				// media='all' />

				$html = preg_replace_callback(
					'~(<link.*?rel=["\']stylesheet["\'].*?>)~i',
					function ($arr) {
						static::$cssFileHtmls .= $arr[1].PHP_EOL;
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
						'</head>',
						static::$cssFileHtmls.PHP_EOL.'</head>',
						$html
					);
				}

			return $html;
		};

		// Title to Top
		$titleToTop = function ($html) {
			// Убиаем noscript из head
			$html = preg_replace_callback(
				'~<head>[\s\S]*</head>~i',
				function ($arr) {

					$html = $arr[0];
					$first = [];

					// Title
					$html = preg_replace_callback(
						'~<title[\s\S]*?</title>~i',
						function ($arr) use (&$first) {

							$first[] = $arr[0];

							return '';
						},
						$html
					);

					// Keywords
					// <meta name="keywords" content=""/>
					$html = preg_replace_callback(
						'~<meta[^<]*?name="keywords"[\s\S]*?>~i',
						function ($arr) use (&$first, &$html) {

							$first[] = $arr[0];

							return '';
						},
						$html
					);

					// Description
					// <meta name="description" content=""/>
					$html = preg_replace_callback(
						'~<meta[^<]*?name=["\']description["\'][\s\S]*?>~i',
						function ($arr) use (&$first) {
							$first[] = $arr[0];

							return '';
						},
						$html
					);

					$html = str_replace(
						'<head>',
						'<head>'.implode('', $first),
						$html);

					return $html;
				},
				$html
			);

			return $html;
		};

		// Scripts To Noindex
		$scriptsToNoindex = function ($html) {

			// Скрипты в noindex
			if (wdpro_get_option('wdpro_scripts_to_noindex') == 1) {

				// Убираем пробелы между скриптами
				// Пробелы могут остаться в html коде, потому что после этого еще будут
				// убираться html комментарии и заменяться на пробелы
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

				// Убиаем noscript из head
				$html = preg_replace_callback(
					'~<head>[\s\S]*</head>~i',
					function ($arr) {

						$head = $arr[0];
						$head = str_replace('<noindex>', '', $head);
						$head = str_replace('</noindex>', '', $head);

						return $head;
					},
					$html
				);

				/*$html = preg_replace(
					'~(</noindex>\s*<noindex>)~',
					'',
					$html
				);*/
				$html = str_replace('</noindex><noindex>', '', $html);


			}


			// Убиаем noscript из head
			$html = preg_replace_callback(
				'~<head>[\s\S]*</head>~i',
				function ($arr) {

					$html = $arr[0];
					$first = [];

					// Title
					$html = preg_replace_callback(
						'~<title[\s\S]*?</title>~i',
						function ($arr) use (&$first) {

							$first[] = $arr[0];

							return '';
						},
						$html
					);

					// Description
					// <meta name="description" content="Сеобит - продвижение сайтов в Санкт-Петербурге, Москве и других городах."/>
					$html = preg_replace_callback(
						'~<meta[\s]+?name=["\']description["\'][\s\S]*?/>~i',
						function ($arr) use (&$first) {

							$first[] = $arr[0];

							return '';
						},
						$html
					);

					// Keywords
					$html = preg_replace_callback(
						'~<meta[\s]*?name=["\']keywords["\'][\s\S]*?/>~i',
						function ($arr) use (&$first) {

							$first[] = $arr[0];

							return '';
						},
						$html
					);

					//print_r($html); exit();

					$html = str_replace(
						'<head>',
						'<head>'.PHP_EOL.implode(PHP_EOL, $first),
						$html);

					return $html;
				},
				$html
			);

			return $html;
		};

		// Css, Javascript, mail
		add_filter('wdpro_html', function ($html)
		use (&$cssToFooter, &$cssToHeader, &$titleToTop, &$scriptsToNoindex) {

			if (!static::$w3css) {
				//$html = $cssToFooter($html);
				$html = wdpro_get_option('wdpro_css_to_footer') ? $cssToFooter($html) : $cssToHeader($html);
				$html = $titleToTop($html);
				$html = $scriptsToNoindex($html);
			}

			// Антиспам ящиков
			if (wdpro_get_option('wdpro_mail_antispam')) {

				$isMail = false;

				$html = preg_replace_callback(

					'~<a[^<]+?'

					.'([\.\-a-zа-я0-9]+@[a-zа-я0-9\-]+\.[a-zа-я]+)'

					.'[\s\S]+?</a>~ui',

					function ($arr) use (&$isMail) {

						$isMail = true;
						$html = $arr[0];

						$html = base64_encode($html);

						return '<span class="js-mail-antispam-protect" style="display: none;">'
						       .$html
						       .'</span>';
					},
					$html
				);

				$html = preg_replace_callback(
					'~'
					//	.'>[^[<>"]]*?'
					.'([\.\-a-zа-я0-9]+@[a-zа-я0-9\-]+\.[a-zа-я]+)'
					//.'^[<>"]*?<'
					.'~ui',
					function ($arr) use (&$isMail) {

						$isMail = true;
						$html = $arr[0];
						$html = base64_encode($html);
						return '<span class="js-mail-antispam-protect" style="display: none;">'
						       .$html
						       .'</span>';
					},
					$html
				);

				if ($isMail) {
					$html .= '<script>window.wdpro_mail_antispam = true;</script>';
				}
			}

			return $html;
		});

		// Css WC3
		add_filter('w3tc_minify_processed', function ($html)
		use (&$cssToFooter, &$cssToHeader, &$titleToTop, &$scriptsToNoindex) {

			// Css
			if (static::$w3css) {
				//$html = $cssToFooter($html);
				$html = wdpro_get_option('wdpro_css_to_footer') ? $cssToFooter($html) : $cssToHeader($html);
				$html = $titleToTop($html);
				$html = $scriptsToNoindex($html);
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