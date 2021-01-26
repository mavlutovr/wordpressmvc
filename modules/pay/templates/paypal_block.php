<!-- PayPal -->
<form action="<?= $data['button_url'] ?>" method="post">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="business" value="<?= $data['email'] ?>">
    <input id="paypalItemName" type="hidden" name="item_name" value="<?= $data['item_name']?>">
    <input id="paypalQuantity" type="hidden" name="quantity" value="<?= $data['items_quanity'] ?>">
    <input id="paypalAmmount" type="hidden" name="amount" value="<?= $data['cost'] ?>">
    <input type="hidden" name="no_shipping" value="1">
    <input type="hidden" name="return" value="<?= $data['return_url'] ?>">

    <input type="hidden" name="custom" value="<?= json_encode($data['custom_data']) ?>">

    <input type="hidden" name="currency_code" value="<?= $data['currency_code'] ?>">
    <input type="hidden" name="lc" value="US">
    <input type="hidden" name="bn" value="PP-BuyNowBF">

    <button type="submit">PayPal</button>
 </form>