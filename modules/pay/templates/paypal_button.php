<?php
  // https://developer.paypal.com/docs/paypal-payments-standard/ht-test-pps-buttons/#create-test-buttons-in-the-sandbox
?>
<form action="<?= $data['url'] ?>" method="post">
  <input type="hidden" name="cmd" value="_xclick">
  <input type="hidden" name="business" value="<?= $data['business'] ?>">
  <input type="hidden" name="lc" value="US">
  <input type="hidden" name="item_name" value="<?= $data['comment'] ?>">
  <input type="hidden" name="amount" value="<?= $data['amount'] ?>">
  <input type="hidden" name="currency_code" value="<?= $data['currency_code'] ?>">
  <input type="hidden" name="button_subtype" value="services">
  <input type="hidden" name="no_note" value="1">
  <input type="hidden" name="no_shipping" value="1">
  <input type="hidden" name="rm" value="1">
  <input type="hidden" name="return" value="<?= $data['return_url'] ?>">
  <input type="hidden" name="cancel_return" value="<?= $data['cancel_url'] ?>">
  <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHosted">
  <input type="hidden" name="item_number" value="<?= $data['item_number'] ?>">
  <input type="hidden" name="charset" value="utf8">

  <p>Payment is made in Russian rubles at the current exchange rate against the dollar.</p>

  <p>When paying, the currency of your card will be converted into rubles.</p>

  <p class="center">
    <span class="paypal-currency-rate">
      <span><span class="currency">$</span><?= $data['usd'] ?></span>
      &nbsp;=&nbsp;
      <span><span class="rub"><span class="currency">&#8381;</span></span><?= $data['amount'] ?></span>
    </span>
  </p>

  <p class="center">
    <a href="https://www.xe.com/currencyconverter/convert/?Amount=<?= $data['usd']?>&From=USD&To=RUB" target="_blank"><i class="fas fa-external-link-square-alt"></i> Check currency rate</a>
  </p>

  <p class="mb0"><button type="submit" class="w100 mb0">Continue <i class="fas fa-chevron-right"></i></button></p>
  
</form>

