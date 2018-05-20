(function () {

	var uppodN = 0;

	wdpro.ready(function ($) {
		$('.js-uppod-js').wdpro_each(function (div) {

			var json = div.text();
			var params = JSON.parse(json);
			uppodN ++;
			var divId = "js-uppod_js-"+uppodN;
			div.attr('id', divId);
			var st = params['st'] || 'uppodvideo';
			var testDiv;

			div.empty().show();

			var player = new Uppod({
				m: "video",
				uid: divId,
				file: params['video'],
				onReady: function (uppod) {
					if (params['autoplay']) {
						uppod.Play();
					}

					if (params['test']) {
						testDiv = $('<div style="position:absolute; background:  white;' +
							' border: 1px solid black; ' +
							' box-shadow: 2px 2px 2px rgba(0,0,0,0.5);' +
							' z-index: 1000;"></div>');
						div.before(testDiv);
					}
				},
				st: st
			});

			var stop = params['stop'];
			if (stop) stop = Number(stop);
			var stopped = false;

			setInterval(function () {

				if (player) {
					var status = player.getStatus();

					if (status === 1) {
						var time = Number(player.CurrentTime()) * 1000;
						if (!isNaN(time)) {

							var currentTime = Math.round(time);

							if (params['test'] && testDiv) {
								testDiv.html(currentTime);
							}
						}

						if (stop) {
							if (time >= stop) {
								if (!stopped) {
									stopped = true;
									player.Pause();
								}
							}
							else {
								stopped = false;
							}
						}
					}
				}

			}, 50);

			if (params['id']) {
				window.uppodPlayersByID = window.uppodPlayersByID || {};
				window.uppodPlayersByID[params['id']] = player;
			}
		});
	});


	/**
	 * Отображение блока на сайте при достижения определенного времени видео
	 *
	 * @param showId {number}
	 */
	window.wdpro_uppod_js_show = function (showId) {
		wdpro.ready(function ($) {

			var div = $('#js-uppod-js-show-'+showId);
			var test = div.attr('data-test');
			var id = div.attr('data-id');
			var video = window.uppodPlayersByID[id];
			var visible = false;
			var testDiv;
			var showTime = Number(div.attr('data-time'));

			if (test) {

				testDiv = $('<div style="position:absolute; background: white;' +
					' border: 1px solid black; ' +
					'box-shadow: 2px 2px 2px rgba(0,0,0,0.5);"></div>');
				div.before(testDiv);
			}

			setInterval(function () {

				var status = video.getStatus();

				if (status === 1) {
					var seconds = Number(video.CurrentTime());
					if (!isNaN(seconds)) {

						var currentTime = Math.round(seconds * 1000);

						if (showTime <= currentTime) {
							if (!visible) {
								visible = true;
								div.show();
							}
						}
						else {
							if (visible) {
								visible = false;
								div.hide();
							}
						}

						if (test) {
							testDiv.html(currentTime);
						}
					}
				}

			}, 50);
		});
	};
})();

