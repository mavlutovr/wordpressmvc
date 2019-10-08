<?php

namespace Wdpro\Cart;

class Roll extends \Wdpro\Site\Roll {


	/**
	 * Возвращает адрес php файла шаблона
	 *
	 * @return string
	 * @example return WDPRO_TEMPLATE_PATH.'catalog_list.php';
	 */
	public static function getTemplatePhpFile()
	{
		return WDPRO_TEMPLATE_PATH.'cart_list.php';
	}


	/**
	 * Дополнительная обработка данных для шаблона
	 *
	 * @param array $row Строка из базы
	 * @return array Строка для шаблона
	 * @throws \Exception
	 */
	public static function prepareDataForTemplate($row)
	{
		/** @var \Wdpro\BasePage $element */
		$element = wdpro_object_by_key($row['element_key']);
		$row['element'] = $element->getDataForTemplate();

		return $row;
	}


	/**
	 * Возвращает данные по запросу
	 *
	 * @param array|string $where Запрос типа array('WHERE id=%d', 123)
	 * @return array
	 * @throws \Exception
	 */
	public static function getData($where)
	{
		$data = parent::getData($where);

		$data['info'] = Controller::getInfoData();

		return $data;
	}


}