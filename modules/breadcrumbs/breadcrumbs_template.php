<!-- Шаблон хлебных крошек -->
<div class="breadcrumbs">
	<?php $n = 0; foreach($data['elements'] as $n=>$element): $n ++; ?>
	
		<?php if ($n > 1): ?> <span class="breadcrumbs-separator">/</span> <?php endif; ?>

		<?php if (isset($element['uri']) && $element['uri']): ?>
		<a href="<?php echo $element['uri']; ?>"><?php 
		endif; echo $element['text']; if (isset($element['uri']) && $element['uri']):
		?></a><?php endif; ?>
		
	<?php endforeach; ?>

	<span class="breadcrumbs-separator">/</span>
</div>