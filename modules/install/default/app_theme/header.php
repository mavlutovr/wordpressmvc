<!DOCTYPE html>
<html>
<head>
	<?php wdpro_the_header(); ?>
	<?php wp_head(); ?>
	<meta name=viewport content="width=device-width, initial-scale=1.0, user-scalable=no">
</head>
<body <?php body_class(); ?>>

<!-- Место для виджетов -->
<?php if ( is_active_sidebar( 'header-1' ) ) : ?>
	<div id="widget-area" class="widget-area" role="complementary">
		<?php dynamic_sidebar( 'header-1' ); ?>
	</div><!-- .widget-area -->
<?php endif; ?>
<!-- /Место для виджетов -->
