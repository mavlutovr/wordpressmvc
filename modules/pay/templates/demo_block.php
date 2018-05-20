<!-- Шаблон блока демо оплаты на главной странице -->

<div class="inline_block JS_pay_method">
	<form action="<?php echo(home_url('pay_demo')); ?>" method="POST" 
	enctype="multipart/form-data">
		<input type="hidden" class="JS_pay_cost" name="cost" 
		       value="<?php echo($data['cost']); ?>" />
		<input type="hidden" name="id" value="<?php echo($data['id']); ?>" />
		<div><input type="submit" value="Demo" 
		            class="pay_method_submit wdpro-form-submit pay-form-submit" /></div>
	</form>
</div>

