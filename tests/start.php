<?php
define('WP_USE_THEMES', false);

// Окружение разработки
define('WP_ENV', 'development');

require __DIR__.'/../../../../wp-load.php';
wp();