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

	<?php if ( is_array($item['tags']) ): ?>
		<div class="blog_tags_wr">
			<div class="blog_tags_icn"></div>
			<div class="blog_tags">

				<?php foreach ( $item['tags'] as $tag ): ?>
					<a href="<?= wdpro_home_url_with_lang() . 'blog_tag_list/?tags=' .
					             urlencode($tag) ?>"
					   class="tl-tag"><span><?= $tag ?></span></a>
				<?php endforeach; ?>


			</div>
		</div>
	<?endif; ?>

	<!-- Всякие сервисы -->
	<div class="blog-codes"><?php echo wdpro_get_option('blog_codes'); ?></div>
</div>
