wdpro.ready($ => {

  // Dialog loading translate
  wdpro.addFilter('dialog--loading', (value, callback) => {

    if (wdpro.langNotEmpty() === 'en') {
      value = 'Loading...';
    }

    callback(value);
  });
});
