<?php
namespace Wdpro\Man;

class Controller extends \Wdpro\BaseController {


	protected static $buttonText;


	/**
	 * Дополниительная инициализация для админки
	 *
	 * Иконки: https://developer.wordpress.org/resource/dashicons/#forms
	 */
	public static function initConsole() {

		\Wdpro\Console\Menu::add([
			'roll'=>ConsoleRoll::class,
			'n'=>-10,
		]);


		add_action( 'show_user_profile', [ '\\Wdpro\\Man\\Controller', 'userProfileForm' ] );
		add_action( 'edit_user_profile', [ '\\Wdpro\\Man\\Controller', 'userProfileForm' ] );

		add_action( 'personal_options_update',  [ '\\Wdpro\\Man\\Controller', 'userProfileSave' ]);
		add_action( 'edit_user_profile_update', [ '\\Wdpro\\Man\\Controller', 'userProfileSave' ]);
	}


	/**
	 * Возвращает Options с шаблонами для Select
	 *
	 * @return array
	 */
	public static function getTemplatesOptions() {
		$options = [''=>''];

		$files = scandir(__DIR__.'/templates');
		foreach ($files as $file) {

			if ($fileData = static::getTemplateData($file)) {
				$options[$file] = $fileData['name'];
			}
		}

		return $options;
	}


	/**
	 * Возвращает данные шаблона
	 *
	 * @param string $file Имя файла
	 * @return array
	 */
	public static function getTemplateData($file) {
		$filePath = __DIR__.'/templates/'.$file;

		if (is_file($filePath)) {
			return require($filePath);
		}
	}


	/**
	 * Задает текст кнопки справки в меню админки
	 *
	 * Чтобы стандартный текст кнопки "Справка по ТЕКУЩИЙ_ДОМЕН" можно было поменять на другой
	 *
	 * @param string $buttonText Текст кнопки
	 */
	public static function setButtonText($buttonText) {
		static::$buttonText = $buttonText;
	}


	/**
	 * Возвращает текст для кнопки меню в админке "Справка по сайту"
	 *
	 * @return string
	 */
	public static function getButtonText() {
		if (isset(static::$buttonText) && static::$buttonText)
			return static::$buttonText;

		return 'Справка по '.$_SERVER['HTTP_HOST'];
	}


	public static function userProfileForm() {
		global $user;
		?>
		<h2>Справка по сайту</h2>
		<table class="form-table">
			<tr>
				<th><label for="wdpro_man_templates">Доступ к шаблонам справки</label></th>
				<td>
					<input type="hidden" name="wdpro_man_templates" value="">
					<input type="checkbox"
						<?php
						if (wp_get_current_user()->wdpro_man_templates): ?> checked="checked"<?php endif; ?>
						     name="wdpro_man_templates" id="wdpro_man_templates" class="regular-text"
						     value="1" />
				</td>
			</tr>
		</table>
		<?php
	}


	public static function userProfileSave($user_id) {
		$saved = false;
		if ( current_user_can( 'edit_user', $user_id ) ) {
			update_user_meta( $user_id, 'wdpro_man_templates', $_POST['wdpro_man_templates'] );
			$saved = true;
		}
		return true;
	}

}

return __NAMESPACE__;