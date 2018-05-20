<?php foreach($data as $item): ?>
	<p><a href="<?php echo home_url($item['post_name']); ?>" class="a_big"><?php echo
			$item['post_title']; ?></a></p>
<?php endforeach; ?>