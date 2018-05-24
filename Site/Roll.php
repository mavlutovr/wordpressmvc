<?php
namespace Wdpro\Site;

use Wdpro\Exception;

class Roll extends \Wdpro\BaseRoll {


	/**
	 * Класс таблицы, из которой происходит выборка
	 * 
	 * @return \Wdpro\BaseSqlTable
	 */
	public static function sqlTable() {
		
		$controller = static::getController();
		///$table = $controller::
		$table = $controller::sqlTable();
		if (!$table) {
			$table = \Wdpro\Page\SqlTable::class;
		}
		return $table;
	}


	/**
	 * Необходимые для списка поля
	 * 
	 * @return string
	 * @example return "ID, post_title";
	 */
	public static function sqlFields() {
		return '*';
	}


	/**
	 * Возвращает объект постраничности для использования в списке
	 * 
	 * @return void|\Wdpro\Tools\Pagination
	 */
	public static function pagination() {
		
	}


	/**
	 * Возвращает html код по запросу
	 *
	 * @param array $where Запрос типа array('WHERE id=%d', 123)
	 * @return string
	 * @throws Exception
	 */
	public static function getHtml($where) {
		
		if ($list = static::getData($where)) {
			$template = static::getTemplatePhpFile();

			return wdpro_render_php($template, $list);
		}
	}


	/**
	 * Возвращает данные по запросу
	 *
	 * @param array $where Запрос типа array('WHERE id=%d', 123)
	 * @return array
	 */
	public static function getData($where) {
		
		$table = static::sqlTable();
		
		if ($pagination = static::pagination()) {
			
			$pagination->initByWhere($where, $table);
			$where[0] .= $pagination->getLimit();
		}
		
		if ($sel = $table::select($where, static::sqlFields())) {
			
			$data = array(
				'list'=>array(),
			);
			
			if ($pagination) {
				$data['pagination'] = $pagination->getHtml();
			}
			
			foreach($sel as $row) {
				$data['list'][] = static::prepareDataForTemplate($row);
			}
			
			return $data;
		}
	}


	/**
	 * Дополнительная обработка данных для шаблона
	 *
	 * @param array $row Строка из базы
	 * @return array Строка для шаблона
	 */
	public static function prepareDataForTemplate($row) {

		return $row;
	}


	/**
	 * Возвращает адрес php файла
	 * 
	 * @return string
	 * @example return WDPRO_TEMPLATE_PATH.'catalog_list.php';
	 */
	public static function getTemplatePhpFile() {
		
		
	}
}