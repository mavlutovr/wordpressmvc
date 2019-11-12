<?php
namespace Wdpro\Counters;

class ConsoleForm extends \Wdpro\Form\Form {
	
	public function initFields() {
		
		$this->add(array(
			'name'=>'code',
			'type'=>'text',
			'left'=>array('text'=>'Код счетчика', 'nowrap'=>true),
			'style'=>'width: 600px;',
			'bottom'=>'<a href="https://metrika.yandex.ru/list">Яндекс.Метрика</a>',
		));
		$this->add('sorting');
		$this->add('submitSave');
	}
}