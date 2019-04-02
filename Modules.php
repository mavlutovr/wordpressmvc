<?php
namespace Wdpro;

/**
 * Модули
 */
class Modules
{
	protected static $modules = array();
	protected static $sections = array(
		'all', 'site', 'console'
	);
	protected static $addedModules = array();
	protected static $controllers = array();


	/**
	 * Добавление модуля
	 *
	 * @param string $pathToModuleDir Путь к папке модуля
	 * @throws Exception
	 */
	public static function add($pathToModuleDir)
	{
		$pathToModuleDir = wdpro_fix_directory_separator($pathToModuleDir);
		if (!isset(static::$addedModules[$pathToModuleDir])) {
			static::$addedModules[$pathToModuleDir] = true;
			
			$controllerClassFileName = $pathToModuleDir.'/Controller.php';
			$controllerClassFileName = wdpro_fix_directory_separator($controllerClassFileName);

			if (is_file($controllerClassFileName))
			{
				$moduleNamespace = require($controllerClassFileName);
				if (!$moduleNamespace || $moduleNamespace === 1)
				{
					throw new Exception('Файл '.$controllerClassFileName
						.' не возвращает имя пространства имен. Добавьте в конец этого 
					файла строку return __NAMESPACE__;');
				}

				Autoload::add($moduleNamespace, $pathToModuleDir);
				
				/** @var \Wdpro\BaseController $controller */
				$controller = $moduleNamespace.'\\Controller';
				//$controller = new $controllerClassName;
				$controller::setDir($pathToModuleDir);
				static::$controllers[$moduleNamespace] = $controller;

				$controller::initStart();
			}

			else {
				if (defined('WP_DEBUG') && WP_DEBUG) {
					echo 'Нет файла: '.$controllerClassFileName.PHP_EOL.PHP_EOL;
				}
			}

			// Секции (все, только сайт, только админка)
			$sections = array('all', 'site', 'console');

			// Получаем имя модуля
			$moduleName = wdpro_basename( $pathToModuleDir );

			// Возвращает имя файл модуля с заданным окончанием
			$fileName = function ( $ext ) use ( $pathToModuleDir, $moduleName ) {

				if (strstr( $pathToModuleDir, '/' ) || strstr( $pathToModuleDir, '\\' )) {
					$dir = $pathToModuleDir;
				}
				else {
					$dir = wdpro_realpath(
						__DIR__ . '/../modules/'
						. $moduleName . '/' . $moduleName . '.' . $ext
					);
				}

				$fullName = $dir . '/' . $moduleName . '.' . $ext;

				$fullName = wdpro_fix_directory_separator($fullName);


				$fullName = realpath(
					$fullName
				);


				if (is_file( $fullName )) {
					return $fullName;
				}
			};

			
			// Шаблоны по-умолчанию
			$templatesDir = $pathToModuleDir.'/default/templates/';
			$templatesDir = wdpro_fix_directory_separator($templatesDir);
			if (is_dir($templatesDir)) {
				$templatesFiles = scandir($templatesDir);
				foreach($templatesFiles as $templateFile) {
					if ($templateFile != '.' && $templateFile != '..' && is_file
						($templatesDir.$templateFile)) {
						if (!is_file(WDPRO_TEMPLATE_PATH.$templateFile)) {
							copy($templatesDir.$templateFile,
								WDPRO_TEMPLATE_PATH.$templateFile);
						}
					}
				}
			}
			

			// Перебирает файлы всех секций
			$eachFiles = function ( $ext, $callback ) use ( &$fileName, &$sections ) {

				foreach ($sections as $section) {
					if ($file = $fileName( $section . '.' . $ext )) {
						$callback( $file, $section );
					}
				}
			};


			// Less
			$eachFiles( 'less',
				function ( $file ) {

					wdpro_less_compile_try( $file, $file . '.css' );
				} );

			// Css
			$eachFiles( 'less.css',
				function ( $file, $section ) {

					if ($section === 'console' || $section === 'all') {
						wdpro_add_css_to_console( $file );
					}

					if ($section === 'site' || $section === 'all') {
						wdpro_add_css_to_site( $file );
					}
				} );

			// Soy
			$eachFiles( 'soy',
				function ( $file ) {

					wdpro_closure_compile_try( $file, $file . '.js' );
				} );


			// JavaScript
			$jsFileProcess = function ( $jsFile, $section ) {

				if ($section === 'console' || $section === 'all') {
					wdpro_add_script_to_console( $jsFile );
				}
				if ($section === 'site' || $section === 'all') {
					wdpro_add_script_to_site( $jsFile );
				}
			};

			// Скомпилированный SOY
			$eachFiles( 'soy.js', $jsFileProcess );

			// JavaScript
			$eachFiles( 'js', $jsFileProcess );


			// Php
			if ($phpInit = $fileName( 'init.php' )) {
				require($phpInit);
			}
			//static::modulesRun('init');
			
			/*if (is_admin()) {
				
				static::modulesRun('initConsole');
			}
			
			else {
				
				static::modulesRun('initSite');
			}*/

			add_action(
				'wdpro_modules_php_run',
				function ( $type ) use ( &$fileName ) {

					if ($phpFile = $fileName( $type . '.php' )) {
						// Т.к. мы сейчас внутри функции, чтобы были видны внешние переменные,
						// делаем это
						extract( $GLOBALS, EXTR_REFS );

						require($phpFile);
					}
				}
			);

			/*$eachFiles('php', function ($file) {
				require($file);
			});*/
		}
	}


	/**
	 * Добавление плагина, который находится в папке этого плагина /modules
	 * 
	 * @param string $moduleName Имя модуля
	 */
	public static function addWdpro($moduleName)
	{
		static::add(WDPRO_DIR.'modules/'.$moduleName);
	}


	/**
	 * Запустить все php скрипты модулей заданного типа
	 * 
	 * @param $type
	 */
	public static function run($type)
	{
		// Старый метод
		do_action('wdpro_modules_php_run', $type);
		
		static::modulesRun($type);
	}


	/**
	 * Запускает методы контроллеров
	 * 
	 * @param string $methodname Имя метода
	 */
	protected static function modulesRun($methodname)
	{
		/** @var \Wdpro\BaseController $controller */
		foreach(static::$controllers as $controller) {
			if (method_exists($controller, $methodname)){
				call_user_func(array($controller, $methodname));
				//$controller->$methodname();
			}
		}
	}


	/**
	 * Проверка, включен ли модуль по папке
	 *
	 * @param string $pathToModuleDir Папка модуля
	 *
	 * @return bool
	 */
	public static function exists($pathToModuleDir) {
		return isset(static::$addedModules[$pathToModuleDir])
			&& static::$addedModules[$pathToModuleDir];
	}


	/**
	 * Проверка, включен ли модуль Wdpro
	 *
	 * @param string $moduleName Папка модуля в /modules
	 *
	 * @return bool
	 */
	public static function existsWdpro($moduleName) {
		return static::exists(WDPRO_DIR.'modules/'.$moduleName);
	}


	/**
	 * Возвращает массив контроллеров
	 *
	 * @return \App\BaseController[]
	 */
	public static function getControllers() {
		return static::$controllers;
	}
}

