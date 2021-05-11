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

	$width = null;
	if (!\Wdpro\Lang\Data::enabled()) {
		$width = 600;
	}

	$form->add([ 'name'=>'title[lang]', 'top'=>'Title', 'width'=>$width ]);
	$form->add([ 'name'=>'h1[lang]', 'top'=>'H1', 'width'=>$width ]);
	$form->add([
		'name'=>'description[lang]',
		'top'=>'Description',
		'type'=> $form::TEXT,
		'width'=>$width,
	]);
	$form->add([ 'name'=>'keywords[lang]', 'top'=>'Keywords', 'width'=>$width ]);

	$form->add([
		'name'=>'alternative_url[lang]',
		'top'=>'Ссылка на другую страницу<BR>или другой сайт',
	]);
	// $form->add([ 'name'=>'madein[lang]', 'top'=>'Ссылка "Сделано в студии"',
	//              'type'=>$form::TEXT
	// ]);

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
	$description = wdpro_data('description');
	$keywords = wdpro_data('keywords');


	// Стандартная страница WopdPress
	if (!$title)
		$title = wdpro_get_post_meta('title');

	if (!$description)
		$description = wdpro_get_post_meta('description');

	if (!$keywords)
		$keywords = wdpro_get_post_meta('keywords');

	// Применяем фильтры для Title
	$title = apply_filters('wdpro_title', $title);

	// Страница Wdpro
	$page = wdpro_current_page();
	if ($page) {
		if (!$title)
			$title = $page->getTitle();

		if (!$description)
			$description = $page->getDescription();

		if (!$keywords)
			$keywords = $page->getKeywords();
	}


	$h1 = wdpro_the_h1(true, false);
	$template = function ($string, $key) use (&$h1) {
		if (!$string) {
			$string = wdpro_get_option($key.'[lang]');
			$string = str_replace('[h1]', $h1, $string);
		}

		return $string;
	};

	$title = $template($title, 'wdpro_title_template');
	$description = apply_filters('wdpro_description', $description);
	$description = $template($description, 'wdpro_description_template');

	// Применяем фильтры для description
	$description = apply_filters('wdpro_description_2', $description);
	$keywords = $template($keywords, 'wdpro_keywords_template');
	if (!$title) {
		$title = wdpro_the_title_standart();
	}
	$title = apply_filters('wdpro_title_2', $title);

	if ($description === '-') {
		$description = '';
	}


?><title><?php echo($title); ?></title>
	<meta name="description" content="<?php echo( htmlspecialchars($description) );
	?>" />

	<?php if ($keywords): ?>
	<meta name="keywords" content="<?php echo( htmlspecialchars($keywords) ); ?>" />
	<?php endif; ?>

	<meta charset="<?php bloginfo( 'charset' ); ?>" />

	<?php if (wdpro_data('noindex')):?>
	<meta name="googlebot" content="noindex">
	<?php endif; ?>

<?php

	// Og
	if (\Wdpro\Modules::existsWdpro('og')) {
		echo \Wdpro\Og\Controller::getHeaderTags([
			'title' => $title,
			'description' => $description,
		]);
	}


	// Дополнительные head теги из админки
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
 * @param bool $applyFilters2 Применить фильтр 2 группы
 * @return bool|null|string
 */
function wdpro_the_h1($force=false, $applyFilters2=true)
{
	$h1 = wdpro_data('h1');

	if (!$h1)
	$h1 = wdpro_get_post_meta('h1');

	if ($h1 != '-' && $h1 != '—' || $force)
	{
		$h1 = apply_filters('wdpro_h1', $h1);
		if (!$h1) {
			$page = wdpro_current_page();
			if ($page) {
				$h1 = $page->getH1(false);
			}
		}
		if (!$h1)
		{
			$h1 = wdpro_the_title_standart();
		}
		if ($applyFilters2) {
			$h1 = apply_filters('wdpro_h1_2', $h1);
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
function wdpro_get_post_meta($metaName, $postId=null)
{
	if ($postId === null) $postId = get_the_ID();

	$arr = get_post_meta($postId, $metaName.\Wdpro\Lang\Data::getCurrentSuffix());

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
