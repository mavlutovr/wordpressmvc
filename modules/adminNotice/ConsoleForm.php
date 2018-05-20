<?php
namespace Wdpro\AdminNotice;

class ConsoleForm extends \Wdpro\Form\Form {
	
	public function initFields() {
		
		$this->add(array(
			'name'=>'email',
			'left'=>'E-mail',
			'*',
		));
		$this->add('sorting');
		$this->add('submitSave');
	}
}