<ul>
	<?php foreach ( $data['list'] as $item ): ?>
		<li><a href="<?=$item['url']?>"><?=$item['post_title']?></a></li>
	<?php endforeach; ?>
</ul>
