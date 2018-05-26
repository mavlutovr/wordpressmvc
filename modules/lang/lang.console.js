(function ($) {

	$(document).ready(function () {

		var currentLang = '';

		// Меню
		// Кнопки
		var buttons = $('.js-wdpro-lang-menu');
		var buttonsoByLang = {};
		buttons.each(function () {
			var button = $(this);
			var a = button.find('a');
			var uri = a.attr('href').replace('#', '');
			button.data('lang', uri);

			buttonsoByLang[uri] = button;
		});


		// Elements
		var elements = $('.js-lang-element');
		var elementsByLang = {};
		wdpro.each(buttonsoByLang, function (button, lang) {
			elementsByLang[lang] = elements.filter('[data-lang="'+lang+'"]:not(.js-lang-inited)')
				.addClass('js-lang-inited');
		});

		// Add Elements
		var addElements = function (container) {

			var newElements = container.find('.js-lang-element');

			elements.add(newElements);
			wdpro.each(buttonsoByLang, function (button, lang) {

				var newElementsLang = newElements.filter('[data-lang="'+lang+'"]:not(.js-lang-inited)')
					.addClass('js-lang-inited');

				if (lang == currentLang) {
					newElementsLang.addClass('lang-visible');
				}

				elementsByLang[lang].add(newElementsLang);
			});
		};

		wdpro.forms.onForm(function (form) {
			form.on('addedToPage', function () {
				addElements(form.html);
			}, true);
		});


		// Select
		var select = function (lang) {
			buttons.removeClass('lang-selected');
			buttonsoByLang[lang].addClass('lang-selected');

			elements.removeClass('lang-visible')
				.addClass('js-form-align-disabe');
			elementsByLang[lang].addClass('lang-visible')
				.removeClass('js-form-align-disabe');
			console.log('elementsByLang[lang]', elementsByLang[lang]);

			wdpro.forms.align();
		};
		select('');


		// Click
		buttons.on('click', function (e) {
			var button = $(this);
			var lang = button.data('lang');
			select(lang);

			e.preventDefault();
			return false;
		});


	});


})(jQuery);