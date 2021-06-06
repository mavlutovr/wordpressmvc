wdpro.ready($ => {

  $('#js-paypal-method').each(function () {
    const $button = $(this);

    // Get link
    $button.on('click', function () {
      $button.loading();

      wdpro.ajax(
        {
          action: 'paypal_get_pay_data',
          in: wdpro.payIn,
          sw: wdpro.paySw,
        },

        res => {
          console.log(res);
        }
      );
    });
  });

});