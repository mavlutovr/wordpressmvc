<?php
namespace Wdpro\Blog;

class ConsoleForm extends \Wdpro\Form\Form {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

		$this->add(array(
			'name'=>'image',
			'type'=>'image',
			'top'=>'Картинка',
			'resize'=>array(
				array('width'=>150, 'height'=>150, 'type'=>'crop', 'dir'=>'lit'),
				array('width'=>300),
			)
		));
		$this->add(array(
			'name'=>'anons',
			'type'=>static::CKEDITOR_SMALL,
			'top'=>'Текст в списке статей',
		));
		
		$this->add([
			'name'=>'date_added',
			'type'=>static::DATE,
			'left'=>'Дата публикации',
		]);
		
		$this->add([
			'name'=>'date_edited',
			'type'=>static::DATE,
			'left'=>'Дата обновления',
		]);
		
		do_action('blog_console_form', $this);
		
	}


}