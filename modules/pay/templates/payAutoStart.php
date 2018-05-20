<!-- Шаблон стартового блока оплаты -->

<div id="js-pay-start" JS_id="{{ id }}" style="display: none">

	<!-- Перебираем методы -->
	<?php foreach($data['methods'] as $method): ?>

	<!-- 1 метод -->
	<?php echo($method); ?>

	<?php endforeach; ?>

</div>