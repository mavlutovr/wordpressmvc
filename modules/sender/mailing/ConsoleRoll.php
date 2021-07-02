<?php
namespace Wdpro\Sender\Mailing;

class ConsoleRoll extends \Wdpro\Console\Roll {

	/**
	 * Возвращает параметры списка (необходимо переопределить в дочернем классе для
	 * установки настроек)
	 *
	 * <pre>
	 * return array(
	 *  'labels'=>array(
	 *   'name'=>'Разделы каталога',
	 *   'label'=>'Каталог',
	 *   'add_new'=>'Добавить раздел...',
	 *  ),
	 *  'order'=>'ASC',
	 *  'orderby'=>'$ORDER_FIELD',
	 *  'icon'=>WDPRO_ICONS_PRODUCTS,
	 *      // https://developer.wordpress.org/resource/dashicons/#lock
	 *
	 *  'subsections'=>false,
	 *  'where'=>["WHERE ... %d, %d", [1, 2]],
	 *  'pagination'=>10, //  Количество элементов на странице
	 *  'info'=>'<p>Всякая информация над списком элементов</p>',
	 *
	 *  'showNew' => true, // Показывать в меню количество новых записей
	 * );
	 * </pre>
	 *
	 * @return array
	 */
	public static function params() {

		return [
			'labels' => [
				'label'   => 'Рассылка',
				'add_new' => 'Добавить рассылку',
			],
			// Когда это дочерний элемент
			/*'where'  => [
				'WHERE `post_parent`=%d ORDER BY `menu_order`',
				[
					isset($_GET['sectionId']) ? $_GET['sectionId'] : 0,
				]
			],*/
			'where'=>'ORDER BY menu_order DESC',
			'icon' => 'fas fa-paper-plane',
			'n'=>200,

			// 'pagination'=>10, //  Количество элементов на странице
			// 'info'=>'<p>Всякая информация над списком элементов</p>',
		];
	}


	/**
	 * Возвращает колонки таблицы
	 *
	 * @param array $data Данные строки
	 * @param \Wdpro\BaseEntity $entity Сущность
	 * @return array
	 */
	public function template($data, $entity) {

		$label = $data['label'];
		if (!$label) {
			$label = $data['subject'];
		}

		$views = Stat\Controller::getRowsCount('view', $data['id']);
		$viewsPercent = $data['sended_count'] 
			? round($views / $data['sended_count'] * 100, 1)
			: 0;

		$visits = Stat\Controller::getRowsCount('visit', $data['id']);
		$visitsPercent = $data['sended_count']
			? round($visits / $data['sended_count'] * 100, 1)
			: 0;

		return [
			$label,
			$this->getStatusBlock($data, $entity),

			// Отправлено
			'<strong>'.$data['sended_count'].'</strong>',

			// Открытия
			'<strong>'.$views.'</strong> <sup style="line-height: 0; margin-left: 8px;">'.$viewsPercent.'%</sup>',

			// Переходы
			'<strong>'.$visits.'</strong> <sup style="line-height: 0; margin-left: 8px;">'.$visitsPercent.'%</sup>',


			$this->getSortingField($data)
		];
	}

	
	public static function getStatusBlock($data, $entity) {
		$block = '<div>'.Controller::getStatusLabel($data['status']).'</div>';

		if ($data['status'] === '') {
			$block .= '<div><span class="a js-send-test-letter">Отправить тестовое письмо</span></div>';

			$block .= '<div><span class="a js-start">Запустить</span></div>';
		}

		else if ($data['status'] === 'play') {
			$block .= '<div>Отправлено: '.$data['sended_count'].' / '
				.$entity->getTargetsCountAll().'</div>';
		}

		return $block;
	}


	/**
	 * Возвращает заголовки таблицы в виде массива
	 *
	 * @return array
	 */
	public function templateHeaders() {

		return [
			'Рассылка',
			'Статус',
			'Отправлено',
			'Открыло',
			'Перешло',
			'№ п.п.',
		];
	}


}