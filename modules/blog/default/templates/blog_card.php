<?php
// Карточка статьи блога
?>
<div class="blog-card">
	<?php
	if ($date_added): ?>
		<p class="blog-date">
	<span class="glog-date-added">
		Опубликовано: <?php echo wdpro_date($date_added); ?>.
	</span>
			<?php if ($date_edited): ?>
				<span class="glog-date-edited">
			Обновлено: <?php echo wdpro_date($date_added); ?>.
		</span>
			<?php endif; ?>
		</p>
	<?php endif; ?>

	<!-- Текст статьи -->
	<div class="blog-text"><?php echo $post_content; ?></div>

	<!-- Всякие сервисы -->
	<div class="blog-codes"><?php echo wdpro_get_option('blog_codes'); ?></div>
</div>
