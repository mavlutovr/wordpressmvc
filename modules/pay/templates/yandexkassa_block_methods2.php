<div class="g-align-center pay_form pay-yandex-kassa-methods">
	<div class="g-inline-block g-align-left">
		<!-- Значения всех полей условны и приведены исключительно для примера -->
		<form action="<?php echo $data['action']; ?>" method="post" id="js-yandex-kassa-methods">

			<!-- Обязательные поля -->
			<input name="shopId" value="<?php echo $data['shopId']; ?>" type="hidden"/>
			<input name="scid" value="<?php echo $data['scid']; ?>" type="hidden"/>
			<input name="sum" value="<?php echo $data['cost']; ?>" type="hidden">
			<input name="customerNumber" value="<?php echo $data['customerNumber']; ?>"
			       type="hidden"/>

			<!-- Необязательные поля -->
			<?php if (isset($data['shopArticleId'])): ?>
				<input name="shopArticleId" value="<?php echo $data['shopArticleId']; ?>" type="hidden" />
			<?php endif; ?>

			<!-- Методы оплаты -->
			<?php foreach($data['methods'] as $methodKey=>$methodText): ?>
				<div class="pay-yandex-kassa-methods-row">
					<label><input type="radio" name="paymentType" value="<?php echo $methodKey; ?>" class="js-yandex-method" />
						<?php echo $methodText; ?></label>
				</div>
			<?php endforeach; ?>


			<input name="orderNumber" value="<?php echo $data['id']; ?>" type="hidden"/>
			<input name="cps_phone" value="" type="hidden"/>
			<input name="cps_email" value="<?php echo $data['email']; ?>" type="hidden"/>
			<input name="orderDetails" value="<?php
				echo htmlspecialchars($data['text']); ?>" type="hidden"/>
			<input name="shopSuccessURL" value="<?php echo $data['shopSuccessURL']; ?>" type="hidden"/>
			<input name="shopFailURL" value="<?php echo $data['shopFailURL']; ?>" type="hidden"/>

			<div class="g-align-center pay-yandex-kassa-methods-submit-container">
				<input type="submit" value="Перейти к оплате" class="submit 
				wdpro-form-submit" />
			</div>
		</form>
	</div>
</div>
