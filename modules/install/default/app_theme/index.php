<?php get_header(); ?>


	<div class="container">

		<!-- Хлебные крошки -->
		<?= wdpro_the_breadcrumbs() ?>

		<!-- Заголовок H1 -->
		<?php if ( $h1 = wdpro_the_h1() ): ?>
			<h1><?= $h1 ?></h1>
		<?php endif; ?>

		<article class="page-content">
			<!-- Текст (содержимое) страницы -->
			<?= wdpro_the_content(); ?>

		</article>

	</div>


<?php get_footer(); ?>