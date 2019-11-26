<?php echo '<?php'.PHP_EOL; ?>
namespace <?php echo $namespace; ?>;

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
				'label'   => '<?php echo $ConsoleRoll_label; ?>',
				<?php if ($ConsoleRoll_add_new): ?>'add_new' => 'Добавить <?php echo $ConsoleRoll_add_new; ?>',<?php endif;?>
			],
			// Когда это дочерний элемент
			/*'where'  => [
				'WHERE `post_parent`=%d ORDER BY `<?php echo $ConsoleRoll_sorting_field; ?>`',
				[
					isset($_GET['sectionId']) ? $_GET['sectionId'] : 0,
				]
			],*/
			'where'=>'ORDER BY id',
<?php if ($ConsoleRoll_icon): ?>
			'icon' => '<?php echo $ConsoleRoll_icon; ?>',
<?php endif; ?>

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

		return [
			$this->getSortingField($data)
		];
	}


	/**
	 * Возвращает заголовки таблицы в виде массива
	 *
	 * @return array
	 */
	public function templateHeaders() {

		return [
			'№ п.п.',
		];
	}


}