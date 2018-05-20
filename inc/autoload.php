<?php
namespace Wdpro;

/**
 * Автозагрузка классов
 * 
 * @package Wdpro
 */
class Autoload
{
	protected static $namespaces = array();


	/**
	 * Регистрация папки мени пространства для автозагрузки классов этого пространства
	 * 
	 * @param string $namespaceName Имя пространства имен (Wdpro\Forms)
	 * @param string $dirOfNamespace Полный (абсолютный) путь к папке пространства имен
	 */
	public static function add($namespaceName, $dirOfNamespace)
	{
		static::$namespaces[$namespaceName] = wdpro_realpath($dirOfNamespace).'/';
	}


	/**
	 * Возвращает путь к папке пространства имен
	 * 
	 * @param string $namespaceName Название пространства имен
	 * @return string
	 */
	public static function getDir($namespaceName) {
		
		if (isset(static::$namespaces[$namespaceName]))
			return static::$namespaces[$namespaceName];
	}


	/**
	 * Проверяет наличие класса
	 *
	 * @param string $classNameWithNamespace Полное имя класса вместе с пространством имен
	 * @return bool
	 */
	public static function isClass($classNameWithNamespace) {
		
		if (static::getFileOfClass($classNameWithNamespace)) {
			return true;
		}
		
		return false;
	}


	/**
	 * Возвращает имя класса, если такой файл есть
	 * 
	 * @param string $classNameWithNamespace Полное имя класса вместе с пространством имен
	 * @return string
	 */
	public static function getFileOfClass($classNameWithNamespace) {
		
		$namespacesList = explode('\\', $classNameWithNamespace);
		$className = array_pop($namespacesList);
		$path = implode('\\', $namespacesList);
		$dirNamespace = null;


		// Находим самый подходящий зарегистрированный путь
		$searchProcess = true;
		for($n = count($namespacesList) - 1; $n >= 0 && $searchProcess; $n --)
		{
			$namespace = '';

			for($i=0; $i <= $n; $i ++)
			{
				if ($namespace != '') { $namespace .= '\\'; }
				$namespace .= $namespacesList[$i];
			}

			if (isset(static::$namespaces[$namespace]))
			{
				$dirNamespace = static::$namespaces[$namespace];
				$searchProcess = false;
			}
		}

		// Если есть папка пространства имен
		if ($dirNamespace)
		{
			// Находим подпапки для поиска файла класса
			$subdir = str_replace($namespace, '', $path);
			$subdir = str_replace('\\', '/', $subdir);
			if ($subdir) { $subdir .= '/'; }

			$path = $dirNamespace.$subdir.$className.'.php';
			$path = wdpro_realpath($path);

			if (is_file($path))
			{
				return $path;
			}
		}
	}


	/**
	 * Инициализация
	 */
	public static function init()
	{
		spl_autoload_register(function ($classNameWithNamespace)
		{
			/*echo($classNameWithNamespace."\n\n");
			if ($file = static::getFileOfClass($classNameWithNamespace))
			{
				require $file;
			}

			else
			{
				throw new AutoloadException(
					'Не удалось подключить '
					. AutoloadException::arg(2),
					2
				);
			}*/
			
			
			$namespacesList = explode('\\', $classNameWithNamespace);
			$className = array_pop($namespacesList);
			$path = implode('\\', $namespacesList);
			//array_pop($namespaceArr);
			//print_r($namespaceArr);
			$dirNamespace = null;
			
			
			// Находим самый подходящий зарегистрированный путь
			$searchProcess = true;
			for($n = count($namespacesList) - 1; $n >= 0 && $searchProcess; $n --)
			{
				$namespace = '';
				
				for($i=0; $i <= $n; $i ++)
				{
					if ($namespace != '') { $namespace .= '\\'; }
					$namespace .= $namespacesList[$i];
				}

				if (isset(static::$namespaces[$namespace]))
				{
					$dirNamespace = static::$namespaces[$namespace];
					$searchProcess = false;
				}
				
				//echo('$namespace: '."$namespace\n<BR><BR>\n");
			}
			
			// Если есть папка пространства имен
			if ($dirNamespace)
			{
				// Находим подпапки для поиска файла класса
				$subdir = str_replace($namespace, '', $path);
				$subdir = str_replace('\\', '/', $subdir);
				if ($subdir) { $subdir .= '/'; }
				
				$path = $dirNamespace.$subdir.$className.'.php';
				$path = wdpro_realpath($path);

				if (is_file($path))
				{
					require $path;
				}
				
				else
				{
					throw new AutoloadException(
						'Не удалось подключить '.$path."\n\n"
						. AutoloadException::arg(2),
						2
					);
				}
			}
		});
	}
}


require(__DIR__.'/../Exception.php');
class AutoloadException extends \Wdpro\Exception
{
	
}


Autoload::add('Wdpro', __DIR__.'/../');
Autoload::add('Wdpro\Libs', __DIR__.'/../libs/');
//Autoload::add('Wdpro\Console', __DIR__.'/../Console/');
Autoload::init();


