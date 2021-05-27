wdpro.ready($ => {

  $('#js-kwallet-crypts').each(function () {
    const $container = $(this);

    $container.find('.js-kwallet-crypt').each(function () {
      const $button = $(this);
      const key = $button.data('key');
      const id = $button.data('id');

      $button.on('click', function () {
        $button.loading();

        wdpro.ajax(
          {
            action: 'fkwallet_open_wallet',
            in: wdpro.payIn,
            sw: wdpro.paySw,
            currencyId: id,
            currencyKey: key,
          },

          res => {
            console.log('res', res)
          }
        );
      })
    })
  })
})