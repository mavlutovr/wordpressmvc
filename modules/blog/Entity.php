<?php
namespace Wdpro\Blog;

use Wdpro\Exception;

class Entity extends \Wdpro\BasePage {


	/**
	 * Текст страницы
	 *
	 * @param string $content Текущий текст страницы
	 * @throws \Exception
	 */
	public function getCard( &$content ) {
		
		//print_r($this->getDataWithPost());
		
		// Дополнительная внешняя обработка текста
		//$content = apply_filters('wdpro_blog_card_data', $content);
		//$content = $this->data['post_content'.\Wdpro\Lang\Data::getCurrentSuffix()];

		$data = $this->getDataWithPost();

		if (is_array($data['tags']) &&
			(!count($data['tags']) || (count($data['tags']) === 1 && !$data['tags'][0])))
			$data['tags'] = null;

		// Возвращаем карточку страницы
		$content = wdpro_render_php(
			WDPRO_TEMPLATE_PATH.'blog_card.php',
			// Дополнительная внешняя обработка текста
			apply_filters('wdpro_blog_card_data', $data)
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


	/**
	 * Подготавливает данные для сохранения
	 *
	 * @param array $data Исходные данные
	 *
	 * @return array
	 */
	protected function prepareDataForSave ($data) {

		// Теги
		$tags = function ($lang) use (&$data) {

			$suffix = \Wdpro\Lang\Data::getSuffix($lang);

			if (isset($data['tags_string'.$suffix])) {
				$saveTags = [];

				$tags = explode(',', $data['tags_string'.$suffix]);
				foreach ( $tags as $tag ) {
					$tag = trim($tag);
					$saveTags[] = $tag;
				}

				$data['tags'.$suffix] = $saveTags;
			}
		};

		foreach(\Wdpro\Lang\Data::getUris() as $lang) {
			$tags($lang);
		}

		// Добавление шорткода в родительскую страницу
		wdpro_add_shortcode_to_post($this->getParent(), '[blog_posts]');

		return $data;
	}


	/**
	 * Обработка данных после загрузки из базы
	 *
	 * @param array $data Данные
	 *
	 * @return array
	 */
	protected function prepareDataAfterLoad ($data) {


		$tags = function ($lang) use (&$data) {

			$suffix = \Wdpro\Lang\Data::getSuffix($lang);

			if (is_array($data['tags'.$suffix])) {
				$data['tags_string'.$suffix] = implode(', ', $data['tags'.$suffix]);
			}
		};

		foreach(\Wdpro\Lang\Data::getUris() as $lang) {
			$tags($lang);
		}

		return $data;
	}


}