<?php
namespace Wdpro\Man;

class Entity extends \Wdpro\BaseEntity {
	/**
	 * Подготавливает данные для сохранения перед первым сохранением в базе
	 *
	 * В вордпресс страницы сохраняются сразу, как только была открыта форма создания.
	 * То есть еще до того, как заполнили форму создания.
	 *
	 * Этот метод обрабатывает данные как бы перед нормальным созданием. Когда уже
	 * заполнили форму. И это обработка данных первого сохранение формы.
	 *
	 * @param array $data Исходные данные
	 *
	 * @return array
	 */
	protected function prepareDataForCreate($data)
	{
		// Данные из шаблона
		if ($data['template']) {
			$data = wdpro_extend(
				$data,
				Controller::getTemplateData($data['template'])
			);
		}

		return $data;
	}


	/**
	 * Возвращает текст карточки
	 *
	 * @return string
	 */
	public function getConsoleCard() {

		$data = $this->getData();

		return '
			<h1>'.$data['name'].'</h1>'
			.$data['text'];
	}


}