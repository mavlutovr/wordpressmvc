wdpro.ready(function ($) {
	$('#js-breadcrumbs').wdpro_each(function (breadcrumbs)
	{
		var type = breadcrumbs.attr('data-first-type');

		if (type)
		{
			$('#adminmenu').wdpro_each(function (menu)
			{
				menu.find('#toplevel_page_edit-post_type-'+type).wdpro_each(function (button)
				{
					menu.find('.current').removeClass('current');

					button.addClass('current');
					button.find('a').addClass('current');
				});
			});
		}
	});
});