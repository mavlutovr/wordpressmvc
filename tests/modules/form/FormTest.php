<?php
require_once __DIR__.'/../../start.php';

/**
 * Created by PhpStorm.
 * User: roma
 * Date: 26.03.16
 * Time: 17:25
 */
class FormTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var \Wdpro\Form\Form
	 */
	protected $form;
	
	public function setUp() {

		$this->form = new \Wdpro\Form\Form('testForm');
		$this->form->add([
			'name'=>'string',
			'left'=>'String',
			'*'=>true,
		]);
		$this->form->add([
			'type'=>\Wdpro\Form\Form::BUTTON,
			'value'=>'button',
		]);
		$this->form->add([
			'type'=>\Wdpro\Form\Form::CHECK,
			'name'=>'check',
			'left'=>'Chcek',
		]);
		$this->form->add([
			'type'=>\Wdpro\Form\Form::EMAIL,
			'name'=>'email',
			'left'=>'E-mail',
		]);
		$this->form->add([
			'type'=>\Wdpro\Form\Form::FILE,
			'name'=>'file',
			'left'=>'File',
		]);
		$this->form->add([
			'type'=>\Wdpro\Form\Form::HIDDEN,
			'name'=>'hidden',
			'value'=>'test',
		]);
		$this->form->add([
			'type'=>\Wdpro\Form\Form::HTML,
			'html'=>'<p>test</p>',
		]);
		$this->form->add([
			'type'=>\Wdpro\Form\Form::IMAGE,
			'name'=>'image',
			'left'=>'Image',
		]);
		$this->form->add([
			'type'=>\Wdpro\Form\Form::PASS,
			'name'=>'pass',
			'left'=>'Password',
		]);
		$this->form->add([
			'type'=>\Wdpro\Form\Form::SELECT,
			'name'=>'select',
			'left'=>'Select',
			'options'=>[
				1=>'1',
				2=>'2',
			],
		]);
		$this->form->add(\Wdpro\Form\Form::SORTING);
		$this->form->add([
			'type'=>\Wdpro\Form\Form::SPINNER,
			'name'=>'spinner',
			'left'=>'Spinner',
		]);
		$this->form->add([
			'type'=>\Wdpro\Form\Form::SUBMIT,
			'value'=>'Submit',
		]);
		$this->form->add(\Wdpro\Form\Form::SUBMIT_SAVE);
		$this->form->add([
			'type'=>\Wdpro\Form\Form::TEXT,
			'name'=>'text',
			'left'=>'Text',
		]);
	}
	
	
	public function tearDown() {

		$this->form = null;
	}


	public function testGetHtml() {
		
		$html = $this->form->getHtml();
		$this->assertStringStartsWith('<div class="js-wdpro-form"><div class="js-params g-hid">{', $html, 'Форма не выдала html код');
	}
	
	
	public function testSubmit() {
		
		$_POST = [
			'testForm'=>[
				'string'=>'stringValue',
			]
		];
		
		$this->assertTrue($this->form->sended(), 'Форма не определила свой запуск');
		
		$this->form->onSubmit(function ($data) {
			
			if (!is_array($data)) {
				$this->fail('В форме не обнаружены данные');
			}
		});
	}
}
