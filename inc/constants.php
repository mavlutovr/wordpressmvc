<?php

// Папка плагина
define('WDPRO_DIR', realpath(__DIR__.'/../').'/');
define('WDPRO_PATH', WDPRO_DIR);
define('WDPRO_URL', plugins_url('wordpressmvc').'/');
define('WDPRO_CONSOLE_URL', admin_url());

// Css классы синей кнопки
// http://screenshot3.seobit.ru/roma.2015.09.02___19:32:1441211575.png
define('WDPRO_BUTTON_CSS_CLASS', 'button button-primary button-large');

// Иконки
define('WDPRO_ICONS_PAGES', 'fa-file-o far fa-file');
define('WDPRO_ICONS_PRODUCTS', 'dashicons-products');
define('WDPRO_ICONS_CART', 'dashicons-cart');

// Роль администратора
define('WDPRO_ADMINISTRATOR', 'administrator');


// Форма
// Настройки маленького редактора
define('WDPRO_FORM_CKEDITOR_SMALL', 'small');


// Время
// Количество секунд в сутках
define('WDPRO_DAY', 60*60*24);

// Корневая папка темы
define('WDPRO_TEMPLATE_PATH', get_stylesheet_directory().'/');
define('WDPRO_TEMPLATE_URL', get_template_directory_uri().'/');

// Папка загрузок
define('WDPRO_UPLOAD_URL', wdpro_upload_dir_url());
define('WDPRO_UPLOAD_IMAGES_URL', WDPRO_UPLOAD_URL.'images/');
define('WDPRO_UPLOAD_FILES_URL', WDPRO_UPLOAD_URL.'files/');
define('WDPRO_UPLOAD_PATH', wdpro_upload_dir_path());
define('WDPRO_UPLOAD_IMAGES_PATH', WDPRO_UPLOAD_PATH.'images/');

// Папка приложения
define('APP_PATH', wdpro_realpath(__DIR__.'/../../app/').'/');
