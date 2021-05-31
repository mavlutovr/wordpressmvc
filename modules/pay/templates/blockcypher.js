
wdpro.ready($ => {

  $('#js-nopayments-methods').each(function () {
    const $container = $(this);

    const priceForMonth = Number($('#js-price').text());

    $container.find('.js-method').each(function () {
      const $button = $(this);
      const key = $button.data('key');
      const id = $button.data('id');

      $button.on('click', function () {
        $button.loading();

        wdpro.ajax(
          {
            action: 'blockcypher_get_pay_data',
            in: wdpro.payIn,
            sw: wdpro.paySw,
            currencyId: id,
            currencyKey: key,
          },

          res => {
            $button.loadingStop();

            if (res['error']) {
              alert(res['error']);
              return;
            }

            res['amountCrypt'] = wdpro.toNonExponential(res['amountCrypt'])


            let $content = $(blockcypherTemplates.start({
              name: res['currency']['name'],
              image: wdpro.WDPRO_HOME_URL
                + 'wp-content/plugins/wordpressmvc/modules/pay/Methods/images/'
                + res['currency']['image'],
              amount: res['amount'],
              amountCrypt: res['amountCrypt'],
            }));


            // Go To Payment
            $content.find('button').on('click', function () {
              const $button = $(this);
              $button.loading();

              wdpro.ajax(
                {
                  action: 'blockcypher',
                  in: wdpro.payIn,
                  sw: wdpro.paySw,
                  currencyId: id,
                  currencyKey: key,
                },

                res => {
                  console.log('res', res);
                }
              );
            });


            let dialog = new wdpro.dialogs.Dialog({
              content: $content,
              substrate: true,
            });
            dialog.show();
          }
        );
      })
    })
  })
})