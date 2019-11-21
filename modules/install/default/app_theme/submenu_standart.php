<ul>
	<?php foreach ($data['list'] as $button): ?>
		<li><?=$button['before']?><a href="<?=$button['url']?>" class="<?

			// Активность кнопки
			if ($button['breadcrumbs']):	?>breadcrumbs <?endif;

			// Мы прямо на этой странице
			if ($button['current']): ?>current <?endif;

			?>" <?=$button['attrs']?>><?=$button['post_title']?></a><?=$button['after']?></li>
	<?php endforeach; ?>
</ul>
