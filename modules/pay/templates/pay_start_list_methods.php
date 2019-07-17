<!-- Шаблон списка методов оплаты -->

<!-- Заказ -->
<?=$data['target_confirm_info']?>

<!-- Методы оплаты -->
<div>

	<!-- Перебираем методы -->
	<?php foreach($data['methods'] as $method): ?>

	<!-- 1 метод -->
	<?php echo($method); ?>

	<?php endforeach; ?>

</div>