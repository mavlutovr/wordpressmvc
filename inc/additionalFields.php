<?php

// Можно доработать так
// http://truemisha.ru/blog/wordpress/meta-boxes.html


/**
 * Показывает форму дополнительных полей для поста и страницы
 * 
 * @param $post
 */
function wdproShowMetaForm($post)
{
	$form = new \Wdpro\Form\Form('wdpro');

	$form->add([
		'name'=>'alternative_url[lang]',
		'top'=>'Ссылка на другую страницу или другой сайт',
	]);

	$form->add([ 'name'=>'title[lang]', 'top'=>'Title' ]);
	$form->add([ 'name'=>'h1[lang]', 'top'=>'H1' ]);
	$form->add([ 'name'=>'keywords[lang]', 'top'=>'Keywords' ]);
	$form->add([ 'name'=>'description[lang]', 'top'=>'Description' ]);
	$form->add([ 'name'=>'madein[lang]', 'top'=>'Ссылка "Сделано в студии"',
	             'type'=>$form::TEXT
	]);

	$formData = [];
	$langs = \Wdpro\Lang\Data::getUris();

	$addDataToForm = function ($name, $lang) use (&$formData, &$post) {
		$name .= \Wdpro\Lang\Data::getSuffix($lang);
		$formData[$name] = get_post_meta($post->ID, $name, 1);
	};

	foreach ( $langs as $lang ) {
		$addDataToForm('alternative_url', $lang);
		$addDataToForm('title', $lang);
		$addDataToForm('h1', $lang);
		$addDataToForm('keywords', $lang);
		$addDataToForm('description', $lang);
		$addDataToForm('madein', $lang);
	}

	$form->setData($formData);

	$form->removeFormTag();
	echo $form->getHtml();

}


// Добавляем поля в админку
add_action('admin_init', function () {

	if (get_option('wdpro_additional_remove') != 1)
	{
		add_meta_box('extra_fields',
			'SEO',
			'wdproShowMetaForm',
			'post',
			'normal');
	
	
		add_meta_box('extra_fields',
			'Дополнительно',
			'wdproShowMetaForm',
			'page',
			'normal');
	}
	
}, 1);



// Сохранение
add_action('save_post', function ($postId) {

	// Если форма не прошла проверку
	if ( (isset($_POST['wdpro_nonce']) 
		&& !wp_verify_nonce( $_POST['wdpro_nonce'], __FILE__))
			|| (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			|| !current_user_can('edit_post', $postId)
			|| !(isset($_POST['wdpro']) && $_POST['wdpro'])
	) {

		// Завершаем процесс сохранения преждевременно
		return false;
	}
	
	
	// Перебираем данные и сохраняем
	foreach($_POST['wdpro'] as $key=>$value)
	{
		empty($value)
			? delete_post_meta($postId, $key)
			: update_post_meta($postId, $key, $value);
	}
	
	return $postId;
	
}, 0);



	
/**
 * Возвращает внутренности <head>
 */
function wdpro_the_header()
{
	remove_action( 'wp_head', '_wp_render_title_tag', 1 );

	$title = wdpro_data('title');

	if (!$title)
	$title = wdpro_get_post_meta('title');
	$description = wdpro_get_post_meta('description');
	$keywords = wdpro_get_post_meta('keywords');


	$h1 = wdpro_the_h1(true);
	$template = function ($string, $key) use (&$h1) {
		if (!$string) {
			$string = wdpro_get_option($key.'[lang]');
			$string = str_replace('[h1]', $h1, $string);
		}

		return $string;
	};

	$title = $template($title, 'wdpro_title_template');
	$description = $template($description, 'wdpro_description_template');
	$keywords = $template($keywords, 'wdpro_keywords_template');
	if (!$title) {
		$title = wdpro_the_title_standart();
	}


	// Картинка для Поделиться
	// Из Wdpro
	$ogImage = wdpro_data('ogImage');
	if (!$ogImage) {
		$page = wdpro_current_page();
		if (isset($page->data['image'])) {
			$ogImage = WDPRO_UPLOAD_IMAGES_URL.$page->data['image'];
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
	
?><title><?php echo($title); ?></title>
	<meta name="description" content="<?php echo( htmlspecialchars($description) );
	?>" />
	<meta name="keywords" content="<?php echo( htmlspecialchars($keywords) ); ?>" />

	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta property="og:title" content="<?=$title?>">
	<meta property="og:description" content="<?=htmlspecialchars($description)?>">
	<meta property="og:image" content="<?=$ogImage?>">
	<meta property="og:url" content="<?=wdpro_current_url()?>">
<?php


	echo wdpro_get_option('wdpro_head_additional');

	//wdpro_css_header();
	//wdpro_scripts_header();


}


/**
 * Выводит скрипты, которые необходимо отобразить в <header>
 */
function wdpro_scripts_header() {
	if (wdpro_get_option('wdpro_scripts_to_footer') != 1) {
		wdpro_scripts();
	}
}

/**
 * Выводит скрипты, которые необходимо отобразить перед </body>
 */
function wdpro_scripts_footer() {
	if (wdpro_get_option('wdpro_scripts_to_footer') == 1) {
		wdpro_scripts();
	}
}

// Выводит скрипты
function wdpro_scripts() {
	//$noindex = wdpro_get_option('wdpro_scripts_to_noindex') == 1;
	//$noindex && noindex_start();

	wp_enqueue_scripts();
	wp_print_head_scripts();
	wp_print_scripts();

	//$noindex && noindex_end();
}

function wdpro_the_footer() {
	if (wdpro_get_option('wdpro_css_to_footer') == 1) { ?>
		<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css"
		      media="screen"/>

		<?php
	}
}


/**
 * Выводит css
 */
function wdpro_css() {
	?>
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
	<?php
	wp_print_styles();
	locale_stylesheet();
}


/**
 * Выводит css
 */
function wdpro_css_header() {
	if (wdpro_get_option('wdpro_css_to_footer') != 1) {
		wdpro_css();
	}
}


/**
 * Выводит css
 */
function wdpro_css_footer() {
	if (wdpro_get_option('wdpro_css_to_footer') == 1) {
		wdpro_css();
	}
}


/**
 * Возвращает заголовок страницы
 *
 * @param bool $force Возвратить заголовок, даже когда он выключен через "-"
 * @return bool|null|string
 */
function wdpro_the_h1($force=false)
{
	$h1 = wdpro_data('h1');

	if (!$h1)
	$h1 = wdpro_get_post_meta('h1');
	
	if ($h1 != '-' && $h1 != '—' || $force)
	{
		if (!$h1)
		{
			$h1 = wdpro_the_title_standart();
		}
		
		return $h1;
	}
}
	

/**
 * Возвращает стандартный Title
 * 
 * @return null|string
 */
function wdpro_the_title_standart()
{
	$wdproTitle = wdpro_current_page()->getTitle();

	if ($wdproTitle) {
		return $wdproTitle;
	}

	return get_the_title();
}
	

/**
 * Возвращает Meta данные поста
 * 
 * @param string $metaName Имя мета-данных
 * @return mixed
 */
function wdpro_get_post_meta($metaName)
{
	$arr = get_post_meta(get_the_ID(), $metaName.\Wdpro\Lang\Data::getCurrentSuffix());
	
	if (is_array($arr) && isset($arr[0]) && $arr[0])
	{
		return $arr[0];
	}
}
	

/**
 * Возвращает сблок перенинковки
 * 
 * @return mixed
 */
function wdpro_links()
{
	echo wdpro_get_post_meta('links');
}


function wdpro_counters() {

	echo(\Wdpro\Counters\Controller::getCountersHtml());
}
	

/**
 * Возвращает ссылку на веб-студию
 * 
 * Рекоменудется устанавливать индексируемую ссылку на веб-студию только на главной 
 * странице
 * 
 * @return mixed
 */
function wdpro_madein()
{
	echo wdpro_get_post_meta('madein');
}


/*// Страница с настройками
add_action(
	'admin_menu',

	function () {
		
		// Options
		add_options_page(
			'Настройки WDPro',
			'WDPro',
			'administrator',
			'wdproOptions',
			function () {
				echo('<h2>WDPro</h2>');
			}
		);
	}
);*/