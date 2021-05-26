wdpro.ready($ => {

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
            if (res['error']) {
              alert(res['error']);
            }
            
            $pay.loadingStop();
            console.log('res', res);
          }
        );
      });
    });
  });

});