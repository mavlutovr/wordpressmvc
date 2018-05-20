<?php
namespace Wdpro\Form\Elements;

class Date extends StringField {

	/**
	 * @param array $params Параметры
	 */
	public function __construct(array $params) {

		parent::__construct($params);

		// Это перенесено в console.php, чтобы загружалось в начале страницы
		/*add_action( 'admin_enqueue_scripts', function ()
		{
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		});*/
		
	}
	
	
	
	


}