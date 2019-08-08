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
				'left'=>'Картинка для социальных сетей',
				'bottom'=>'Размер от 200x200 пикселей. Лучше от 600×315.',
				'type'=>$form::IMAGE,
			]);

			$form->add([
				'left'=>'ID <a href="https://developers.facebook.com/docs/plugins/share-button?locale=ru_RU" target="_blank">приложения в FB</a>',
				'name'=>'fbAppId',
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

		$data['url'] = wdpro_current_url();

		// Картинка для Поделиться
		// Из Wdpro
		$ogImage = wdpro_data('ogImage');
		if (!$ogImage) {
			$page = wdpro_current_page();
			if (isset($page->data['image'])) {
				$ogImage = WDPRO_UPLOAD_IMAGES_URL.$page->data['image'];
			}
			else{
				$ogImageFile = wdpro_get_option('ogImage');
				if ($ogImageFile) {
					$ogImage = WDPRO_UPLOAD_IMAGES_URL.$ogImageFile;
				}
			}
		}

		// Стандартная
		if (!$ogImage) {
			$post = get_post();
			if ($post) {
				$image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail');
				$ogImage = $image[0];
			}
		}

		$data['ogImage'] = $ogImage;

		$data['fbAppId'] = wdpro_get_option('fbAppId');

		return wdpro_render_php(
			__DIR__.'/templates/header.php',
			$data
		);
	}


}

return __NAMESPACE__;