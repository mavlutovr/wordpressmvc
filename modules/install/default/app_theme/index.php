<?php get_header(); ?>

	<div class="wrapper">

		<!-- Хлебные крошки -->
		<?= wdpro_the_breadcrumbs() ?>

		<div class="page-middle">

			<!-- Блок сбоку страницы -->
			<?php get_sidebar(); ?>

			<article class="page-content">

				<!-- Заголовок H1 -->
				<?php if ( $h1 = wdpro_the_h1() ): ?>
					<h1><?= $h1 ?></h1>
				<?php endif; ?>

				<!-- Текст (содержимое) страницы -->
				<?= wdpro_the_content(); ?>

			</article>
		</div>

	</div>


<?php get_footer(); ?>