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

		if (\Wdpro\Lang\Data::enabled()) {
			$this->add([
				'name'=>'post_title[lang]',
				'left'=>'Заголовок',
				'*'=>true,
				'langs_skip'=>[''], // Не показывать в языках
			]);
		}

		if (Controller::isImageEnabled()) {
			$this->add(array(
				'name'=>'image',
				'type'=>'image',
				'left'=>'Картинка',
				'resize'=>array(
					array('width'=>150, 'height'=>150, 'type'=>'crop', 'dir'=>'lit'),
					array('width'=>300),
				)
			));
		}

		if (Controller::isAnonsEnabled()) {
			$this->add(array(
				'name'=>'anons[lang]',
				'type'=>static::CKEDITOR_SMALL,
				'top'=>'Текст в списке статей',
				'autoLeft'=>false,
			));
		}
		
		$this->add([
			'name'=>'date_added',
			'type'=>static::DATE,
			'left'=>'Дата публикации',
			'value'=>date('Y.m.d'),
		]);
		
		if (Controller::isDateEdited()) {
			$this->add([
				'name'=>'date_edited',
				'type'=>static::DATE,
				'left'=>'Дата обновления',
			]);
		}

		$this->add([
			'name'=>'post_content[lang]',
			'top'=>'Текст страницы',
			'autoLeft'=>false,
			'type'=>static::CKEDITOR,
		]);


		if (Controller::isTags()) {

			$subinfo = Controller::isTagsPagesModule()
				? '<p><i class="fas fa-tags"></i> <a href="options-general.php?page=Wdpro.Blog.Tags.ConsoleRoll" target="_blank">Настроить теги</a></p>'
				: '';

			$this->add([
				'name'=>'tags_string[lang]',
				'left'=>'Теги',
				'class'=>'js-blog-tags',
				'bottom'=>'<div class="js-blog-tags-list blog-tags-list">'
				          .json_encode(Controller::getTagsList(), JSON_UNESCAPED_UNICODE)
				          .'</div>'.$subinfo,
			]);
		}

		if ($info = \Wdpro\Page\Controller::getConsoleFormInfo()) {
			$this->add([
				'type'=>static::HTML,
				'html'=>$info,
			]);
		}

		do_action('blog_console_form', $this);

		//print_r($this->getElementByName('image')); exit();
		
	}


}