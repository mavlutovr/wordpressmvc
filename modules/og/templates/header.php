	<meta property="og:title" content="<?=htmlspecialchars($title)?>">
	<meta property="og:description" content="<?=htmlspecialchars($description)?>">
	<meta property="og:image" content="<?=$ogImage?>">
	<meta property="og:url" content="<?=$url?>">
	<meta property="og:type" content="article" />
	<?php if ($fbAppId): ?>
	<meta property="fb:app_id" content="<?=$fbAppId?>"/>
	<?php endif; ?>
