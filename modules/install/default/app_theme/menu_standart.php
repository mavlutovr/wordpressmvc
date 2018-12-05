
<ul>
	<?php foreach ( $list as $button ): ?>
		<li>
			<?=$button['before']?>
			<a href="<?=$button['url']?>" class="<?

				// Активность кнопки
				if ($button['breadcrumbs']):	?>breadcrumbs <?endif;

				// Мы прямо на этой странице
				if ($button['current']): ?>current <?endif;

			?>" <?=$button['attrs']?>><?=$button['post_title']?></a>
			<?=$button['after']?>
			<?=$button['submenu']?></li>
	<?php endforeach; ?>
</ul>

