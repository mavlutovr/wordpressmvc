<?php echo '<?php'.PHP_EOL; ?>
namespace <?php echo $namespace; ?>;

class ConsoleRoll extends \Wdpro\Console\PagesRoll {

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
	 *  'where'  => [
	 *    'WHERE `post_parent`=%d ORDER BY `menu_order`',
	 *    [
	 *      wdpro_current_console_section_id(), // ID текущего раздела
	 *    ]
	 *  ],
	 *  'pagination'=>10, //  Количество элементов на странице
	 *  'info'=>'<p>Всякая информация над списком элементов</p>'
	 * );
	 * </pre>
	 *
	 * @return array
	 */
	public static function params() {

		return [
			'labels'=>[
				'label'=>'<?php echo $ConsoleRoll_label; ?>',
<?php if ($ConsoleRoll_add_new): ?>'add_new' => 'Добавить <?php echo $ConsoleRoll_add_new; ?>',<?php endif;?>

			],
<?php if ($ConsoleRoll_icon): ?>
			'icon' => '<?php echo $ConsoleRoll_icon; ?>',
<?php endif; ?>

		];
	}
}