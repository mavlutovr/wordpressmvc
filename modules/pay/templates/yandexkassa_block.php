<!-- Значения всех полей условны и приведены исключительно для примера -->
<form action="<?php echo $data['action']; ?>" method="post" id="js-yandex-kassa">
	
	<!-- Методы оплаты -->
	<!--<div class="g-hid js-data"><?php /*echo $data['data']; */?></div>-->

	<!-- Обязательные поля -->
	<input name="shopId" value="<?php echo $data['shopId']; ?>" type="hidden"/>
	<input name="scid" value="<?php echo $data['scid']; ?>" type="hidden"/>
	<input name="sum" value="<?php echo $data['cost']; ?>" type="hidden">
	<input name="customerNumber" value="<?php echo $data['customerNumber']; ?>" 
	type="hidden"/>

	<!-- Необязательные поля -->
	<?php if (isset($data['shopArticleId'])): ?>
	<input name="shopArticleId" value="<?php echo $data['shopArticleId']; ?>" type="hidden"/>
	<?php endif; ?>
	<input name="paymentType" value="<?php echo $data['method']; ?>" type="hidden" class="js-method" />
	<input name="orderNumber" value="<?php echo $data['id']; ?>" type="hidden"/>
	<input name="cps_phone" value="" type="hidden"/>
	<input name="cps_email" value="<?php echo $data['email']; ?>" type="hidden"/>
	<input name="orderDetails" value="<?php echo $data['text']; ?>" type="hidden"/>
	<input name="shopSuccessURL" value="<?php echo $data['shopSuccessURL']; ?>" type="hidden"/>
	<input name="shopFailURL" value="<?php echo $data['shopFailURL']; ?>" type="hidden"/>

	<div class="g-inline-block">
		<input type="submit" value="Яндекс.Касса" class="wdpro-form-submit pay-form-submit" />
	</div>
</form>