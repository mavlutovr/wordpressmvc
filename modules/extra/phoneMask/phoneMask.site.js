wdpro.ready($ => {

	var maskList = $.masksSort($.masksLoad(wdpro.data.homeUrl+"wp-content/themes/app/data/phones-ru.json"),
		['#'], /[0-9]|#/, "mask");
	
	var maskOpts = {
		inputmask: {
			definitions: {
				'#': {
					validator: "[0-9]",
					cardinality: 0
				}
			},
			//clearIncomplete: true,
			showMaskOnHover: false,
			autoUnmask: true
		},
		match: /[0-9]/,
		replace: '#',
		list: maskList,
		listKey: "mask",
		onMaskChange: function(maskObj, completed) {

		}
	};


	setInterval(function () {
		$('input[type=tel]:not(.js-masked), .js-phone-mask:not(.js-masked)').each(function () {
			var input = $(this).addClass('js-masked');

			input.change(function () {
				input.inputmasks(maskOpts);
			});

			input.change();
		});
	}, 500);


	setInterval(function () {
		$('.js-phone-simple-mask:not(.js-masked)').each(function () {
			var input = $(this).addClass('js-masked');

			Inputmask({
				mask: '+9 (999) 999-99-99',
			}).mask(input.get(0));
		});
	}, 500);


});
