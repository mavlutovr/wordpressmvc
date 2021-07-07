wdpro.ready($ => {

  const getCookie = name => {
    let matches = document.cookie.match(new RegExp(
      "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
  };

  const visible = !getCookie('cookieClosed');

  if (visible) {
    const $dialog = $(cookie_notice_templates.dialog());
    $('body').append($dialog);

    $dialog.find('.js-close').on('click', function () {
      let date = new Date();
      date.setDate(date.getDate() + 365);
      document.cookie = "cookieClosed=true; path=/; expires=" + date.toUTCString();
      $dialog.remove();
    });
  }
})