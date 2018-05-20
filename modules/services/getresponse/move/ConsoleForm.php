<?php
namespace Wdpro\Getresponse\Move;

class ConsoleForm extends \Wdpro\Form\Form {

	/**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

		if ($campaigns = \Wdpro\Services\Getresponse\Controller::getCompaignsOptions())  {

			$this->add([
				'name'=>'from',
				'top'=>'Откуда переместить',
				'type'=>static::SELECT,
				'options'=>$campaigns,
				'*',
			]);
			$this->add([
				'name'=>'to',
				'top'=>'Куда переместить',
				'type'=>static::SELECT,
				'options'=>$campaigns,
				'*',
			]);

			$this->add([
				'name'=>'days',
				'top'=>'Переместить через это количество дней после отправки последнего
				 письма',
			]);
			$this->add([
				'name'=>'enable',
				'right'=>'Включен',
				'type'=>static::CHECK,
			]);
			$this->add(static::SORTING_TOP);
			$this->add(static::SUBMIT_SAVE);
		}

		else {

			$this->add([
				'type'=>static::HTML,
				'html'=>'<p>Api ключ указан не верно или в аккаунте нету компаний</p>'
			]);
		}

	}


}