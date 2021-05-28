<?php
session_start();
require __DIR__.'/captcha/simple-php-captcha.php';
$_SESSION['captcha'] = simple_php_captcha();

?><img src="<?= $_SESSION['captcha']['image_src']?>">