<!-- Шаблон постраничности 2 -->
<div class="wdpro-pagination">
	
	<!-- Номера страниц -->
	<div class="wdpro-pagination-pages">
		
		<!-- Предыдущая страница -->
		<?php if ($prev): ?>
			<a class="wdpro-pagination-prev"
			   href="<?php echo $prev; ?>"
			>&larr; Предыдущая страница</a>
		<?php else: ?>
			<span class="wdpro-pagination-prev"
			>&larr; Предыдущая страница</span>
		<?php endif; ?>
		
		<?php foreach($data['pages'] as $page): ?>
			<?php if ($page == 'crumbs'): 
				?> ... <?php
			else: 
				if (isset($page['url']) && $page['url']):
					?><a href="<?php echo($page['url']); ?>"
					<?php if ($page['number'] == $data['currentPage']):
					?> class="wdpro-pagination-selected"<?php endif; 
					?>><?php echo($page['number']); ?></a>
				<?php endif; ?>
			<?php endif; ?>
		<?php endforeach; ?>

		<!-- Предыдущая страница -->
		<?php if ($data['next']): ?>
			<a class="wdpro-pagination-next"
			   href="<?php echo $data['next']; ?>"
			>Следующая страница &rarr;</a>
		<?php else: ?>
			<span class="wdpro-pagination-next"
			>Следующая страница &rarr;</span>
		<?php endif; ?>

	</div>
	
</div>