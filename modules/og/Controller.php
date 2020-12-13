<?php
namespace Wdpro\Og;

class Controller extends \Wdpro\BaseController {

	/**
	 * Выполнение скриптов после инициализаций всех модулей (в админке)
	 */
	public static function runConsole()
	{
		\Wdpro\Console\Menu::addSettings('Соц сети', function ($form) {

			/** @var \Wdpro\Form\Form $form */


			$form->add([
				'name'=>'ogImage',
				'left'=>'Картинка для социальных сетей (по-умолчанию)',
				'bottom'=>'Размер от 200x200 пикселей. Лучше от 600×315.',
				'type'=>$form::IMAGE,
			]);

			$form->add([
				'left'=>'ID <a href="https://developers.facebook.com/docs/plugins/share-button?locale=ru_RU" target="_blank">приложения в FB</a>',
				'name'=>'fbAppId',
			]);

			$form->add([
				'name'=>'ogUrlHost',
				'left'=>'Англоязычный домен',
				'center'=>'https://...',
				'bottom'=>'<div style="max-width: 600px; font-weight: normal;">
<p>Это может пригодиться, когда у вас домен в зоне рф.</p>
<p>Тогда вы можете сделать дополнительный англоязычный домен (en). Сделать с него редирект на основной (рф). И указать в этом поле этот англоязычный домен (en).</p>
<p>Тогда в социальные сети будет отправляться англоязычный домен.</p>
</div>'
			]);

			$form->add($form::SUBMIT_SAVE);

			$form->addHeader('Отладка');
			$form->addHtml('
			<p>Это чтобы сбросить кеш и начали работать новые изменения для кнопок Поделиться.</p>
			<p>
				<a href="https://developers.facebook.com/tools/debug/og/object/" target="_blank">Facebook</a>,
				<a href="https://vk.com/dev/pages.clearCache" target="_blank">VK</a>
			');

			return $form;
		});
	}


	public static function getHeaderTags($data) {

		$page = wdpro_current_page();


		// Картинка для Поделиться
		// Из Wdpro
		$ogImage = '';


		// Из страницы (из метода)
		if (!$ogImage) {
			if (\method_exists($page, 'getOgImage')) {
				$ogImage = $page->getOgImage();
			}
		}


		// Из страницы (из специального поля)
		if (!$ogImage) {
			if (!empty($page->data['og_image'])) {
				$ogImage = WDPRO_UPLOAD_IMAGES_URL.$page->data['og_image'];
			}
		}


		// Из страницы (стандартное поле)
		if (!$ogImage) {
			if (!empty($page->data['image'])) {
				//$ogImage = WDPRO_UPLOAD_IMAGES_URL.$page->data['image'];
			}
		}

		// Из настроек
		if (!$ogImage) {
			$ogImageFile = wdpro_get_option('ogImage');
			if ($ogImageFile) {
				$ogImage = WDPRO_UPLOAD_IMAGES_URL.$ogImageFile;
			}
		}


		// Из поста
		/*if (!$ogImage) {
			$post = get_post();
			if ($post) {
				$image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail');
				$ogImage = $image[0];
			}
		}*/



		// URL
		$url = wdpro_current_url_rf();

		/*$replace = wdpro_get_option('ogUrlHost');
		if ($replace) {
			$replaceParsed = parse_url($replace);

			$parsedUrl = parse_url($url);

			$scheme = $replaceParsed['scheme'] ?
				$replaceParsed['scheme'] :
				$parsedUrl['scheme'];

			$host = $replaceParsed['host'];

			$url = str_replace(
				$parsedUrl['scheme'].'://'.$parsedUrl['host'],
				$scheme.'://'.$host,
				$url
			);
		}*/

		$data['url'] = $url;
		$data['ogImage'] = $ogImage;


		// Title, Description
		$og_title_data = wdpro_data('og_title');
		if ($og_title_data) {
			$data['title'] = $og_title_data;
		}
		else if (!empty($page->data['og_title'])) {
			$data['title'] = $page->data['og_title'];
		}

		// Description
		$og_description_data = wdpro_data('og_description');
		if ($og_description_data) {
			$data['description'] = $og_description_data;
		}
		else if (!empty($page->data['og_description'])) {
			$data['description'] = $page->data['og_description'];
		}


		$data['fbAppId'] = wdpro_get_option('fbAppId');


		$data['type'] = 'website';
		if (\method_exists($page, 'getOgType')) {
			$data['type'] = $page->getOgType();
		}

		$data = \apply_filters('wdpro_og', $data);

		return wdpro_render_php(
			__DIR__.'/templates/header.php',
			$data
		);
	}


}

return __NAMESPACE__;