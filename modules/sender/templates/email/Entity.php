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
		$text = apply_filters('wdpro_sender_templates_text', $text);
		$text = wdpro_render_text($text, $data);
		
		$subject = $this->getData('subject');
		$text = apply_filters('wdpro_sender_templates_subject', $text);
		$subject = wdpro_render_text($subject, $data);
		
		\Wdpro\Sender\Controller::sendEmail(
			$email,
			$subject,
			$text
		);
	}


	/**
	 * Устанавливает текст
	 *
	 * @param string $text Текст
	 */
	public function setText($text) {
		$this->setData('text', $text);
	}


	/**
	 * Возвращает текст
	 *
	 * @return string
	 */
	public function getText() {
		return $this->getData('text');
	}

}