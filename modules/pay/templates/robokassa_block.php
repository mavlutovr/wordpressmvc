<form action="<?php echo $data['action']; ?>" method="post">
	<input type="hidden" name="MerchantLogin" value="<?php echo $data['MerchantLogin']; ?>" />
	<input type="hidden" name="OutSum" value="<?php echo $data['OutSum']; ?>" />
	<input type="hidden" name="InvId" value="<?php echo $data['InvId']; ?>" />
	<input type="hidden" name="Desc" value="<?php echo htmlspecialchars($data['Desc']);
	?>" />
	<input type="hidden" name="SignatureValue" value="<?php echo $data['SignatureValueMd5']; ?>" />
	<input type="hidden" name="IncCurrLabel" value="<?php echo $data['IncCurrLabel']; ?>" />
	<input type="hidden" name="Culture" value="<?php echo $data['Culture']; ?>" />
	<input type="submit" value="Robokassa" class="wdpro-form-submit pay-form-submit" />
</form>