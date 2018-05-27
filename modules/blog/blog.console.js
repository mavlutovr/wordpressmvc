(function ($) {
	$(document).ready(function () {

		wdpro.forms.onFormAddedToPage('wdpro_blog', function (form) {

			var tagsInputs = form.html.find('.js-blog-tags');

			tagsInputs.each(function () {
				var input = $(this);
				var container = input.closest('.JS_element');
				var tagsContainer = container.find('.js-blog-tags-list');
				var json = tagsContainer.text();

				if (json) {
					var tagsList = JSON.parse(json);
					var tagsHtml = $(blog_templates.tags({
						tags: tagsList
					}));

					/**
					 * Возвращает массив добавленных тегов
					 *
					 * @return {Array}
					 */
					var getTagsFromInput = function () {
						var text = input.val();
						return text.split(', ');
					};

					/**
					 * Обновление меню тегов
					 */
					var update = function () {
						var addedTags = getTagsFromInput();
						tagsHtml.find('span').each(function () {
							var span = $(this);
							var tag = span.text();
							if (addedTags.indexOf(tag) != -1) {
								span.addClass('blog-tags-active');
							}
							else {
								span.removeClass('blog-tags-active');
							}
						});
					};
					update();

					// Клик по тегу
					tagsHtml.find('span').on('click', function () {
						var tag = $(this).text();
						var addedTags = getTagsFromInput();
						if (addedTags.indexOf(tag) === -1) {
							var inputText = input.val();
							if (inputText) inputText += ', ';
							inputText += tag;
							input.val(inputText);
							update();
						}
					});

					tagsContainer.empty().append(tagsHtml);
				}


			});

		});

	});
})(jQuery);