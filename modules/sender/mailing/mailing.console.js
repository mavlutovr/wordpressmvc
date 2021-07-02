wdpro.ready($ => {

  $('#wdpro-console-roll-wdpro_mailing').find('.js-row').each(function () {
    const $row = $(this);
    const id = $row.data('id');
    const $testButton = $row.find('.js-send-test-letter');
    const $startButton = $row.find('.js-start');


    // Test Letter
    $testButton.on('click', () => {
      $testButton.loading();
      
      wdpro.ajax(
        {
          action: 'mailing-send-test-letter',
          id,
        },

        res => {
          $testButton.loadingStop();

          if (res['error']) {
            alert(res['error']);
          }

          if (res['ok']) {
            alert('Отправлено');
          }
        }
      );
    });


    // Start
    $startButton.on('click', () => {
      if (!confirm('Запустить рассылку?')) return;
      
      $startButton.loading();

      wdpro.ajax(
        {
          action: 'mailing-start',
          id,
        },

        res => {

          if (res['error']) {
            $startButton.loadingStop();
            alert(res['error']);
          }

          if (res['ok']) {
            window.location = window.location;
          }
        }
      );
    });
  });
})