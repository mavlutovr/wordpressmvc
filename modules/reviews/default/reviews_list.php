<?foreach ($data['list'] as $item): ?>
	<div class="reviews_item">
		<?php if ($item['avatar']):?>
			<div class="reviews_avatar">
				<img src="<?=WDPRO_UPLOAD_IMAGES_URL ."lit/".$item['avatar']?>">
			</div>
		<? endif;?>


		<div class="reviews_txt">
			<div class="reviews_heading">
				<?=$item['heading']?>
			</div>

			<?=$item['text']?>
			<?php if ($item['name'])?>
			<div class="user_name">
				<?=$item['name']?>
			</div>
		</div>
	</div>
<?endforeach;?>