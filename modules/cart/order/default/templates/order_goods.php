<!-- Список товаров на странице заказа -->

<div class="order-goods">

	<h3>Товары</h3>
	<?php /*print_r($data);*/ ?>

	<?php foreach ($data['list'] as $item): ?>

		<p><?=$item['good']['post_title']?>, размер: <?=$item['keyArray']['object']['size']?>, <?=$item['count']?> шт., стоимость: <?=$item['cost_for_all'] * 1?> рублей.</p>

	<?php endforeach; ?>

	<h3>Стоимость товаров: <span class="js-cost-space"><?=$data['cost']?></span> руб.</h3>

	<h3>Скидка: <span class="js-cost-space"><?=$data['discount']?></span> руб.</h3>

	<h3>Итого: <span class="js-cost-space"><?=$data['total']?></span> руб.</h3>
</div>