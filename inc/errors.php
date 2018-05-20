<?php
// Обработка ошибок

/**
 * Ручная отправка ошибки без выхова throw
 * 
 * @param string $message Сообщение
 */
function wdpro_error($subject, $message) {
	if (get_option('wdpro_send_errors_to_admins_emails') == 1) {

		// Отправляем письмо
		\Wdpro\AdminNotice\Controller::sendMessageHtml(
			$_SERVER['HTTP_HOST'].' - Ошибка',
			'
			<p>'.wdpro_current_url().'</p>
			<p>'.$subject.'</p>
			<p>'.print_r($message, 1).'</p>
			'
		);
	}
}

// Если отправка ошибок включена
if (get_option('wdpro_send_errors_to_admins_emails') == 1) {

	// Возвращает тип ошибки в виде текста
	function FriendlyErrorType($type)
	{
		switch($type)
		{
			case E_ERROR: // 1 // 
				return 'E_ERROR';
			case E_WARNING: // 2 // 
				return 'E_WARNING';
			case E_PARSE: // 4 // 
				return 'E_PARSE';
			case E_NOTICE: // 8 // 
				return 'E_NOTICE';
			case E_CORE_ERROR: // 16 // 
				return 'E_CORE_ERROR';
			case E_CORE_WARNING: // 32 // 
				return 'E_CORE_WARNING';
			case E_COMPILE_ERROR: // 64 // 
				return 'E_COMPILE_ERROR';
			case E_COMPILE_WARNING: // 128 // 
				return 'E_COMPILE_WARNING';
			case E_USER_ERROR: // 256 // 
				return 'E_USER_ERROR';
			case E_USER_WARNING: // 512 // 
				return 'E_USER_WARNING';
			case E_USER_NOTICE: // 1024 // 
				return 'E_USER_NOTICE';
			case E_STRICT: // 2048 // 
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR: // 4096 // 
				return 'E_RECOVERABLE_ERROR';
			case E_DEPRECATED: // 8192 // 
				return 'E_DEPRECATED';
			case E_USER_DEPRECATED: // 16384 // 
				return 'E_USER_DEPRECATED';
		}
		return "";
	}
	
	// Проверяет ошибку на серьезность
	function wdpro_is_strong_error($errorId) {

		if ($errorId == 1) return true;
		if ($errorId == 2) return true;
		if ($errorId == 4) return true;
		if ($errorId == 16) return true;
		if ($errorId == 32) return true;
		if ($errorId == 64) return true;
		if ($errorId == 128) return true;
		if ($errorId == 256) return true;
		if ($errorId == 512) return true;
		if ($errorId == 4096) return true;
		
		return false;
	}
	

	// Обработчик ошибок
	function wdpro_error_handler($errorId, $message, $file, $line) {

		// Если это серьезная ошибка
		if (wdpro_is_strong_error($errorId)) {
			
			// Отправляем письмо
			\Wdpro\AdminNotice\Controller::sendMessageHtml(
				$_SERVER['HTTP_HOST'].' - Ошибка',
				'
				<p>'.wdpro_current_url().'</p>
				<p>Тип ошибки: '.FriendlyErrorType($errorId).'</p>
				<p>'.$message.'</p>
				<p>Файл: '.$file.'</p>
				<p>Строка: '.$line.'</p>
				'
			);
		}
		
		// И после этого запускаем стандартный обработчик ошибок
		return false;
	} 
	set_error_handler('wdpro_error_handler', E_ALL ^ (E_NOTICE | E_USER_NOTICE));

	
	// Обработка ошибок, которые не обрабатываются с помощью set_error_handler
	register_shutdown_function(function () {
		
		$error = error_get_last();
		
		if ($error && wdpro_is_strong_error($error['id'])) {

			//echo(FriendlyErrorType($error['id'])."\n\n<BR><BR>\n\n");
			//print_r($error); exit();

			// Отправляем письмо
			\Wdpro\AdminNotice\Controller::sendMessageHtml(
				$_SERVER['HTTP_HOST'].' - Ошибка',
				'
			<p>Тип ошибки: '.FriendlyErrorType($error['id']).'</p>
			<p>'.wdpro_current_url().'</p>
			<p>'.$error['message'].'</p>
			<p>Файл: '.$error['file'].'</p>
			<p>Строка: '.$error['line'].'</p>
			'
			);
		}
		
	});
}
