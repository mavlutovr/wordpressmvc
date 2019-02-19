<?php
/* Template Name: Пустой бланк */
?>
<!DOCTYPE html>
<html>
<head>
	<?php wdpro_the_header(); ?>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>


<!-- Заголовок H1 -->
<?php if ( $h1 = wdpro_the_h1() ): ?>
	<h1><?= $h1 ?></h1>
<?php endif; ?>

<!-- Текст (содержимое) страницы -->
<?= wdpro_the_content(); ?>


<?php wp_footer(); ?>
</body>
</html>
