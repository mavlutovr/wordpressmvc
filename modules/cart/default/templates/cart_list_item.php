<!-- Товар в корзине -->
<div class="cart-list-item js-cart-control" data-key="<?=$data['key']?>">


	<!-- Фото -->
	<a href="<?= home_url() . '/' . $data['good']['post_name'] ?>"><img
			src="<?= WDPRO_UPLOAD_IMAGES_URL . $data['good']['image'] ?>" alt="<?=$data['good']['post_title']?>"></a>


	<!-- Описание -->
	<div>
		<p><?= $data['good']['post_title'] ?></p>

		<?= $data['good']['params'] ?>
	</div>


	<!-- Цена за шт. -->
	<div>
		<p>Цена за шт. <span class="js-cost-space"><?= $data['cost_for_one'] ?></span> руб.</p>
	</div>


	<!-- Кол-во -->
	<div class="cart-coll-4">

		<p><span class="cart-count-plus js-cart-control-button" data-delta="1">Добавить</span></p>

		<p>Количество: <span class="js-cost-space"><?= $data['count'] ?></span> шт.</p>
		<input type="hidden"
		       data-step="<?=$data['good']['pack_count']?>"
		       data-min="<?=$data['good']['pack_count']?>"
		       class="js-cart-control-count"
		       data-remove-confirm="Удалить товар из корзины?"
		       value="<?=($data['count']) ? $data['count'] : '0'?>">

		<p><span class="cart-count-minus js-cart-control-button" data-delta="-1">Убавить</span></p>

	</div>


	<!-- Стоимость -->
	<div>
		<p>Стоимость</p>
		<p><span class="js-cost-space"><?= $data['cost_for_all'] ?></span> руб.</p>
	</div>


	<!-- Удалить -->
	<div>
		<button class="cart-remove-button js-cart-control-button"
		        data-count="0"
		        title="Удалить">Удалить</button>
	</div>

</div>