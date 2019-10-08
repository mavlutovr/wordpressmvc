<div class="cart-list">

	<!-- Заголовки -->
	<div class="cart-list-header">
		<div>Фото</div>
		<div>Описание</div>
		<div>Цена за шт.</div>
		<div>Кол-во</div>
		<div>Стоимость</div>
	</div>

	<!-- Товары -->
	<?php foreach ($list as $cart): ?>

	<!-- Товар -->
	<div class="cart-list-item">

		<!-- Фото -->
		<a href="<?=home_url().'/'.$cart['item']['post_name']?>"><img src="<?=WDPRO_UPLOAD_IMAGES_URL.$cart['item']['image']?>" alt=""></a>

		<!-- Описание -->
		<div>
			<div class="h3"><?=$cart['item']['post_name']?></div>

			<div></div>
		</div>

	</div>
	<?php endforeach; ?>


	<table>

		<!-- Заголовки -->
		<tr>
			<th>Фото</th>
			<th>Описание</th>
			<th>Цена за шт.</th>
			<th>Кол-во</th>
			<th>Стоимость</th>
			<th></th>
		</tr>

		<!-- Товары -->
		<?php foreach ($list as $item): ?>

		<?php endforeach; ?>
	</table>
</div>