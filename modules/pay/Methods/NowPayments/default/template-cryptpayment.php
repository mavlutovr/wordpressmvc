<div class="crypt-payment" id="js-nopayments-payment">

  <div class="cypt-payment--order">Order: <?= $data['order']?></div>

  <div class="crypt-payment--colls">

    <div class="crypt-payment--left">

      <div class="crypt-payment--row">
        <span class="crypt-payment--label">Price:</span>
        <span class="crypt-payment--value"><?= $data['price_amount']?> <span class="crypt-payment--currency"><?= $data['price_currency']?></span></span>
      </div>

      <div class="crypt-payment--row">
        <span class="crypt-payment--label">Amount:</span>
        <span class="crypt-payment--value">
          <span class="js-copy-text"><?= $data['pay_amount']?></span> 
          <span class="crypt-payment--currency"><?= $data['pay_currency']?></span>
        </span>
      </div>

      <div class="crypt-payment--row">
        <span class="crypt-payment--label">Address:</span>
        <span class="crypt-payment--value">
          <span class="js-copy-text"><?= $data['pay_address']?></span>
        </span>
      </div>

      <div class="crypt-payment--row">
        <span class="crypt-payment--label">Valid:</span>
        <span class="crypt-payment--value">
          <span><?= date('Y-m-d, H:i', $data['valid_until']) ?></span>
          <span class="crypt-payment--until--container">
            (<span class="js-countdown crypt-payment--until" data-until="<?=$data['valid_until']?>"></span>)
            <?php if ($data['valid_until'] < time()): ?>
              <div class="red">Time is up invoice is not longer valid.</div>
            <?php endif; ?>
          </span>
        </span>
      </div>

      <p><i class="fas fa-wallet"></i> <a href="<?= $data['query'] ?>">Open wallet</a></p>

    </div>


    <div class="crypt-payment--right">
      <span id="js-payment-qrcode" data-query="<?= $data['query'] ?>"></span>
      <span class="js-status-process-container"></span>
      <!-- <p><a href="<?= $data['query'] ?>">Open wallet</a></p> -->
    </div>

  </div>


  <hr>


  <p>
    Status: <span class="inline-block"><span class="crypt-payment--status crypt-payment--status--<?= $data['payment_status']?> js-status"><?= $data['payment_status']?></span></span>

    <span class="crypt-payment--status--update"><i class="fas fa-sync-alt"></i> <span class="js-update--seconds"></span></span>
  </p>

</div>

<script src="<?=WDPRO_TEMPLATE_URL?>node_modules/jquery-countdown/dist/jquery.countdown.min.js"></script>
