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
          <div class="js-copy-text"><?= $data['pay_amount']?></div> 
          <span class="crypt-payment--currency"><?= $data['pay_currency']?></span>
        </span>
      </div>

      <div class="crypt-payment--row">
        <span class="crypt-payment--label">Address:</span>
        <span class="crypt-payment--value">
          <div class="js-copy-text"><?= $data['pay_address']?></div>
        </span>
      </div>

    </div>


    <div class="crypt-payment--right">
      <span id="js-payment-qrcode" data-query="<?= $data['query'] ?>"></span>
      <span class="js-status-process-container"></span>
    </div>

  </div>


  <hr>


  <p>
    Status: <span class="crypt-payment--status crypt-payment--status--<?= $data['payment_status']?>"><?= $data['payment_status']?></span>
  </p>

</div>