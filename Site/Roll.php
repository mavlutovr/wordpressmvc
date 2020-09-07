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
	 * return [
	 *  'n'=>10,
	 *  'key'=>'videoPage',
	 * ]
	 *
	 * @return void|\Wdpro\Tools\Pagination
	 */
	public static function pagination() {

	}


	/**
	 * Возвращает html код с поиском
	 *
	 * Необходимо, чтобы у таблицы были FULLTEXT индексы для каждого поля и один индекс для всех полей сразу
	 *
	 * @param  string $query  Запрос
	 * @param  array $fields Поля, по которым искать
	 * @return string Html код
	 */
	public static function getHtmlSearch($query, $fields) {
		if (!empty($query)) {
			$query = trim($query);
			$query = strip_tags($query);
			$words = \explode(' ', $query);
			$fieldsInQuery = '';

			$where = '';

			$queryData = [];

			// Слова в поиске
			$wordsInQuery = '';
			foreach($words as $word) {
				$wordsInQuery .= '+'.$word;
			}

			$table = static::sqlTable();

			// Перебираем поля
			// SELECT *, ( (1.3 * (MATCH(post_title) AGAINST ('+трусы+домино' IN BOOLEAN MODE))) + (0.6 * (MATCH(post_content) AGAINST ('+трусы+домино' IN BOOLEAN MODE))) + (0.6 * (MATCH(anons) AGAINST ('+трусы+домино' IN BOOLEAN MODE)))) AS relevance FROM `ll_app_catalog` WHERE (MATCH(post_title,post_content,anons) AGAINST ('+трусы+домино' IN BOOLEAN MODE) ) ORDER BY relevance DESC
			foreach ($fields as $fieldName) {

				// Fields
				if ($fieldsInQuery) $fieldsInQuery .= ' + ';

				$fieldsInQuery .= $table::prepare([
					'(1.5 * (MATCH('.$fieldName.') '
					.'AGAINST (%s IN BOOLEAN MODE)))',
					[ $wordsInQuery ]
				]);
			}

			// Where fields
			$queryData[] = $wordsInQuery;

			if ($wordsInQuery) {
				return static::getHtml([
					'where'=>[
						'WHERE (
							MATCH('.\implode(',', $fields).')
							AGAINST (%s IN BOOLEAN MODE)
						)',
						$queryData
					],
					'fields'=>'*, ('.$fieldsInQuery.') AS relevance ',
				]);
			}
		}
	}


	/**
	 * Возвращает html код по запросу
	 *
	 * @param array|string $params Запрос типа array('WHERE id=%d', 123) или параметры
	 * @return string
	 * @throws \Exception
	 */
	public static function getHtml($params) {

		if (is_string($params)) {
			$params = [
				'where'=>$params,
			];
		}

		if (empty($params['where'])) {
			$params['where'] = $params;
		}

		if (empty($params['fields'])) {
			$params['fields'] = null;
		}

		if ($list = static::getData($params['where'], $params['fields'])) {

			if (empty($params['template']))
				$params['template'] = static::getTemplatePhpFile();

			return wdpro_render_php($params['template'], $list, static::$templateExtraData);
		}
	}


	/**
	 * Возвращает данные по запросу
	 *
	 * @param array|string $where Запрос типа array('WHERE id=%d', 123)
	 * @param string $fields
	 * @return array
	 * @throws \Exception
	 */
	public static function getData($where, $fields=null) {

		if (is_string($where)) $where = [$where, []];

		$where[0] = \Wdpro\Lang\Data::replaceLangShortcode($where[0]);

		$table = static::sqlTable();

		if (!strstr($where[0], 'LIMIT ') && $pagination = static::pagination()) {

			if ($pagination === true || $pagination === 1) {
				$pagination = new \Wdpro\Tools\Pagination();
			}
			else if (is_array($pagination)) {
				$pagination = new \Wdpro\Tools\Pagination($pagination);
			}

			$pagination->initByWhere($where, $table);
			$where[0] .= $pagination->getLimit();
		}

		if ($fields === null) {
			$fields = static::sqlFields();
		}
		$fields = \Wdpro\Lang\Data::replaceLangShortcode($fields);
		// $fields = '*';

		if ($sel = $table::select($where, $fields)) {

			$data = array(
				'list'=>array(),
			);

			$data['pagination'] = '';
			if (isset($pagination) && $pagination) {
				$data['pagination'] = $pagination->getHtml();
			}

			foreach($sel as $n=>$row) {
				if ( isset($row['post_name'])  && ! isset($row['url'])) {
					// $post = wdpro_get_post_by_id($row['id']);
					$post = static::getEntityByData($row);
					$row = $post->getDataForTemplate();
					//$row['url'] = $post->getUrl();
				//$row['url'] = \Wdpro\Lang\Data::currentUrl() . $row['post_name'] . wdpro_url_slash_at_end();
					// print_r($row);
				}

				$data['list'][] = static::prepareDataForTemplate($row);
			}

			$data = static::prepareDataForTemplateFull($data);

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
	 * Дополнительная обработка полных данных для шаблона
	 *
	 * @param array $data Данные для шаблона
	 * @return array
	 */
	public static function prepareDataForTemplateFull($data) {

		return $data;
	}


	/**
	 * Возвращает адрес php файла шаблона
	 *
	 * @return string
	 * @example return WDPRO_TEMPLATE_PATH.'catalog_list.php';
	 */
	public static function getTemplatePhpFile() {


	}
}
