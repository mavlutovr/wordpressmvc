<?php
namespace Wdpro\Form\Elements;

/**
 * Поле для указания № п.п. (Сортировка)
 *
 * Отличается от StringElement тем, что короче, что у него есть стандартная
 * приписка слева "№ п.п." и у него имя по-умолчанию "sorting"
 * 
 * @package Wdpro\Form\Elements
 */
class MenuOrder extends StringField {
	
	public function __construct($params) {
		
		$params = wdpro_extend(array(
			'left'=>array('text'=>'№ п.п.', 'nowrap'=>true),
			'name'=>'menu_order',
		), $params);
		
		parent::__construct($params);
	}
}