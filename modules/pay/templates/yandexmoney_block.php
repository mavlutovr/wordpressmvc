<form method="POST" action="https://money.yandex.ru/quickpay/confirm.xml">

	<input type="hidden" name="receiver" value="<?=$data['receiver']?>">
	<input type="hidden" name="formcomment" value="<?=$data['text']?>">
	<input type="hidden" name="short-dest" value="<?=$data['text']?>">
	<input type="hidden" name="label" value="<?=$data['targetId']?>">
	<input type="hidden" name="quickpay-form" value="donate">
	<input type="hidden" name="targets" value="транзакция <?=$data['targetId']?>">
	<input type="hidden" name="sum" value="<?=$data['cost']?>" data-type="number">
	<input type="hidden" name="comment" value="<?=$data['text']?>">
	<input type="hidden" name="need-fio" value="true">
	<input type="hidden" name="need-email" value="true">
	<input type="hidden" name="need-phone" value="false">
	<input type="hidden" name="need-address" value="false">

	<div class="g-flex">

		<!-- Яндекс.Деньгами -->
		<div>
			<button name="paymentType" value="PC" type="submit" class="yandex-money-form-choise yandex-money-form-money">
				<span class="yandex-money-form-icon"><span></span></span>
				<span class="yandex-money-form-text">Яндекс.Деньгами</span>
			</button>
		</div>

		<!-- Банковской картой -->
		<div>
			<button name="paymentType" value="AC" type="submit" class="yandex-money-form-choise yandex-money-form-card">
				<span class="yandex-money-form-icon"><span></span></span>
				<span class="yandex-money-form-text">Банковской картой</span>
			</button>
		</div>

	</div>




</form>
