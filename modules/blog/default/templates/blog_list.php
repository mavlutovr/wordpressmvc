<?php
// Список статей блога
?>
	<div class="blog-list"><?php

		foreach($list as $item): ?>
			<div class="blog-item">
				<div class="blog-item-row">
					<!-- Картинка -->
					<?php if (isset($item['image']) && $item['image']): ?>
						<div class="blog-row-1 blog-item-coll">
							<a href="<?php echo home_url($item['post_name']); ?>"><img
									src="<?php
									if (strstr($item['image'], 'http://')):
										echo $item['image'];
									else:
										echo WDPRO_UPLOAD_IMAGES_URL.'/lit/'.$item['image'];
									endif;  ?>"
								/></a>
						</div>
					<?php endif; ?>

					<!-- Справа -->
					<div class="glog-row-2 blog-item-coll">
						<!-- Название статьи -->
						<div class="blog-row-name">
							<a href="<?php echo home_url($item['post_name']); ?>"><?php
								echo $item['post_title']; ?></a>
						</div>

						<!-- Даты -->
						<?php if ($item['date_added']): ?>
							<p class="blog-date">
				<span class="glog-date-added">
					Опубликовано: <?php echo wdpro_date($item['date_added']); ?>.
				</span>
								<?php if ($item['date_edited']): ?>
									<span class="glog-date-edited">
						Обновлено: <?php echo wdpro_date($item['date_added']); ?>.</span>
								<?php endif; ?>
							</p>
						<?php endif; ?>

						<!-- Анонс -->
						<?php echo $item['anons']; ?>
					</div>
					<div class="g-clear"></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

<?php echo $pagination; ?>