wdpro.ready($ => {

  $('#js-paypal-method').each(function () {
    const $button = $(this);

    // Get link
    $button.on('click', function () {
      $button.loading();

      wdpro.ajax(
        {
          action: 'paypal_get_pay_button',
          in: wdpro.payIn,
          sw: wdpro.paySw,
        },

        res => {
          $button.loadingStop();

          if (res['error']) {
            alert(res['error']);
            return;
          }

          let dialog = new wdpro.dialogs.Dialog({
            title: 'Pay via PayPal',
            content: res['form'],
            substrate: true,
          });
        }
      );
    });
  });

});