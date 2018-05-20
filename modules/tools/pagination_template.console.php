<!-- Шаблон постраничности Console -->
<div class="wdpro-pagination">
	
	<!-- Номера страниц -->
	<div class="wdpro-pagination-pages">
		<?php foreach($data['pages'] as $page): ?>
			<? if ($page == 'crumbs'): 
				?> ... <? 
			else: 
				if (isset($page['url']) && $page['url']):
					?><a href="<?php echo($page['url']); ?>"
					<?php if ($page['number'] == $data['currentPage']):
					?> class="wdpro-pagination-selected"<?php endif; 
					?>><?php echo($page['number']); ?></a>
				<?php endif; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	
</div>