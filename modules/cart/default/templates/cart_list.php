<div class="cart-list">

	<!-- Товары -->
	<?php foreach ($list as $item): ?>

		<?=$item['html']?>

	<?php endforeach; ?>


</div>


<!-- Итого -->
<p>Итого: <span class="js-cost-space" id="js-cart-cost"><?= $info['cost'] ?></span> руб.</p>

<!-- Оформить заказ -->
<p><a href="<?= home_url() ?>/checkout/"><button>Оформить заказ</button></a></p>