<?php if ($data['added']): ?>
	<div class="js-cart-control cart-control" data-key="<?=$data['key']?>">
		<button class="js-cart-control-button" data-delta="-1">-</button>
		<input type="number" min="0" class="js-cart-control-count wdpro-form-input" value="<?=$data['added']['count']?>">
		<button class="js-cart-control-button" data-delta="1">+</button>
	</div>


<?php else: ?>
	<div class="js-cart-control" data-key="<?=$data['key']?>">
		<button class="js-cart-control-button" data-count="1">Добавить в корзину</button>
	</div>
<?php endif; ?>