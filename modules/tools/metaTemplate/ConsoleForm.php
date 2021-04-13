<?php
namespace Wdpro\Tools\MetaTemplate;

class ConsoleForm extends \Wdpro\Form\Form {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

		$this->add([
			'name'=>'post_name',
			'left'=>'Адрес раздела',
			'center'=>'catalog',
			'*'=>true,
		]);

		$this->addHtml('<h2>Шорткоды</h2>
							<ul>
								<li>[post_title] - Текст кнопки</li>
								<li>[title] - Title страницы</li>
								<li>[h1] - H1 страницы</li>
							</ul>');

		$this->add([
			'name'=>'title[lang]',
			'left'=>'Title',
			'width'=>500,
		]);

		$this->add([
			'name'=>'description[lang]',
			'left'=>'Description',
			'width'=>500,
			'type'=>static::TEXT,
		]);

		$this->add([
			'name'=>'h1[lang]',
			'left'=>'H1',
			'width'=>500,
		]);

		$this->add(static::SORTING);
		$this->add(static::SUBMIT_SAVE);
	}


}