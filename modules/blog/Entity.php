<?php
namespace Wdpro\Blog;

class Entity extends \Wdpro\BasePage {

	/**
	 * Текст страницы
	 *
	 * @param string $content Текущий текст страницы
	 */
	public function getCard( &$content ) {
		
		//print_r($this->getDataWithPost());
		
		// Дополнительная внешняя обработка текста
		//$content = apply_filters('wdpro_blog_card_data', $content);

		// Возвращаем карточку страницы
		$content = wdpro_render_php(
			WDPRO_TEMPLATE_PATH.'blog_card.php',
			// Дополнительная внешняя обработка текста
			apply_filters('wdpro_blog_card_data', $this->getDataWithPost())
		);
	}


	/**
	 * Список дочерних объектов
	 *
	 * <pre>
	 * return array(
	 *  array(
	 *      'roll'=>\App\Good\GoodConsoleRoll::class,
	 *      'label'=>'Товары',
	 *
	 *      // https://developer.wordpress.org/resource/dashicons/#products
	 *      'icon'=>'dashicons-products',
	 *  )
	 * );
	 * </pre>
	 *
	 * @return array|void
	 */
	protected static function childs() {

		$arr = [];
		$arr = apply_filters('wdpro_blog_entity_childs', $arr);
		
		return $arr;
	}


}