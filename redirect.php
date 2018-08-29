<?php
ini_set('display_errors', 'off');
error_reporting(0);
/**
 * Этот файл перенаправляет браузер по адресу, который указан в запросе (_redirect.php?http=http://yandex.ru/ - перенаправит на яндекс)
 */

if (isset($_GET['http']) && $_GET['http'] != '') {

	$http = urldecode($_GET['http']);
	//$http = urldecode($http);
	$http = str_replace(' ', '+', $http);
	$http = str_replace('&amp;', '&', $http);
	$http = str_replace("///", "//", $http);


// Сделал через javascript для защиты
// https://habrahabr.ru/post/282880/
	?>
	<!doctype html>
	<html lang="ru">
	<head>
		<meta charset="UTF-8">
		<title>Redirect</title>
		<script>
			window.opener = null;
			window.location = "<?php echo $http; ?>";
		</script>
	</head>
	<body>
	<p>Перенаправление...</p>
	</body>
	</html>
	<?php
}
?>