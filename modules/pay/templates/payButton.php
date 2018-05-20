<?php if ($data['status'] == 1):
	?><span class="bold"><?php echo($data['payed_label']); ?></span><?php 
else:
	?><a href="<?php echo($data['http']); ?>" class="pay-button"><?php echo
($data['pay_label']);	?></a><?php
endif; ?>
