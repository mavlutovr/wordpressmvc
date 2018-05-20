<!-- Шаблон подтверждения демо метода оплаты -->

<div>Сумма: <?php echo($data['post']['cost']); ?> руб.</div>

<form action="" method="POST" enctype="multipart/form-data" class="pay_method_demo_confirm_form" id="js-pay-demo-confirm-form">
	<input type="hidden" name="confirm[id]" value="<?php echo($data['post']['id']); ?>" />
	<input type="hidden" name="confirm[confirm]" value="1" class="JS_confirm" />
	<BR><BR>
	<input type="button" value="Отмена" id="JS_pay_method_demo_cancel" 
	       class="wdpro-form-button" />
	<input type="submit" value="Подтверждаю" class="wdpro-form-submit" />
</form>
