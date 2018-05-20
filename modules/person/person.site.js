wdpro.ready(function ($) {
	
	
	wdpro.person = {

		/**
		 * Сохраняет настройки пользователя
		 * 
		 * @param params
		 * @param callback
		 */
		saveParams: function (params, callback) {
			
			wdpro.ajax('personSaveParams',
				{
					'params': params
				},
				callback
			);
		}
	};
});