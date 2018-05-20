(function ($) {
	
	wdpro.fotorama = {
		
		fullscreen: function (params) {
			
			params = wdpro.extend({
				photos: []
			}, params);
			
			var win = $(fotorama_templates.fullscreen(params));
			$('body').prepend(win);
			
			win.find('.js-container').fotorama({
				//'nav': 'none',
				'width': '100%',
				'height': '90%'
				//navposition: "right"
			});
			
			win.find('.js-close').click(function () {
				win.remove();
			});
		}
	};
	
})(jQuery);