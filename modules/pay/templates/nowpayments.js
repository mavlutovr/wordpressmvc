
wdpro.ready($ => {

  // Карточка заказа
  $('#js-nopayments-payment').each(function () {
    const $container = $(this);


    // QR Code
    $('#js-payment-qrcode').each(function () {
      const $qrCodeContainer = $(this);
      const query = $qrCodeContainer.data('query');

      new QRCode($qrCodeContainer.get(0), {
        text: query,
        width: 128 * 2,
        height: 128 * 2,
        // colorDark: "#000000",
        // colorLight: "#ffffff",
        // correctLevel: QRCode.CorrectLevel.H
      });
    });


    // Copy
    $container.find('.js-copy-text').each(function () {
      const $text = $(this);
      const $button = $(nowpaymentsTemplates.copy());
      $text.after($button);

      $button.on('click', function () {
        wdpro.copyTextFromElement($text);
        $button.addClass('copy-text-button-clicked');

        setTimeout(function () {
          $button.removeClass('copy-text-button-clicked');
        }, 250);
        // copyText.select();
        // const copyText = $text.get(0);
        // copyText.select();
        // copyText.setSelectionRange(0, 99999);
        // document.execCommand("copy");
      });
    });

    
    // Update Page
    {
      let sec = 15;
      const $secondsContainer = $container.find('.js-update--seconds');
      let $status = $container.find('.js-status');

      const update = () => {

        $secondsContainer.html(sec);
        sec--;

        if (sec >= 0) {
          setTimeout(update, 1000);
        }

        else {
          let query = wdpro.getQueryStringObject();

          $status.loading();

          wdpro.ajax(
            {
              action: 'nowpayments_get_payment_status',
              id: query['id'],
            },

            res => {
              $status.loadingStop();
              let $newStatus = $(nowpaymentsTemplates.status(res));
              $status.after($newStatus);
              $status.remove();
              $status = $newStatus;

              sec = 15;

              if (res['update']) {
                update();
              }
            }
          )
        }
      }

      // setInterval(update, 1000);
      if ($secondsContainer.length) update();
      
    }
  });


  // Выбор способа оплаты
  $('#js-nopayments-methods').each(function () {
    const $container = $(this);

    $container.find('.js-method').each(function () {
      const $button = $(this);
      const key = $button.data('key');
      const id = $button.data('id');

      $button.on('click', function () {
        $button.loading();

        wdpro.ajax(
          {
            action: 'nowpayment_get_pay_data',
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


            let $content = $(nowpaymentsTemplates.start({
              name: res['currency']['name'],
              image: wdpro.WDPRO_HOME_URL
                + 'wp-content/plugins/wordpressmvc/modules/pay/Methods/images/'
                + res['currency']['image'],
              amount: res['amount'],
              amountCrypt: res['amountCrypt'],
              error: res['error'],
            }));


            // Go To Payment
            $content.find('button').on('click', function () {
              const $button = $(this);
              $button.loading();

              wdpro.ajax(
                {
                  action: 'nowpayment_craete_payment',
                  in: wdpro.payIn,
                  sw: wdpro.paySw,
                  currencyId: id,
                  currencyKey: key,
                },

                res => {
                  if (res['error']) {
                    alert(res['error']);
                    dialog.close();
                    return;
                  }
                  
                  if (res['url']) {
                    window.location = res['url'];
                  }
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