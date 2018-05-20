<?php
namespace Wdpro\Form\Elements;

class SortingTop extends Sorting {

	public function __construct($params) {

		$params = wdpro_extend(array(
			'top'=>true,
		), $params);

		parent::__construct($params);
	}
}