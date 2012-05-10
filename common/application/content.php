<div class="oxy-content">
	<?if($this->child):?>
		<?$this->child->put_view()?>
	<?else:?>
		<?$this->put_tiled()?>
	<?endif?>
</div>