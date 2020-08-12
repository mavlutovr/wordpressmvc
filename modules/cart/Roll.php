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
		$element = wdpro_object_by_key($row['key']);
		$row['good'] = $element->getDataForTemplate();

		$row['cost_for_all'] *= 1;
		$row['cost_for_one'] *= 1;

		$keyArray = wdpro_key_parse($row['key']);
		$row['keyArray'] = $keyArray['object'];

		$row['html'] = wdpro_render_php(
			WDPRO_TEMPLATE_PATH.'cart_list_item.php',
			$row
		);

		return $row;
	}


	/**
	 * Возвращает данные по запросу
	 *
	 * @param array|string $where Запрос типа array('WHERE id=%d', 123)
	 * @return array
	 * @throws \Exception
	 */
	public static function getData($where, $fields=null)
	{
		$data = parent::getData($where);

		$data['info'] = Controller::getSummaryInfo();

		return $data;
	}


}