<?php
namespace Wdpro\Tools;

class Pagination {

	protected $params = array();
	
	protected $pages;

	/**
	 * @param null|true|array $params Параметры. Если указано true, то ничего не 
	 * происходит, как и при null
	 */
	public function __construct($params=null) {
		
		if (is_admin()) {
			$template = __DIR__.'/pagination_template.console.php';
		}
		else {
			/*wdpro_default_file(
				__DIR__.'/default/templates/pagination_template.php', 
				WDPRO_TEMPLATE_PATH.'pagination.php');*/
			$template = WDPRO_TEMPLATE_PATH.'pagination.php';
		}

		if (isset($params['n'])) {
			$params['pageSize'] = $params['n'];
		}
		
		$this->params = wdpro_extend(array(
			'key'=>'pagination', // Имя параметра в QUERY_STRING
			'total'=>0,
			'pageSize'=>10, // Количество элементов на 1 странице
			'currentPage'=>1, // Текущая страница
			'template'=>WDPRO_TEMPLATE_PATH.'pagination_template.php',
			'crumbsSize'=>10, // Количество страниц между точками 1 ... 13 14 15 ... 45
		), $params);
	}


	/**
	 * @param string $where WHERE запрос
	 * @param \Wdpro\BaseSqlTable $sqlTable Таблица
	 */
	public function initByWhere($where, $sqlTable) {
		
		$total = $sqlTable::count($where);
		
		$this->setTotal($total);
		
		$currentPage = isset($_GET[$this->params['key']]) ?
			$_GET[$this->params['key']] : 1;
		
		$this->setCurrentPage($currentPage);
	}


	/**
	 * Возвращает параметры для LIMIT $ret['offset'], $ret['rows']
	 * 
	 * @return array
	 */
	public function getLimitParams() {
		
		return array(
			'offset'=>(int)(($this->params['currentPage'] - 1) 
				* $this->params['pageSize']),
			'rows'=>(int)($this->params['pageSize']),
		);
	}


	/**
	 * Возвращает строку LIMIT 120, 10
	 * 
	 * @return string
	 */
	public function getLimit() {
		$limit = $this->getLimitParams();
		
		return ' LIMIT '.$limit['offset'].', '.$limit['rows'];
	}


	/**
	 * Установка общего количества элементов
	 * 
	 * @param $total
	 */
	public function setTotal($total) {
		
		$this->params['total'] = $total;

		$this->pages = ceil($total / $this->params['pageSize']);
	}


	/**
	 * Установка текущего номера страницы
	 * 
	 * @param $pageNumber
	 */
	public function setCurrentPage($pageNumber) {
		
		$this->params['currentPage'] = $pageNumber;
	}


	/**
	 * Возвращает html код страниц
	 * 
	 * @return string
	 * @throws \Exception
	 */
	public function getHtml() {
		
		if ($this->pages > 1) {
			
			$data = $this->params;
			
			$data['prev'] = null;
			$data['next'] = null;

			$data['pages'] = array(
				array(
					'url'    => $this->getUrlForPage( $data['currentPage'] ),
					'number' => $data['currentPage'],
				)
			);

			$crumbsSize = ceil( ($data['crumbsSize'] - 1) / 2 );

			// Предыдущие страницы
			$min = $data['currentPage'] - $crumbsSize;
			$min = max( 1, $min );
			for ($page = $data['currentPage'] - 1; $page >= $min; $page--) {

				array_unshift( $data['pages'],
					array(
						'url'    => $this->getUrlForPage( $page ),
						'number' => $page,
					) );
			}
			if ($min > 1) {
				if ($min > 2) {
					array_unshift( $data['pages'], 'crumbs' );
				}
				
				array_unshift( $data['pages'],
					array(
						'url'    => $this->getUrlForPage( 1 ),
						'number' => 1,
					) 
				);
			}
			
			// Следующие страницы
			$max = $data['currentPage'] + $crumbsSize;
			$max = min($this->pages, $max);

			for($page = $data['currentPage'] + 1; $page <= $max; $page++) {

				array_push($data['pages'], array(
					'url'=>$this->getUrlForPage($page),
					'number'=>$page,
				));
			}
			if ($max < $this->pages) {
				if ($max < $this->pages - 1) {
					array_push($data['pages'], 'crumbs');
				}
				
				array_push($data['pages'], array(
					'url'=>$this->getUrlForPage($this->pages),
					'number'=>$this->pages,
				));
			}

			// Предыдущая страница
			if ($data['currentPage'] > 1) {
				$data['prev'] = $this->getUrlForPage( $data['currentPage'] - 1 );
			}

			// Следующая страница
			if ($data['currentPage'] < count($data['pages'])) {
				$data['next'] = $this->getUrlForPage( $data['currentPage'] + 1 );
			}

			
			return wdpro_render_php(
				$data['template'],
				$data
			);
		}
	}
	
	
	public function getUrlForPage($pageNumber) {
		
		return wdpro_current_url(array(
			$this->params['key'] => $pageNumber > 1 ? $pageNumber : null,
		));
	}
}