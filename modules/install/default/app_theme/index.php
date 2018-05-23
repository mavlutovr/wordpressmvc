<?php get_header(); ?>

	<div class="wrapper">

		<?php echo( wdpro_breadcrumbs()->getHtml() ); ?>

		<div class="page-middle">
			<?php get_sidebar(); ?>
			<article class="page-content">
				<?php if ( $h1 = wdpro_the_h1() ): ?>
					<h1><?php echo( $h1 ); ?></h1>
				<?php endif; ?>
				<?php wdpro_the_content(); ?>
			</article>
		</div>

	</div>


<?php get_footer(); ?>