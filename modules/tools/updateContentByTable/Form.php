<?php
namespace Wdpro\Tools\UpdateContentByTable;

class Form extends \Wdpro\Form\Form {

  /**
	 * Инициализация полей
	 *
	 * Здесь поля добавляются в дочерних классах через $this->add(array(...)) когда они
	 * не добавлены через конструктор
	 */
	protected function initFields() {

    $this->setName('updateContent');

    $this->add([
      'name'=>'table',
      'top'=>'Скопируйте сюда ячейки из таблицы',
      '*'=>true,
      'type'=>static::TEXT,
      'width'=>800,
    ]);

    $this->add([
      'type'=>static::SUBMIT_SAVE,
      'value'=>'Обновить контент',
    ]);
  }
}

