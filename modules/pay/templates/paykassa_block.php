<div class="pay-method-block pay-method--crypts" id="js-pay-crypts">

	<?php foreach ($data['currencies'] as $currencyData): ?>
    <div>
      <div class="pay-method--crypt js-pay-crypt" data-id="<?= $currencyData['id'] ?>" data-key="<?= $currencyData['key'] ?>">
        <img src="<?=WDPRO_URL?>modules/pay/Methods/images/<?=$currencyData['image']?>" alt="">
        <span><?= $currencyData['name'] ?></span>
      </div>
    </div>
  <?php endforeach; ?>
</div>