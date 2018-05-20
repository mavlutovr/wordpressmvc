<?php
namespace Wdpro\Sender\Templates\Email;

class Entity extends \Wdpro\BaseEntity {

	/**
	 * Отправляет письмо
	 * 
	 * @param string $email Адресат
	 * @param array $data Данные для шаблона
	 */
	public function send($email, $data) {
		
		$text = $this->getData('text');
		$text = wdpro_render_text($text, $data);
		
		$subject = $this->getData('subject');
		$subject = wdpro_render_text($subject, $data);
		
		\Wdpro\Sender\Controller::sendEmail(
			$email,
			$subject,
			$text
		);
	}
}