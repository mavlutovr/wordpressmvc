<ul>
	<?php foreach ($data['list'] as $item): ?>
		<li><?=$button['before']?><a href="<?=$item['url']?>" class="<?

			// Активность кнопки
			if ($button['breadcrumbs']):	?>breadcrumbs <?endif;

			// Мы прямо на этой странице
			if ($button['current']): ?>current <?endif;

			?>" <?=$button['attrs']?>><?=$item['post_title']?></a><?=$button['after']?></li>
	<?php endforeach; ?>
</ul>
