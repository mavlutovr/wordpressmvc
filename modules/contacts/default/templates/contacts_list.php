<?foreach ($data['list'] as $item): ?>

	<?=$item['text']?>

	<?php if ($item['map'])?>
	<div class="cont_map"><?=$item['map']?></div>
<?endforeach;?>
