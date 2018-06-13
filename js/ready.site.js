jQuery(document).ready(function () {

	var $ = jQuery;

	/**
	 * Защита ящиков от спама
	 */
	window['wdpro_mail_antispam'] && $('.js-mail-antispam-protect').each(function () {
		var span = $(this);
		var base64 = span.text();
		var decoded = wdpro.base64_decode(base64);
		span.after(decoded);
		span.remove();
	})

});