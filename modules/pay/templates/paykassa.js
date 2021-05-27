wdpro.ready($ => {

  // https://stackoverflow.com/a/45184412/6694099
  const toNonExponential = value => {
    // if value is not a number try to convert it to number
    if (typeof value !== "number") {
      value = parseFloat(value);

      // after convert, if value is not a number return empty string
      if (isNaN(value)) {
        return "";
      }
    }

    var sign;
    var e;

    // if value is negative, save "-" in sign variable and calculate the absolute value
    if (value < 0) {
      sign = "-";
      value = Math.abs(value);
    }
    else {
      sign = "";
    }

    // if value is between 0 and 1
    if (value < 1.0) {
      // get e value
      e = parseInt(value.toString().split('e-')[1]);

      // if value is exponential convert it to non exponential
      if (e) {
        value *= Math.pow(10, e - 1);
        value = '0.' + (new Array(e)).join('0') + value.toString().substring(2);
      }
    }
    else {
      // get e value
      e = parseInt(value.toString().split('e+')[1]);

      // if value is exponential convert it to non exponential
      if (e) {
        value /= Math.pow(10, e);
        value += (new Array(e + 1)).join('0');
      }
    }

    // if value has negative sign, add to it
    return sign + value;
  }



  $('#js-pay-crypts').each(function () {
    $(this).find('.js-pay-crypt').each(function () {
      const $pay = $(this);
      const id = $pay.data('id');
      const key = $pay.data('key');

      $pay.on('click', () => {
        $pay.loading();

        wdpro.ajax(
          {
            action: 'paykassa_get_link',
            in: wdpro.payIn,
            sw: wdpro.paySw,
            currencyId: id,
            currencyKey: key,
          },

          res => {
            $pay.loadingStop();

            if (res['error']) {
              if (res['error'] == '1') {
                res['error'] = 'Something went wrong. Try a different payment method.'
              }
              alert(res['error']);
              return;
            }

            res['amountCrypt'] = toNonExponential(res['amountCrypt'])

            
            let $content = $(paykassaTemplates.goToPaykassa({
              name: res['currency']['name'],
              image: wdpro.WDPRO_HOME_URL
                + 'wp-content/plugins/wordpressmvc/modules/pay/Methods/images/'
                + res['currency']['image'],
              amount: res['amount'],
              amountCrypt: res['amountCrypt'],
              url: res['url'],
            }));

            let dialog = new wdpro.dialogs.Dialog({
              content: $content,
              substrate: true,
            });
            dialog.show();
          }
        );
      });
    });
  });

});