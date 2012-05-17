<div class="oxy-header">
	<?$this->put_logo()?>
	<?$current = $this->getCurrent()?>
	<h1><?$current->put_title()?></h1>
	<ul class="bread-crumbs">
	<?$breadCrumbs = $this->getPathToCurrent()?>
	<?array_pop($breadCrumbs)?>
	<?foreach($breadCrumbs as $child):?>
	<li><a href="<?=$child->go()?>"><?=$child?></a><?/*
		<?if($child):?>
		<ul class="submenu">
			<?foreach($child as $ch):?>
			<li><?$ch->put_as_child()?></li>
			<?endforeach?>
		</ul>
		<?endif?>*/?>
	</li>
	<?endforeach?>
	</ul>
	<?$this->put_login()?>
</div>