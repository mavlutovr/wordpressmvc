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
		<p>[statusText] - Текущий статус заказа.</p>
		<p>[cart] - Список товаров с итоговой ценой.</p>
		<p>[formData] - Данные, которые указал покупатель.</p>
		<p>[url] - Адрес карточки заказа.</p>',
];