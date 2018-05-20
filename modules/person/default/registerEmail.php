<?php

return [
	'subject'=>$_SERVER['HTTP_HOST'].' - Регистрация',
	'text'=>'
		<p>Здравствуйте!</p>
		<p>Ваш логин: {user_login}</p>
		<p>Ваш пароль: {user_pass}</p>
	',
	'info'=>'
{user_login} - Логин
{user_pass} - Пароль'
];