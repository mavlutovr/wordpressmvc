<?php if ($data['added']): ?>
<div class="js-cart-entity cart-entity" data-key="<?=$data['entity']['key']?>">
	<button>-</button>
	<input type="number" min="0" class="js-cart-count">
	<button>+</button>
</div>

<?php else: ?>
<button class="js-cart-add" data-key="<?=$data['entity']['key']?>">Добавить в корзину</button>

<?php endif; ?>
