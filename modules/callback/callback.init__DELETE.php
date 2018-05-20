<?php
namespace Wdpro\Callback;

\Wdpro\Autoload::add('Wdpro\Callback', __DIR__);

// Подключаем окошки
\Wdpro\Modules::addWdpro('dialog');

SqlTable::init();

// Ajax
wdpro_ajax('callback-form', function ($data)
{
	// Сохраняем
	$callback = new Entity();
	$callback->setData(array(
		'data'=>$data,
	));
	$callback->save();
	
	// Отправляем сообщение админам
	\Wdpro\AdminNotice\Controller::sendMessageHtml(
		'Заказ обратного звонка',
		'<p>Имя: '.$data['name'].'</p>'
		.'<p>Телефон: '.$data['phone'].'</p>'
	);
	
	return array(
		'message'=>'Мы свяжемся с Вами в ближайшее время',
	);
});


