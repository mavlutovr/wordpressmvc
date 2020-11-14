wdpro.ready($ => {

  $('#js-robots-generate').on('click', function () {

    if (!confirm('Continue? This can replace an existing file, if there is one.')) return;

    let $button = $(this);
    $button.loading();

    wdpro.ajax(
      {
        action: 'robots-generate',
      },

      res => {
        $button.loadingStop();
        if (res.error) alert(res.error);
        if (res.html) $button.after(res.html);
      }
    );
  });
});