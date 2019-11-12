<?php
return [
	'subject'=>'Заказ № [id]',

	'text'=>'
<div class="order-card-content">
	
	<h2>Ваш заказ № [id] оформлен</h2>
	
	<p>Ссылка на заказ: <a href="[url]" target="_blank">[url]</a></p>
	
	<div>[cart]</div>
	
	<h2>Ваши данные</h2>
	
	<div>[formData]</div>

</div>
	',

	'info'=>'
[statusText] - Текущий статус заказа.
[cart] - Список товаров с итоговой ценой.
[formData] - Данные, которые указал покупатель.
[url] - Адрес карточки заказа.',
];