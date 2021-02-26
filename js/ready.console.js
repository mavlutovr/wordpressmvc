jQuery(document).ready(function ()
{
	var $ = jQuery;

	/*// Формы (Перенесено в ready.all.js
		$('.js-wdpro-form').each(function ()
		{
			var container = $(this);

			// Получаем параметры формы
			var jsonDiv = container.find('.js-params');
			var json = jsonDiv.text();
			var data = wdpro.parseJSON(json);

			// Создаем форму
			var form = new wdpro.forms.Form(data);

			form.getHtml(function (html) {
				container.append(html);
			});
		});*/
	
	
	// Формы шаблонов
	$('#js-wdpro-templates-forms').wdpro_each(function (formsContainer)
	{
		$('#page_template').wdpro_each(function (templateSelect)
		{
			var formsList = {};
			var rootBlock = $('#js-wdpro-form-fields');
			
			var showCurrentForm = function() {
				
				var currentTemplate = templateSelect.val();
				
				var issetForm = false;
				
				for(var templateName in formsList)
				{
					if (currentTemplate == templateName)
					{
						formsList[templateName].show();
						issetForm = true;
					}
					else
					{
						formsList[templateName].hide();
					}
				}
				
				if (issetForm)
				{
					rootBlock.show();
				}
				else
				{
					rootBlock.hide();
				}
			};
			
			formsContainer
				.children('.js-wdpro-template-form')
				.wdpro_each(function (formContainer) {
					
					var formTemplateName = formContainer.attr('data-template-name');
					formContainer.hide();
					
					formsList[formTemplateName] = formContainer;
				});
			
			showCurrentForm();
			templateSelect.on('change', showCurrentForm);
		});
	});
	
	
	// Дополнительные параметры кнопки "Добавить...", "Корзина"
	(function ()
	{
		var query = wdpro.parseQueryString(window.location.search);
		
		var sectionId = window.parentPageId || query['sectionId'];
		
		if (sectionId)
		{
			// Добавить
			$('.page-title-action').wdpro_each(function (a)
			{
				var href = a.attr('href');
				href += '&sectionId='+sectionId;
				a.attr('href', href);
			});
			
			// Корзина
			$(".subsubsub").wdpro_each(function (list) {
				
				list.find('a').wdpro_each(function (a) {
					
					var href = a.attr('href');
					href += '&sectionId='+sectionId;
					a.attr('href', href);
				});
			});
		}
	})();
	
	
	// Выбор родительской страницы при добавлении новой страницы
	$('#pageparentdiv').wdpro_each(function (block)
	{
		var queryes = wdpro.parseQueryString();
		var sectionId = queryes['sectionId'];
		
		if (sectionId)
		{
			block.find('#parent_id').wdpro_each(function (select)
			{
				if (!select.val())
				{
					select.val(sectionId);
				}
			});
		}
	});
	
	
	// Замена ссылок у названий страниц на переход к подразделам
	$('.wp-list-table').wdpro_each(function (table)
	{
		table.find('.iedit').wdpro_each(function (tr)
		{
			var a = tr.find('.row-title');
			var oldStrong = a.parent();
			var newStrong = $('<strong />');
			oldStrong.after(newStrong);

			// Лишние минусы перед названиями
			a.text(a.text().replace(/^([— ]+)/g, ''));
			newStrong.append(a);
			
			// Лишняя информация о родительской странице
			var info = oldStrong.text();
			info = info.replace(/ \| Родительский: [^|]+/, '');
			newStrong.append(info);
			
			oldStrong.remove();
			
			// Редактирование
			// Иконка у заголовка
			a.prepend('<i class="fa fa-pencil-square-o" aria-hidden="true"' +
			          ' title="Изменить"></i>');

			// Убираем редактирование снизу
			tr.find('.edit').remove();
			
			/*var editIcon = $('<span><span class="dashicons dashicons-edit"></span> </span>');
			a.prepend(editIcon);*/

			
			// Подразделы
			tr.find('.js-subsections').wdpro_each(function (subsections)
			{
				var subsectionsCount = subsections.find('.js-subsections-count');
				if (subsectionsCount.length) {
					var subsectionArrow =
						$('<span title="У этой страницы есть подразделы"><a href="' + subsections.attr('href') +
						  '"><span class="fa fa-folder"></span>' +
						  '<span class="js-children-count-container">' +
						  '</span></a></span>');
					a.after(subsectionArrow).after('<span class="wdpro-row-title-separator"></span>');
					subsectionArrow.find('.js-children-count-container')
						.append(subsectionsCount);
					//subsections.parent().remove();
				}
			});
			
			
			// Дочерние элементы
			tr.find('.js-children-button').wdpro_each(function (button) {
				
				var count = button.find('.js-children-count');
				if (count.length) {
					var text = button.attr('data-label');
					var icon = button.attr('data-icon');
					var arrow =
						$('<span class="wdpro-row-title-child" ' +
						  'title="У этой страницы есть &laquo;' +
						  text + '&raquo;">' +  '<a href="' + button.attr('href') +
						  '"><span class="' +  icon +  '"></span>' +
						  '<span class="js-count-container"></span></a></span>');
					arrow.find('.js-count-container').append(count);
					a.after(arrow).after('<span class="wdpro-row-title-separator"></span>');
				}
			});
			
			// Адрес страницы
			tr.find('.js-post-link').wdpro_each(function (a) {
				a.after('<span class="wdpro-console-post-name">' +
				        a.attr('data-post-name') +
				        '</span>'
				);
			});
		});
		
		table.css('opacity', '1');
	});
	
	
	// Списки
	$('.js-roll').wdpro_each(function (roll)
	{
		roll.find('.js-row').wdpro_each(function (row)
		{
			// Удаление
			row.find('.js-del').click(function (e)
			{
				if (!confirm('У этого списка нет корзины, удалить на совсем этот' +
				             ' элемент?'))
				{
					e.preventDefault();
					return false;
				}
				
				return true;
			});
		});
	});
	
	
	// Адрес страницы в форме редактирования
	$('#sample-permalink').wdpro_each(function (permalink) {
		var a = permalink.find('a');
		var link = wdpro.WDPRO_HOME_URL+$('#editable-post-name-full').text();
		a.attr('href', link);
		a.attr('target', '_blank');
		
		$('#message').wdpro_each(function (message) {
			message.find('a').wdpro_each(function (a) {
				if (a.attr('href').indexOf('%postname%') != -1) {
					a.attr('href', link);
					a.attr('target', '_blank');
				}
			});
		});
	});


	// Code copy
	$('body').on('click', 'code', function (e) {
		const $code = $(this);
		let code = $code.html();
		let text = $code.text();
		if (code === text) {
			let range = document.createRange();
			range.selectNode($code.get(0));
			window.getSelection().removeAllRanges();
			window.getSelection().addRange(range);
		}
	});

});