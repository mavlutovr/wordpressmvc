<?foreach ($data['list'] as $item): ?>

	<?=$item['text']?>

	<?php if ($item['map'])?>
	<div class="contacts-map"><?=$item['map']?></div>
<?endforeach;?>
