<?php

// Инициализация виджетов (не обязательно)
function widgetsInit() {
	register_sidebar( array(
		'name'          => 'Шапка',
		'id'            => 'header-1',
		'description'   => 'Перетащите сюда виджеты, чтобы добавить их в шапку',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}

add_action( 'widgets_init', 'widgetsInit');


