<?php
/**
 * Всякие шорткоды
 */


// Год в копирайте
add_shortcode('copy_year', function ($params) {

	$y = date('Y');

	if ($params['start'] && $params['start'] != $y) {
		return $params['start'] . ' - ' . $y;
	}

	return $y;
});

