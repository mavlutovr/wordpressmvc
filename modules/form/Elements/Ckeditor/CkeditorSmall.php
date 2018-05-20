<?php
namespace Wdpro\Form\Elements\Ckeditor;

class CkeditorSmall extends Ckeditor {

	/**
	 * @param array $params Параметры
	 */
	public function __construct( array $params ) {

		$params = wdpro_extend([
			'config'=>'small',
		], $params);
		
		parent::__construct( $params );
	}


}