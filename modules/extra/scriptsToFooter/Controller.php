<?php
namespace Wdpro\Extra\ScriptsToFooter;

class Controller extends \Wdpro\BaseController {

	protected static $headScripts = [];
	protected static $cssFileUrl;

	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole () {
		\Wdpro\Console\Menu::addSettings('Скрипты в футер', function ($form) {

			/** @var $form \Wdpro\Form\Form */

			$form->add([
				'name'=>'wdpro_scripts_to_footer',
				'right'=>'Переместить скрипты в футер',
				'type'=>'check',
			]);

			$form->add([
				'name'=>'wdpro_scripts_to_footer_exclude_jquery',
				'right'=>'Оставить jQuery в header',
				'type'=>'check',
			]);

			$form->add([
				'name'=>'wdpro_css_to_footer',
				'right'=>'Переместить в футер Css',
				'type'=>'check',
			]);

			$form->add($form::SUBMIT_SAVE);

			return $form;

		});
	}


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

		add_action( 'wp_footer', 'wp_print_scripts' );
		add_action( 'wp_footer', 'wp_print_head_scripts', 9 );
		add_action( 'wp_footer', 'wp_enqueue_scripts', 1 );
		add_action( 'wp_footer', 'wdpro_the_footer', 1 );

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

//				$buffer = str_replace(
//					'</head>',
//					'
//		<noscript id="deferred-styles">
//      <link rel="stylesheet" type="text/css" href="'
//					.static::$cssFileUrl
//					.'" media="all"/>
//    </noscript>
//    <script>
//      var loadDeferredStyles = function() {
//        var addStylesNode = document.getElementById("deferred-styles");
//        var replacement = document.createElement("div");
//        replacement.innerHTML = addStylesNode.textContent;
//        document.body.appendChild(replacement)
//        addStylesNode.parentElement.removeChild(addStylesNode);
//      };
//      var raf = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
//          window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
//      if (raf) raf(function() { window.setTimeout(loadDeferredStyles, 0); });
//      else window.addEventListener(\'load\', loadDeferredStyles);
//    </script>'.PHP_EOL.'</head>',
//					$buffer
//				);

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