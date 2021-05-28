<?php
namespace Wdpro\Tools\MetaTemplate;

/**
 * Основная сущность модуля
 */
class Entity extends \Wdpro\BaseEntity {

	/**
	 * Подготавливает данные для сохранения
	 *
	 * @param array $data Исходные данные
	 * @return array
	 */
	protected function prepareDataForSave($data)
	{
		$data['post_name'] = trim($data['post_name']);
		$data['post_name'] = preg_replace(
			'~^('.preg_quote(home_url()).')~',
			'',
			$data['post_name']
		);
		$data['post_name'] = preg_replace('~(.*/)([^/]+)/?$~i', '$2', $data['post_name']);

		return $data;
	}


}