<!DOCTYPE html>
<html>
<head>
	<?php wdpro_the_header(); ?>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php if ( is_active_sidebar( 'header-1' ) ) : ?>
	<div id="widget-area" class="widget-area" role="complementary">
		<?php dynamic_sidebar( 'header-1' ); ?>
	</div><!-- .widget-area -->
<?php endif; ?>

