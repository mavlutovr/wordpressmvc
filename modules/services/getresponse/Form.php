<?php
namespace Wdpro\Services\Getresponse;

class Form extends \Wdpro\Form\Form {

	/**
	 * @param null|array|string $params Параметры или имя формы
	 */
	public function __construct($params=null) {

		$params['jsName'] = 'getresponse-form';
		
		parent::__construct($params);
	}


	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

		$this->add([
			'center'=>'Ваш e-mail',
			'name'=>'email',
			'type'=>static::EMAIL,
			'*'=>1,
			'containerClass'=>'_email _w_100'
		]);
		$this->add([
			'type'=>static::SUBMIT,
			'value'=>'Хочу получить книгу в подарок',
			'containerClass'=>'_submit _w_100'
		]);
		$this->add([
			'type'=>static::HTML,
			'html'=>'<div class="_info"><div class="_icon"></div>Мы не рассылаем спам. Вы сможете отписаться в любой момент, кликнув по ссылке в конце письма.</div>',
		]);
	}


}