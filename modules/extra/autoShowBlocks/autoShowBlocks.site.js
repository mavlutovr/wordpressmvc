wdpro.ready(function ($) {

	// Появляющиеся через определенное время блоки
	$('.js-auto-show').wdpro_each(function (block) {

		var time = block.attr('data-time');
		time = time.replace(',', '.');
		time = time.replace(':', '.');
		if (time) {
			var timeArr = time.split('.');

			if (timeArr.length > 1) {

				var seconds = Number(timeArr[0]) * 60 + Number(timeArr[1]);

				setTimeout(function () {
					block.show();
				}, seconds * 1000);
			}
		}
	});
});