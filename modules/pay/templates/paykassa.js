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
            $pay.loadingStop();

            if (res['error']) {
              if (res['error'] == '1') {
                res['error'] = 'Something went wrong. Try a different payment method.'
              }
              alert(res['error']);
              return;
            }

            
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
            });
            dialog.show();
          }
        );
      });
    });
  });

});